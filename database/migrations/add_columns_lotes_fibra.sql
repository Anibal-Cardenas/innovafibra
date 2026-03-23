-- ============================================================================
-- MIGRACIÓN: Agregar columnas faltantes a lotes_fibra
-- Fecha: 14 de Enero, 2026
-- ============================================================================

USE sistema_napa;

-- Agregar columna numero_cubos si no existe
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'lotes_fibra'
  AND COLUMN_NAME = 'numero_cubos';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE lotes_fibra ADD COLUMN numero_cubos INT UNSIGNED NOT NULL DEFAULT 1 COMMENT "Cantidad de fardos/cubos" AFTER id_calidad_fibra',
    'SELECT "Column numero_cubos already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna numero_guia si no existe
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sistema_napa'
  AND TABLE_NAME = 'lotes_fibra'
  AND COLUMN_NAME = 'numero_guia';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE lotes_fibra ADD COLUMN numero_guia VARCHAR(50) NULL COMMENT "Número de guía de remisión" AFTER numero_cubos',
    'SELECT "Column numero_guia already exists" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT '✅ Columnas numero_cubos y numero_guia agregadas/verificadas en lotes_fibra' as resultado;
