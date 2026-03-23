-- ============================================================================
-- MIGRACIÓN: Inventario por Calidad de Napa
-- La calidad del napa producido depende directamente de la calidad de fibra del cubo
-- El inventario debe rastrear stock por calidad de napa
-- ============================================================================

USE sistema_napa;

-- 1. AGREGAR mapeo directo en calidades_fibra → calidades_napa
ALTER TABLE calidades_fibra 
ADD COLUMN IF NOT EXISTS id_calidad_napa_destino INT UNSIGNED NULL 
COMMENT 'Calidad de napa que produce esta fibra'
AFTER factor_precio;

-- Agregar FK
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'calidades_fibra'
  AND CONSTRAINT_NAME = 'fk_calidad_fibra_napa_destino';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE calidades_fibra ADD CONSTRAINT fk_calidad_fibra_napa_destino FOREIGN KEY (id_calidad_napa_destino) REFERENCES calidades_napa(id_calidad_napa) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. MAPEAR calidades de fibra a calidades de napa
UPDATE calidades_fibra SET id_calidad_napa_destino = 1 WHERE nombre = 'Fibra Virgen';
UPDATE calidades_fibra SET id_calidad_napa_destino = 2 WHERE nombre = 'Fibra Cristalizada';
UPDATE calidades_fibra SET id_calidad_napa_destino = 3 WHERE nombre = 'Fibra Reciclada Premium';
UPDATE calidades_fibra SET id_calidad_napa_destino = 4 WHERE nombre = 'Fibra Estándar';

-- 3. MODIFICAR inventario para incluir calidad de napa
-- Primero, eliminar constraint UNIQUE en tipo_item
ALTER TABLE inventario DROP INDEX IF EXISTS tipo_item_UNIQUE;

-- Agregar columna de calidad
ALTER TABLE inventario 
ADD COLUMN IF NOT EXISTS id_calidad_napa INT UNSIGNED NULL 
COMMENT 'Para producto_terminado: calidad específica'
AFTER tipo_item;

-- Agregar FK
SET @fk_inv_exists = 0;
SELECT COUNT(*) INTO @fk_inv_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'inventario'
  AND CONSTRAINT_NAME = 'fk_inventario_calidad_napa';

