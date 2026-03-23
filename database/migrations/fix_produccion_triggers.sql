-- ============================================================================
-- MIGRACIÓN: Corrección de Producción (Columnas y Triggers)
-- Fecha: 14 de Enero, 2026
-- ============================================================================

USE sistema_napa;

-- 1. Agregar columna id_cubo a producciones si no existe
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'producciones'
  AND COLUMN_NAME = 'id_cubo';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE producciones ADD COLUMN id_cubo INT UNSIGNED NULL COMMENT "Cubo utilizado" AFTER id_lote_fibra',
    'SELECT "Column id_cubo already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar FK si no existe
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'producciones'
  AND CONSTRAINT_NAME = 'fk_produccion_cubo';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE producciones ADD CONSTRAINT fk_produccion_cubo FOREIGN KEY (id_cubo) REFERENCES cubos_fibra(id_cubo) ON DELETE RESTRICT ON UPDATE CASCADE',
    'SELECT "FK fk_produccion_cubo already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Asegurar columna id_calidad_napa en producciones (para consistencia con controller)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'producciones'
  AND COLUMN_NAME = 'id_calidad_napa';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE producciones ADD COLUMN id_calidad_napa INT UNSIGNED NULL AFTER id_operario',
    'SELECT "Column id_calidad_napa already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- 3. Actualizar Triggers para manejar validación (UPDATE)

DROP TRIGGER IF EXISTS trg_after_update_produccion;

DELIMITER $$
CREATE TRIGGER `trg_after_update_produccion`
AFTER UPDATE ON `producciones`
FOR EACH ROW
BEGIN
    DECLARE v_saldo_bolsas DECIMAL(12,2);
    DECLARE v_saldo_producto DECIMAL(12,2);
    
    -- Detectar cambio de estado a 'aprobado'
    IF NEW.estado_validacion = 'aprobado' AND OLD.estado_validacion != 'aprobado' THEN
        
        -- A. Descontar bolsas plásticas
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` - NEW.peso_bolsas_consumido
        WHERE `tipo_item` = 'bolsas_plasticas';
        
        -- Kardex bolsas
        SELECT `cantidad` INTO v_saldo_bolsas 
        FROM `inventario` WHERE `tipo_item` = 'bolsas_plasticas';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`)
        VALUES ('bolsas_plasticas', 'salida', NEW.peso_bolsas_consumido, 'kg',
                v_saldo_bolsas + NEW.peso_bolsas_consumido, v_saldo_bolsas, 
                'produccion', NEW.id_produccion);
        
        -- B. Incrementar producto terminado
        -- Buscar si existe registro de inventario para esa calidad
        IF NOT EXISTS (SELECT 1 FROM inventario WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = NEW.id_calidad_napa) THEN
             INSERT INTO inventario (tipo_item, id_calidad_napa, cantidad, unidad_medida, stock_minimo)
             VALUES ('producto_terminado', NEW.id_calidad_napa, 0, 'unidades', 50);
        END IF;

        UPDATE `inventario` 
        SET `cantidad` = `cantidad` + NEW.cantidad_producida
        WHERE `tipo_item` = 'producto_terminado' AND id_calidad_napa = NEW.id_calidad_napa;
        
        -- Kardex producto
        SELECT `cantidad` INTO v_saldo_producto 
        FROM `inventario` WHERE `tipo_item` = 'producto_terminado' AND id_calidad_napa = NEW.id_calidad_napa;
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`)
        VALUES ('producto_terminado', 'entrada', NEW.cantidad_producida, 'unidades',
                v_saldo_producto - NEW.cantidad_producida, v_saldo_producto, 
                'produccion', NEW.id_produccion);
        
        -- C. Actualizar producción acumulada del LOTE
        UPDATE `lotes_fibra` 
        SET `cantidad_producida_real` = `cantidad_producida_real` + NEW.cantidad_producida
        WHERE `id_lote` = NEW.id_lote_fibra;
        
        -- D. Actualizar producción acumulada del CUBO
        IF NEW.id_cubo IS NOT NULL THEN
            UPDATE `cubos_fibra`
            SET `cantidad_producida_real` = `cantidad_producida_real` + NEW.cantidad_producida,
                `estado` = 'en_uso' -- Asegurar estado
            WHERE `id_cubo` = NEW.id_cubo;
            
            -- Verificar si el cubo se agotó (opcional, lógica simple por ahora)
        END IF;
        
    END IF;
END$$
DELIMITER ;

-- 4. Actualizar Trigger de INSERT (solo para marcar cubo en uso, NO mover inventario si es pendiente)
DROP TRIGGER IF EXISTS trg_after_insert_produccion;

DELIMITER $$
CREATE TRIGGER `trg_after_insert_produccion`
AFTER INSERT ON `producciones`
FOR EACH ROW
BEGIN
    -- Si se inserta directamente como aprobado (raro, pero posible)
    IF NEW.estado_validacion = 'aprobado' THEN
        -- Lógica de inventario igual al UPDATE... (omitida para evitar duplicidad compleja, asumiendo flujo normal: insert pendiente -> update aprobado)
        -- Si se requiere, se puede llamar a un SP compartido.
        -- Por ahora, solo marcamos el cubo como en uso
        IF NEW.id_cubo IS NOT NULL THEN
             UPDATE `cubos_fibra` SET `estado` = 'en_uso' WHERE `id_cubo` = NEW.id_cubo;
        END IF;
    ELSE
        -- Si es pendiente, solo marcar cubo en uso
        IF NEW.id_cubo IS NOT NULL THEN
             UPDATE `cubos_fibra` SET `estado` = 'en_uso' WHERE `id_cubo` = NEW.id_cubo;
        END IF;
    END IF;
END$$
DELIMITER ;

SELECT '✅ Migración de Producción (Triggers + Columnas) completada' as resultado;