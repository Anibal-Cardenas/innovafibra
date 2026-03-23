-- ============================================================================
-- CORRECCIÓN: Sistema de Calidades de FIBRA (no bolsas)
-- La calidad del producto (napa) depende de la calidad de la FIBRA usada
-- ============================================================================

USE sistema_napa;

-- 1. ELIMINAR sistema incorrecto de calidades de bolsas
SET FOREIGN_KEY_CHECKS=0;

-- Eliminar FK primero
ALTER TABLE compras_bolsas DROP FOREIGN KEY IF EXISTS fk_compra_bolsa_calidad;

-- Eliminar columna
ALTER TABLE compras_bolsas DROP COLUMN IF EXISTS id_calidad_insumo;

-- Eliminar tabla
DROP TABLE IF EXISTS calidades_insumo;

SET FOREIGN_KEY_CHECKS=1;

-- 2. CREAR tabla de calidades de FIBRA (gestionable por admin)
CREATE TABLE IF NOT EXISTS `calidades_fibra` (
  `id_calidad_fibra` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Ej: Virgen, Cristalizada, Reciclada, etc.',
  `descripcion` TEXT NULL,
  `factor_precio` DECIMAL(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Multiplicador de precio base',
  `color` VARCHAR(20) NULL COMMENT 'Color badge en UI (success/warning/info)',
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_calidad_fibra`),
  UNIQUE INDEX `nombre_UNIQUE` (`nombre` ASC),
  INDEX `idx_estado` (`estado` ASC),
  CONSTRAINT `fk_calidad_fibra_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Calidades de fibra configurables por el administrador';

-- 3. AGREGAR columna de calidad a lotes_fibra (si no existe)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'lotes_fibra'
  AND COLUMN_NAME = 'id_calidad_fibra';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE lotes_fibra ADD COLUMN id_calidad_fibra INT UNSIGNED NULL AFTER id_proveedor',
    'SELECT "Column already exists" AS info');
    
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar FK si no existe
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'lotes_fibra'
  AND CONSTRAINT_NAME = 'fk_lote_calidad_fibra';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE lotes_fibra ADD CONSTRAINT fk_lote_calidad_fibra FOREIGN KEY (id_calidad_fibra) REFERENCES calidades_fibra(id_calidad_fibra) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK already exists" AS info');
    
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE INDEX IF NOT EXISTS idx_lote_calidad ON lotes_fibra(id_calidad_fibra);

-- 4. RENOMBRAR tabla calidades_producto a calidades_napa (más claro)
-- Y actualizar referencias (solo si existe)
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'calidades_producto';

SET @sql = IF(@table_exists = 1,
    'ALTER TABLE calidades_producto RENAME TO calidades_napa',
    'SELECT "Table already renamed or does not exist" AS info');
    
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar si la tabla calidades_napa existe
SET @napa_exists = 0;
SELECT COUNT(*) INTO @napa_exists
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'calidades_napa';

-- Solo renombrar columna si tabla existe y columna no está renombrada aún
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'calidades_napa'
  AND COLUMN_NAME = 'id_calidad_producto';

SET @sql = IF(@col_exists = 1 AND @napa_exists = 1,
    'ALTER TABLE calidades_napa CHANGE id_calidad_producto id_calidad_napa INT UNSIGNED NOT NULL AUTO_INCREMENT',
    'SELECT "Column already renamed" AS info');
    
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar foreign keys en producciones (verificar nombre actual)
SET @prod_col_name = NULL;
SELECT COLUMN_NAME INTO @prod_col_name
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'producciones'
  AND COLUMN_NAME IN ('id_calidad_producto', 'id_calidad_napa')
LIMIT 1;

-- Solo cambiar si se llama id_calidad_producto
SET @sql = IF(@prod_col_name = 'id_calidad_producto',
    'ALTER TABLE producciones CHANGE id_calidad_producto id_calidad_napa INT UNSIGNED NULL',
    'SELECT "producciones already updated" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Eliminar FK vieja si existe
SET @fk_prod_exists = 0;
SELECT COUNT(*) INTO @fk_prod_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'producciones'
  AND CONSTRAINT_NAME = 'fk_produccion_calidad_producto';

SET @sql = IF(@fk_prod_exists = 1,
    'ALTER TABLE producciones DROP FOREIGN KEY fk_produccion_calidad_producto',
    'SELECT "FK already updated" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar FK nueva si no existe
SET @fk_prod_new_exists = 0;
SELECT COUNT(*) INTO @fk_prod_new_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'producciones'
  AND CONSTRAINT_NAME = 'fk_produccion_calidad_napa';

SET @sql = IF(@fk_prod_new_exists = 0,
    'ALTER TABLE producciones ADD CONSTRAINT fk_produccion_calidad_napa FOREIGN KEY (id_calidad_napa) REFERENCES calidades_napa(id_calidad_napa) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar foreign keys en ventas (verificar nombre actual)
SET @ventas_col_name = NULL;
SELECT COLUMN_NAME INTO @ventas_col_name
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'ventas'
  AND COLUMN_NAME IN ('id_calidad_producto', 'id_calidad_napa')
LIMIT 1;

-- Solo cambiar si se llama id_calidad_producto
SET @sql = IF(@ventas_col_name = 'id_calidad_producto',
    'ALTER TABLE ventas CHANGE id_calidad_producto id_calidad_napa INT UNSIGNED NULL',
    'SELECT "ventas already updated" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Eliminar FK vieja si existe
SET @fk_ventas_exists = 0;
SELECT COUNT(*) INTO @fk_ventas_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'ventas'
  AND CONSTRAINT_NAME = 'fk_venta_calidad_producto';

SET @sql = IF(@fk_ventas_exists = 1,
    'ALTER TABLE ventas DROP FOREIGN KEY fk_venta_calidad_producto',
    'SELECT "FK already updated" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar FK nueva si no existe
SET @fk_ventas_new_exists = 0;
SELECT COUNT(*) INTO @fk_ventas_new_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'ventas'
  AND CONSTRAINT_NAME = 'fk_venta_calidad_napa';

SET @sql = IF(@fk_ventas_new_exists = 0,
    'ALTER TABLE ventas ADD CONSTRAINT fk_venta_calidad_napa FOREIGN KEY (id_calidad_napa) REFERENCES calidades_napa(id_calidad_napa) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. INSERTAR calidades de fibra iniciales (ejemplos comunes)
INSERT IGNORE INTO calidades_fibra (nombre, descripcion, factor_precio, color, estado) VALUES
('Fibra Virgen', 'Fibra de primera calidad, sin procesar previamente', 1.20, 'success', 'activo'),
('Fibra Cristalizada', 'Fibra procesada con características especiales', 1.00, 'info', 'activo'),
('Fibra Reciclada Premium', 'Fibra reciclada de alta calidad', 0.85, 'warning', 'activo'),
('Fibra Estándar', 'Fibra de calidad estándar para uso general', 0.80, 'secondary', 'activo');

-- 6. ACTUALIZAR calidades_napa con nombres más descriptivos
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE calidades_napa;
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO calidades_napa (nombre, codigo, precio_base_sugerido, estado) VALUES
('Napa Premium (Fibra Virgen)', 'A', 15.00, 'activo'),
('Napa Estándar (Fibra Cristalizada)', 'B', 12.00, 'activo'),
('Napa Económica (Fibra Reciclada)', 'C', 10.00, 'activo'),
('Napa Básica', 'D', 8.00, 'activo');

-- 7. ACTUALIZAR stored procedure para calcular costo con calidad de fibra
DROP PROCEDURE IF EXISTS sp_calcular_costo_unitario;

DELIMITER $$
CREATE PROCEDURE sp_calcular_costo_unitario(
    IN p_id_produccion INT
)
BEGIN
    DECLARE v_costo_fibra DECIMAL(12,4);
    DECLARE v_costo_bolsas DECIMAL(12,4);
    DECLARE v_costo_mano_obra DECIMAL(12,4);
    DECLARE v_cantidad DECIMAL(12,2);
    DECLARE v_factor_calidad DECIMAL(5,2);
    DECLARE v_costo_total DECIMAL(12,4);
    
    -- Obtener factor de calidad de la fibra
    SELECT COALESCE(cf.factor_precio, 1.00)
    INTO v_factor_calidad
    FROM producciones pr
    INNER JOIN lotes_fibra l ON pr.id_lote_fibra = l.id_lote
    LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
    WHERE pr.id_produccion = p_id_produccion;
    
    -- Calcular costos
    SELECT 
        ((l.precio_total / l.peso_neto) * (l.peso_neto / NULLIF(l.cantidad_producida_real, 0))) * v_factor_calidad,
        pr.peso_bolsas_consumido * (SELECT AVG(precio_por_kg) FROM compras_bolsas ORDER BY id_compra_bolsa DESC LIMIT 5),
        u.tarifa_por_bolsa,
        pr.cantidad_producida
    INTO v_costo_fibra, v_costo_bolsas, v_costo_mano_obra, v_cantidad
    FROM producciones pr
    INNER JOIN lotes_fibra l ON pr.id_lote_fibra = l.id_lote
    INNER JOIN usuarios u ON pr.id_operario = u.id_usuario
    WHERE pr.id_produccion = p_id_produccion;
    
    SET v_costo_total = (v_costo_fibra + v_costo_bolsas + v_costo_mano_obra) / NULLIF(v_cantidad, 0);
    
    SELECT COALESCE(v_costo_total, 0) AS costo_unitario;
END$$
DELIMITER ;

-- 8. CREAR vista de inventario por calidad de fibra
CREATE OR REPLACE VIEW v_inventario_por_calidad_fibra AS
SELECT 
    cf.nombre AS calidad_fibra,
    cf.factor_precio,
    SUM(l.peso_neto - (l.cantidad_producida_real * 0.02)) AS kg_disponibles,
    COUNT(l.id_lote) AS lotes_activos,
    SUM(l.cantidad_estimada_bolsas - l.cantidad_producida_real) AS bolsas_pendientes
FROM lotes_fibra l
INNER JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
WHERE l.estado IN ('disponible', 'en_proceso')
GROUP BY cf.id_calidad_fibra, cf.nombre, cf.factor_precio;

-- 9. CREAR vista de producción por calidad
CREATE OR REPLACE VIEW v_produccion_por_calidad AS
SELECT 
    DATE(pr.fecha_produccion) AS fecha,
    cf.nombre AS calidad_fibra,
    cn.nombre AS calidad_napa,
    COUNT(pr.id_produccion) AS num_producciones,
    SUM(pr.cantidad_producida) AS total_producido,
    AVG(pr.eficiencia_porcentual) AS eficiencia_promedio
FROM producciones pr
INNER JOIN lotes_fibra l ON pr.id_lote_fibra = l.id_lote
LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
LEFT JOIN calidades_napa cn ON pr.id_calidad_napa = cn.id_calidad_napa
WHERE pr.estado_validacion = 'aprobado'
GROUP BY DATE(pr.fecha_produccion), cf.id_calidad_fibra, cn.id_calidad_napa;

-- ============================================================================
-- FIN DE CORRECCIÓN
-- ============================================================================