SET @sql = IF(@fk_inv_exists = 0,
    'ALTER TABLE inventario ADD CONSTRAINT fk_inventario_calidad_napa FOREIGN KEY (id_calidad_napa) REFERENCES calidades_napa(id_calidad_napa) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "FK already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índice único compuesto
CREATE UNIQUE INDEX IF NOT EXISTS idx_inventario_tipo_calidad ON inventario(tipo_item, id_calidad_napa);

-- 4. MIGRAR datos existentes del inventario de producto_terminado
-- Si existe un registro genérico de producto_terminado, dividirlo por calidades
SET @producto_actual = 0;
SELECT COALESCE(cantidad, 0) INTO @producto_actual 
FROM inventario 
WHERE tipo_item = 'producto_terminado' AND id_calidad_napa IS NULL
LIMIT 1;

-- Eliminar registro genérico si existe
DELETE FROM inventario WHERE tipo_item = 'producto_terminado' AND id_calidad_napa IS NULL;

-- Crear registros por cada calidad de napa (inicialmente en 0)
INSERT IGNORE INTO inventario (tipo_item, id_calidad_napa, cantidad, unidad_medida, stock_minimo)
SELECT 'producto_terminado', id_calidad_napa, 0, 'unidades', 100
FROM calidades_napa
WHERE estado = 'activo';

-- Si había stock genérico, asignarlo proporcionalmente o a la primera calidad
-- (esto es una aproximación, idealmente el usuario debe hacer inventario físico)
UPDATE inventario 
SET cantidad = @producto_actual 
WHERE tipo_item = 'producto_terminado' 
  AND id_calidad_napa = 1
  AND @producto_actual > 0;

-- 5. MODIFICAR kardex para incluir calidad
ALTER TABLE kardex 
ADD COLUMN IF NOT EXISTS id_calidad_napa INT UNSIGNED NULL 
COMMENT 'Para producto_terminado: calidad específica'
AFTER tipo_item;

-- Agregar FK al kardex
SET @fk_kardex_exists = 0;
SELECT COUNT(*) INTO @fk_kardex_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'kardex'
  AND CONSTRAINT_NAME = 'fk_kardex_calidad_napa';

SET @sql = IF(@fk_kardex_exists = 0,
    'ALTER TABLE kardex ADD CONSTRAINT fk_kardex_calidad_napa FOREIGN KEY (id_calidad_napa) REFERENCES calidades_napa(id_calidad_napa) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. RECREAR triggers de producción con asignación automática de calidad

DROP TRIGGER IF EXISTS trg_produccion_cubo_en_uso;
DROP TRIGGER IF EXISTS trg_produccion_aprobada_inventario;

DELIMITER $$

-- Trigger: marcar cubo en uso al insertar producción
CREATE TRIGGER trg_produccion_cubo_en_uso
AFTER INSERT ON producciones
FOR EACH ROW
BEGIN
    -- Marcar cubo como en uso
    UPDATE cubos_fibra 
    SET estado = 'en_uso'
    WHERE id_cubo = NEW.id_cubo 
      AND estado = 'disponible';
END$$

-- Trigger: actualizar inventario y asignar calidad al aprobar producción
CREATE TRIGGER trg_produccion_aprobada_inventario
AFTER UPDATE ON producciones
FOR EACH ROW
BEGIN
    DECLARE v_calidad_napa INT UNSIGNED;
    DECLARE v_peso_bolsas_consumido DECIMAL(12,2);
    DECLARE v_saldo_bolsas DECIMAL(12,2);
    DECLARE v_saldo_producto DECIMAL(12,2);
    DECLARE v_cantidad_producida INT;
    
    -- Solo actuar si cambió a 'aprobado'
    IF OLD.estado_validacion != 'aprobado' AND NEW.estado_validacion = 'aprobado' THEN
        
        -- Obtener la calidad de napa basada en el cubo usado
        SELECT cf.id_calidad_napa_destino
        INTO v_calidad_napa
        FROM cubos_fibra c
        INNER JOIN lotes_fibra l ON c.id_lote = l.id_lote
        LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
        WHERE c.id_cubo = NEW.id_cubo
        LIMIT 1;
        
        -- Si no hay mapeo, usar primera calidad disponible como default
        IF v_calidad_napa IS NULL THEN
            SELECT MIN(id_calidad_napa) INTO v_calidad_napa FROM calidades_napa WHERE estado = 'activo';
        END IF;
        
        -- ACTUALIZAR calidad en la producción
        UPDATE producciones 
        SET id_calidad_napa = v_calidad_napa
        WHERE id_produccion = NEW.id_produccion;
        
        SET v_cantidad_producida = NEW.cantidad_producida;
        SET v_peso_bolsas_consumido = NEW.peso_bolsas_consumido;
        
        -- 1. ACTUALIZAR inventario de bolsas plásticas (SALIDA)
        SELECT cantidad INTO v_saldo_bolsas
        FROM inventario 
        WHERE tipo_item = 'bolsas_plasticas'
        FOR UPDATE;
        
        UPDATE inventario 
        SET cantidad = cantidad - v_peso_bolsas_consumido
        WHERE tipo_item = 'bolsas_plasticas';
        
        INSERT INTO kardex (tipo_item, tipo_movimiento, cantidad, unidad_medida, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, descripcion)
        VALUES ('bolsas_plasticas', 'salida', v_peso_bolsas_consumido, 'kg', v_saldo_bolsas, v_saldo_bolsas - v_peso_bolsas_consumido, 'produccion', NEW.id_produccion, CONCAT('Consumo en producción #', NEW.id_produccion));
        
        -- 2. ACTUALIZAR inventario de producto terminado POR CALIDAD (ENTRADA)
        -- Asegurar que existe el registro de inventario para esta calidad
        INSERT IGNORE INTO inventario (tipo_item, id_calidad_napa, cantidad, unidad_medida, stock_minimo)
        VALUES ('producto_terminado', v_calidad_napa, 0, 'unidades', 100);
        
        SELECT cantidad INTO v_saldo_producto
        FROM inventario 
        WHERE tipo_item = 'producto_terminado' 
          AND id_calidad_napa = v_calidad_napa
        FOR UPDATE;
        
        UPDATE inventario 
        SET cantidad = cantidad + v_cantidad_producida
        WHERE tipo_item = 'producto_terminado'
          AND id_calidad_napa = v_calidad_napa;
        
        INSERT INTO kardex (tipo_item, id_calidad_napa, tipo_movimiento, cantidad, unidad_medida, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, descripcion)
        VALUES ('producto_terminado', v_calidad_napa, 'entrada', v_cantidad_producida, 'unidades', v_saldo_producto, v_saldo_producto + v_cantidad_producida, 'produccion', NEW.id_produccion, CONCAT('Producción #', NEW.id_produccion, ' aprobada'));
        
        -- 3. ACTUALIZAR cubo: incrementar cantidad producida real
        UPDATE cubos_fibra 
        SET cantidad_producida_real = COALESCE(cantidad_producida_real, 0) + v_cantidad_producida,
            estado = IF(
                (cantidad_estimada_bolsas - (COALESCE(cantidad_producida_real, 0) + v_cantidad_producida)) <= 0,
                'agotado',
                'en_uso'
            )
        WHERE id_cubo = NEW.id_cubo;
        
        -- 4. ACTUALIZAR lote: recalcular totales desde cubos
        UPDATE lotes_fibra l
        SET 
            cantidad_producida_real = (
                SELECT COALESCE(SUM(c.cantidad_producida_real), 0)
                FROM cubos_fibra c
                WHERE c.id_lote = l.id_lote
            ),
            cantidad_estimada_bolsas = (
                SELECT COALESCE(SUM(c.cantidad_estimada_bolsas), 0)
                FROM cubos_fibra c
                WHERE c.id_lote = l.id_lote
            ),
            estado = CASE
                WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'agotado') = (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote) THEN 'agotado'
                WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'en_uso') > 0 THEN 'en_proceso'
                ELSE 'disponible'
            END
        WHERE l.id_lote = (SELECT id_lote FROM cubos_fibra WHERE id_cubo = NEW.id_cubo);
        
    END IF;
