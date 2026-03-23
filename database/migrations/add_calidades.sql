-- ============================================================================
-- MIGRACIÓN: Añadir sistema de calidades para insumos y productos
-- Sistema de Gestión de Producción - Taller de Napa
-- Versión: 1.1
-- Fecha: 09 de Enero, 2026
-- ============================================================================

USE `sistema_napa`;

-- ============================================================================
-- TABLA: calidades_insumo
-- ============================================================================

CREATE TABLE IF NOT EXISTS `calidades_insumo` (
  `id_calidad_insumo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL COMMENT 'Ej: Premium, Standard, Económico',
  `codigo` VARCHAR(10) NOT NULL COMMENT 'Ej: A, B, C',
  `descripcion` TEXT NULL,
  `factor_calidad` DECIMAL(5,4) NOT NULL DEFAULT 1.0000 COMMENT 'Factor multiplicador de rendimiento',
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_calidad_insumo`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Calidades de insumos (bolsas plásticas)';

-- ============================================================================
-- TABLA: calidades_producto
-- ============================================================================

CREATE TABLE IF NOT EXISTS `calidades_producto` (
  `id_calidad_producto` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL COMMENT 'Ej: Premium, Standard, Económico',
  `codigo` VARCHAR(10) NOT NULL COMMENT 'Ej: A, B, C',
  `descripcion` TEXT NULL,
  `precio_base_sugerido` DECIMAL(10,2) NULL COMMENT 'Precio sugerido de venta',
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_calidad_producto`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Calidades de producto terminado';

-- ============================================================================
-- MODIFICAR TABLAS EXISTENTES
-- ============================================================================

-- Añadir calidad a compras de bolsas
ALTER TABLE `compras_bolsas`
ADD COLUMN `id_calidad_insumo` INT UNSIGNED NULL AFTER `tipo_bolsa`,
ADD INDEX `fk_compra_bolsa_calidad_idx` (`id_calidad_insumo` ASC),
ADD CONSTRAINT `fk_compra_bolsa_calidad`
  FOREIGN KEY (`id_calidad_insumo`)
  REFERENCES `calidades_insumo` (`id_calidad_insumo`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

-- Añadir calidad a producciones
ALTER TABLE `producciones`
ADD COLUMN `id_calidad_producto` INT UNSIGNED NULL AFTER `cantidad_producida`,
ADD INDEX `fk_prod_calidad_idx` (`id_calidad_producto` ASC),
ADD CONSTRAINT `fk_prod_calidad`
  FOREIGN KEY (`id_calidad_producto`)
  REFERENCES `calidades_producto` (`id_calidad_producto`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- Añadir calidad a ventas
ALTER TABLE `ventas`
ADD COLUMN `id_calidad_producto` INT UNSIGNED NULL AFTER `cantidad_vendida`,
ADD INDEX `fk_venta_calidad_idx` (`id_calidad_producto` ASC),
ADD CONSTRAINT `fk_venta_calidad`
  FOREIGN KEY (`id_calidad_producto`)
  REFERENCES `calidades_producto` (`id_calidad_producto`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- ============================================================================
-- DATOS INICIALES: Calidades predefinidas
-- ============================================================================

-- Calidades de insumo (bolsas plásticas)
INSERT INTO `calidades_insumo` (`nombre`, `codigo`, `descripcion`, `factor_calidad`) VALUES
('Premium', 'A', 'Bolsas plásticas de alta calidad, mayor resistencia y durabilidad', 1.0500),
('Standard', 'B', 'Bolsas plásticas de calidad estándar', 1.0000),
('Económico', 'C', 'Bolsas plásticas de calidad económica', 0.9500);

-- Calidades de producto terminado
INSERT INTO `calidades_producto` (`nombre`, `codigo`, `descripcion`, `precio_base_sugerido`) VALUES
('Premium', 'A', 'Producto de alta calidad con insumos premium', 15.00),
('Standard', 'B', 'Producto de calidad estándar', 12.00),
('Económico', 'C', 'Producto de calidad económica', 10.00);

-- ============================================================================
-- NUEVA VISTA: Inventario por calidad de producto
-- ============================================================================

CREATE OR REPLACE VIEW `v_inventario_por_calidad` AS
SELECT 
    cp.codigo as codigo_calidad,
    cp.nombre as calidad,
    SUM(v.cantidad_vendida) as total_vendido,
    COUNT(DISTINCT v.id_venta) as num_ventas
FROM calidades_producto cp
LEFT JOIN ventas v ON cp.id_calidad_producto = v.id_calidad_producto
GROUP BY cp.id_calidad_producto, cp.codigo, cp.nombre;

-- ============================================================================
-- ACTUALIZAR STORED PROCEDURE: Calcular costo unitario con calidad
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_calcular_costo_unitario`;

DELIMITER $$
CREATE PROCEDURE `sp_calcular_costo_unitario`(
    IN p_id_calidad_insumo INT
)
BEGIN
    DECLARE v_costo_fibra DECIMAL(12,4);
    DECLARE v_costo_bolsas DECIMAL(12,4);
    DECLARE v_costo_mano_obra DECIMAL(12,4);
    DECLARE v_costo_total DECIMAL(12,4);
    DECLARE v_factor_calidad DECIMAL(5,4);
    
    -- Obtener costo promedio de fibra (últimas 5 compras)
    SELECT AVG(precio_por_kg) INTO v_costo_fibra
    FROM lotes_fibra
    ORDER BY id_lote DESC
    LIMIT 5;
    
    -- Obtener costo promedio de bolsas con calidad específica
    IF p_id_calidad_insumo IS NOT NULL THEN
        SELECT AVG(cb.precio_por_kg), ci.factor_calidad
        INTO v_costo_bolsas, v_factor_calidad
        FROM compras_bolsas cb
        INNER JOIN calidades_insumo ci ON cb.id_calidad_insumo = ci.id_calidad_insumo
        WHERE cb.id_calidad_insumo = p_id_calidad_insumo
        ORDER BY cb.id_compra_bolsa DESC
        LIMIT 5;
    ELSE
        -- Si no hay calidad, usar promedio general
        SELECT AVG(precio_por_kg) INTO v_costo_bolsas
        FROM compras_bolsas
        ORDER BY id_compra_bolsa DESC
        LIMIT 5;
        
        SET v_factor_calidad = 1.0000;
    END IF;
    
    -- Costo de mano de obra promedio
    SELECT AVG(tarifa_por_bolsa) INTO v_costo_mano_obra
    FROM usuarios
    WHERE rol = 'trabajador' AND estado = 'activo';
    
    -- Calcular costo total considerando factor de calidad
    SET v_costo_fibra = COALESCE(v_costo_fibra, 0);
    SET v_costo_bolsas = COALESCE(v_costo_bolsas, 0) * 0.02 * v_factor_calidad; -- 0.02 kg por bolsa
    SET v_costo_mano_obra = COALESCE(v_costo_mano_obra, 0);
    
    SET v_costo_total = v_costo_fibra + v_costo_bolsas + v_costo_mano_obra;
    
    SELECT 
        v_costo_total AS costo_unitario,
        v_costo_fibra AS costo_fibra,
        v_costo_bolsas AS costo_bolsas,
        v_costo_mano_obra AS costo_mano_obra,
        v_factor_calidad AS factor_calidad;
END$$
DELIMITER ;

-- ============================================================================
-- FIN DE LA MIGRACIÓN
-- ============================================================================
