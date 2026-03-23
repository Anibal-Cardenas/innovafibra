-- ============================================================================
-- MIGRACIÓN: Otros Ingresos
-- Fecha: 31 de Enero, 2026
-- Descripción: Creación de tabla para registrar otros ingresos que suman a la utilidad
-- ============================================================================

USE sistema_napa;

CREATE TABLE IF NOT EXISTS `otros_ingresos` (
  `id_ingreso` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha_ingreso` DATE NOT NULL,
  `categoria` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `monto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_ingreso`),
  INDEX `idx_fecha` (`fecha_ingreso`),
  INDEX `idx_categoria` (`categoria`),
  CONSTRAINT `fk_ingreso_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de otros ingresos que suman a la utilidad';

SELECT '✅ Tabla otros_ingresos creada correctamente.' as resultado;
