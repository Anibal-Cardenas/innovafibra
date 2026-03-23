-- ============================================================================
-- MIGRACIÓN: Correcciones Críticas del Sistema
-- Fecha: 14 de Enero, 2026
-- Descripción: Corrige problemas de integridad y añade columnas faltantes
-- ============================================================================

USE sistema_napa;

-- ============================================================================
-- 1. VERIFICAR Y CREAR TABLA cubos_fibra SI NO EXISTE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `cubos_fibra` (
  `id_cubo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_lote` INT UNSIGNED NOT NULL,
  `numero_cubo` INT UNSIGNED NOT NULL COMMENT 'Número secuencial dentro del lote',
  `peso_bruto` DECIMAL(10,2) NOT NULL COMMENT 'Peso bruto individual del cubo',
  `peso_neto` DECIMAL(10,2) NOT NULL COMMENT 'Peso neto individual del cubo',
  `estado` ENUM('disponible', 'en_uso', 'agotado') NOT NULL DEFAULT 'disponible',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cubo`),
  INDEX `fk_cubo_lote_idx` (`id_lote` ASC),
  INDEX `idx_estado` (`estado` ASC),
  CONSTRAINT `fk_cubo_lote`
    FOREIGN KEY (`id_lote`)
    REFERENCES `lotes_fibra` (`id_lote`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cubos individuales de fibra por lote';

-- Agregar columnas adicionales a cubos_fibra si no existen
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'cubos_fibra'
  AND COLUMN_NAME = 'cantidad_estimada_bolsas';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE cubos_fibra ADD COLUMN cantidad_estimada_bolsas INT UNSIGNED NULL COMMENT "Bolsas estimadas de este cubo"',
    'SELECT "Column cantidad_estimada_bolsas already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'cubos_fibra'
  AND COLUMN_NAME = 'rendimiento_estimado';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE cubos_fibra ADD COLUMN rendimiento_estimado DECIMAL(10,4) NULL COMMENT "Bolsas por kg estimado"',
    'SELECT "Column rendimiento_estimado already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 2. CORREGIR TABLA entregas - Añadir columnas faltantes
-- ============================================================================

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'entregas'
  AND COLUMN_NAME = 'fecha_entrega_estimada';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE entregas ADD COLUMN fecha_entrega_estimada DATE NULL COMMENT "Fecha estimada de entrega" AFTER fecha_entrega',
    'SELECT "Column fecha_entrega_estimada already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'entregas'
  AND COLUMN_NAME = 'estado_entrega';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE entregas ADD COLUMN estado_entrega ENUM("pendiente", "en_ruta", "entregado", "cancelado") NOT NULL DEFAULT "pendiente" AFTER fecha_entrega_estimada',
    'SELECT "Column estado_entrega already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Hacer direccion_entrega opcional si es requerida
SET @col_nullable = 0;
SELECT COUNT(*) INTO @col_nullable
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'entregas'
  AND COLUMN_NAME = 'direccion_entrega'
  AND IS_NULLABLE = 'NO';

SET @sql = IF(@col_nullable = 1,
    'ALTER TABLE entregas MODIFY COLUMN direccion_entrega VARCHAR(255) NULL',
    'SELECT "Column direccion_entrega already nullable" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 3. VERIFICAR Y CREAR TABLA calidades_napa SI NO EXISTE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `calidades_napa` (
  `id_calidad_napa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL COMMENT 'Código corto ej: A, B, C',
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo',
  `descripcion` TEXT NULL,
  `precio_base_sugerido` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio base sugerido',
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_calidad_napa`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC),
  INDEX `idx_estado` (`estado` ASC),
  CONSTRAINT `fk_calidad_napa_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Calidades del producto terminado (napa)';

-- Insertar calidades por defecto si la tabla está vacía
INSERT IGNORE INTO calidades_napa (codigo, nombre, descripcion, precio_base_sugerido, estado) VALUES
('A', 'Calidad Premium', 'Napa de calidad superior', 25.00, 'activo'),
('B', 'Calidad Estándar', 'Napa de calidad estándar', 20.00, 'activo'),
('C', 'Calidad Económica', 'Napa de calidad económica', 15.00, 'activo');

-- ============================================================================
-- 4. AGREGAR COLUMNA id_calidad_napa A ventas SI NO EXISTE
-- ============================================================================

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'ventas'
  AND COLUMN_NAME = 'id_calidad_napa';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE ventas ADD COLUMN id_calidad_napa INT UNSIGNED NULL COMMENT "Calidad del producto vendido" AFTER id_cliente',
    'SELECT "Column id_calidad_napa already exists in ventas" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar FK si no existe
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'ventas'
  AND CONSTRAINT_NAME = 'fk_venta_calidad_napa';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE ventas ADD CONSTRAINT fk_venta_calidad_napa FOREIGN KEY (id_calidad_napa) REFERENCES calidades_napa(id_calidad_napa) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_venta_calidad_napa already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 5. AGREGAR COLUMNA id_calidad_napa A inventario SI NO EXISTE
-- ============================================================================

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'inventario'
  AND COLUMN_NAME = 'id_calidad_napa';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE inventario ADD COLUMN id_calidad_napa INT UNSIGNED NULL COMMENT "Calidad del producto (solo para producto_terminado)" AFTER tipo_item',
    'SELECT "Column id_calidad_napa already exists in inventario" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar FK si no existe
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'inventario'
  AND CONSTRAINT_NAME = 'fk_inventario_calidad_napa';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE inventario ADD CONSTRAINT fk_inventario_calidad_napa FOREIGN KEY (id_calidad_napa) REFERENCES calidades_napa(id_calidad_napa) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_inventario_calidad_napa already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Hacer tipo_item + id_calidad_napa único (remover UNIQUE de tipo_item)
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'inventario'
  AND INDEX_NAME = 'tipo_item_UNIQUE';

SET @sql = IF(@idx_exists = 1,
    'ALTER TABLE inventario DROP INDEX tipo_item_UNIQUE',
    'SELECT "Index tipo_item_UNIQUE already removed" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índice compuesto
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'inventario'
  AND INDEX_NAME = 'idx_tipo_calidad';

SET @sql = IF(@idx_exists = 0,
    'CREATE UNIQUE INDEX idx_tipo_calidad ON inventario(tipo_item, COALESCE(id_calidad_napa, 0))',
    'SELECT "Index idx_tipo_calidad already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 6. CREAR VISTA v_stock_ventas SI NO EXISTE
-- ============================================================================

DROP VIEW IF EXISTS v_stock_ventas;

CREATE VIEW v_stock_ventas AS
SELECT 
    cn.id_calidad_napa as id_calidad,
    cn.codigo,
    cn.nombre as calidad_napa,
    cn.precio_base_sugerido as precio,
    COALESCE(i.cantidad, 0) as stock_disponible,
    CASE 
        WHEN COALESCE(i.cantidad, 0) > 50 THEN 'DISPONIBLE'
        WHEN COALESCE(i.cantidad, 0) > 0 THEN 'BAJO STOCK'
        ELSE 'SIN STOCK'
    END as estado_stock
FROM calidades_napa cn
LEFT JOIN inventario i ON i.tipo_item = 'producto_terminado' AND i.id_calidad_napa = cn.id_calidad_napa
WHERE cn.estado = 'activo'
ORDER BY cn.codigo;

-- ============================================================================
-- 7. AGREGAR ÍNDICES PARA MEJORAR PERFORMANCE
-- ============================================================================

-- Índice en clientes para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_clientes_nombre ON clientes(nombre);

-- Índice en ventas para reportes
CREATE INDEX IF NOT EXISTS idx_ventas_fecha_cliente ON ventas(fecha_venta, id_cliente);

-- ============================================================================
-- 8. MIGRAR DATOS EXISTENTES SI ES NECESARIO
-- ============================================================================

-- Si hay lotes sin cubos, crear cubo único por cada lote
INSERT INTO cubos_fibra (id_lote, numero_cubo, peso_bruto, peso_neto, estado)
SELECT 
    id_lote,
    1 as numero_cubo,
    peso_bruto,
    peso_neto,
    CASE 
        WHEN estado = 'agotado' THEN 'agotado'
        WHEN cantidad_producida_real > 0 THEN 'en_uso'
        ELSE 'disponible'
    END as estado
FROM lotes_fibra
WHERE NOT EXISTS (
    SELECT 1 FROM cubos_fibra WHERE cubos_fibra.id_lote = lotes_fibra.id_lote
);

-- Si no hay registros de inventario por calidad, crear entradas iniciales
INSERT IGNORE INTO inventario (tipo_item, id_calidad_napa, cantidad, unidad_medida, stock_minimo)
SELECT 
    'producto_terminado',
    id_calidad_napa,
    0,
    'unidades',
    50
FROM calidades_napa
WHERE estado = 'activo';

-- ============================================================================
-- FIN DE LA MIGRACIÓN
-- ============================================================================

SELECT '✅ Migración completada exitosamente' as resultado;
SELECT 'Tablas y columnas críticas verificadas y corregidas' as info1;
SELECT 'Vista v_stock_ventas creada' as info2;
SELECT 'Índices optimizados' as info3;