END$$

DELIMITER ;

-- 7. CREAR vista de inventario por calidad
CREATE OR REPLACE VIEW v_inventario_por_calidad AS
SELECT 
    i.tipo_item,
    CASE 
        WHEN i.tipo_item = 'producto_terminado' THEN cn.nombre
        ELSE 'N/A'
    END AS calidad,
    i.id_calidad_napa,
    i.cantidad,
    i.unidad_medida,
    i.stock_minimo,
    CASE
        WHEN i.cantidad <= i.stock_minimo THEN 'Bajo'
        WHEN i.cantidad <= (i.stock_minimo * 1.5) THEN 'Medio'
        ELSE 'Normal'
    END AS nivel_stock,
    i.fecha_ultima_actualizacion
FROM inventario i
LEFT JOIN calidades_napa cn ON i.id_calidad_napa = cn.id_calidad_napa
ORDER BY i.tipo_item, cn.codigo;

-- 8. CREAR vista de stock disponible para ventas
CREATE OR REPLACE VIEW v_stock_ventas AS
SELECT 
    cn.id_calidad_napa,
    cn.nombre AS calidad_napa,
    cn.codigo,
    cn.precio_base_sugerido,
    COALESCE(i.cantidad, 0) AS stock_disponible,
    i.unidad_medida,
    CASE
        WHEN COALESCE(i.cantidad, 0) = 0 THEN 'Sin stock'
        WHEN i.cantidad <= i.stock_minimo THEN 'Stock bajo'
        ELSE 'Disponible'
    END AS estado_stock
FROM calidades_napa cn
LEFT JOIN inventario i ON i.tipo_item = 'producto_terminado' AND i.id_calidad_napa = cn.id_calidad_napa
WHERE cn.estado = 'activo'
ORDER BY cn.codigo;

-- ============================================================================
-- FIN DE MIGRACIÓN
-- ============================================================================

-- Verificación
SELECT 'Mapeo calidades fibra → napa:' AS info;
SELECT cf.nombre AS calidad_fibra, cn.nombre AS calidad_napa_destino
FROM calidades_fibra cf
LEFT JOIN calidades_napa cn ON cf.id_calidad_napa_destino = cn.id_calidad_napa;

SELECT 'Inventario por calidad:' AS info;
SELECT * FROM v_inventario_por_calidad;

SELECT 'Stock para ventas:' AS info;
SELECT * FROM v_stock_ventas;
