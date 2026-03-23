-- ============================================================================
-- MODELO DE DATOS RELACIONAL
-- Sistema de Gestión de Producción - Taller de Napa
-- Motor: MySQL 5.7+
-- Versión: 1.0
-- Fecha: 09 de Enero, 2026
-- ============================================================================

-- Configuración inicial
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- Crear base de datos
DROP DATABASE IF EXISTS `sistema_napa`;
CREATE DATABASE `sistema_napa` 
    DEFAULT CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE `sistema_napa`;

-- ============================================================================
-- MÓDULO DE SEGURIDAD Y USUARIOS
-- ============================================================================

-- Tabla: usuarios
CREATE TABLE `usuarios` (
  `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `nombre_completo` VARCHAR(150) NOT NULL,
  `dni` VARCHAR(20) NULL,
  `email` VARCHAR(100) NULL,
  `rol` ENUM('administrador', 'trabajador', 'supervisor') NOT NULL DEFAULT 'trabajador',
  `tarifa_por_bolsa` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Para operarios (trabajadores)',
  `fecha_ingreso` DATE NULL,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  INDEX `idx_rol` (`rol` ASC),
  INDEX `idx_estado` (`estado` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Usuarios del sistema con diferentes roles';

-- Tabla: historial_tarifas
CREATE TABLE `historial_tarifas` (
  `id_historial_tarifa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `tarifa_anterior` DECIMAL(10,2) NOT NULL,
  `tarifa_nueva` DECIMAL(10,2) NOT NULL,
  `fecha_cambio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_autorizo` INT UNSIGNED NULL,
  `motivo` VARCHAR(255) NULL,
  PRIMARY KEY (`id_historial_tarifa`),
  INDEX `fk_historial_usuario_idx` (`id_usuario` ASC),
  CONSTRAINT `fk_historial_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cambios de tarifas de operarios';

-- Tabla: auditoria
CREATE TABLE `auditoria` (
  `id_auditoria` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NULL,
  `tabla_afectada` VARCHAR(100) NOT NULL,
  `id_registro` INT UNSIGNED NULL,
  `accion` ENUM('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT') NOT NULL,
  `descripcion` TEXT NULL,
  `datos_anteriores` JSON NULL,
  `datos_nuevos` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `fecha_accion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  INDEX `idx_usuario` (`id_usuario` ASC),
  INDEX `idx_tabla` (`tabla_afectada` ASC),
  INDEX `idx_fecha` (`fecha_accion` ASC),
  CONSTRAINT `fk_auditoria_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de auditoría de todas las operaciones críticas';

-- ============================================================================
-- MÓDULO DE PROVEEDORES
-- ============================================================================

-- Tabla: proveedores
CREATE TABLE `proveedores` (
  `id_proveedor` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(200) NOT NULL,
  `ruc` VARCHAR(20) NULL,
  `direccion` VARCHAR(255) NULL,
  `telefono` VARCHAR(20) NULL,
  `email` VARCHAR(100) NULL,
  `contacto_principal` VARCHAR(150) NULL,
  `tipo_proveedor` ENUM('fibra', 'bolsas', 'otros') NOT NULL,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_proveedor`),
  INDEX `idx_tipo` (`tipo_proveedor` ASC),
  INDEX `idx_estado` (`estado` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Proveedores de materia prima e insumos';

-- ============================================================================
-- MÓDULO DE COMPRAS - MATERIA PRIMA (FIBRA)
-- ============================================================================

-- Tabla: lotes_fibra
CREATE TABLE `lotes_fibra` (
  `id_lote` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo_lote` VARCHAR(50) NOT NULL COMMENT 'LOTE-YYYY-MM-NNNN',
  `fecha_compra` DATE NOT NULL,
  `id_proveedor` INT UNSIGNED NOT NULL,
  `peso_bruto` DECIMAL(10,2) NOT NULL COMMENT 'Kilogramos',
  `peso_neto` DECIMAL(10,2) NOT NULL COMMENT 'Kilogramos',
  `precio_total` DECIMAL(12,2) NOT NULL,
  `precio_por_kg` DECIMAL(10,2) NOT NULL COMMENT 'Calculado automáticamente',
  `cantidad_estimada_bolsas` INT UNSIGNED NOT NULL DEFAULT 70 COMMENT 'Rendimiento estimado',
  `rendimiento_estimado` DECIMAL(10,4) NOT NULL COMMENT 'Bolsas por kg',
  `cantidad_producida_real` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Acumulado de producción',
  `estado` ENUM('disponible', 'en_proceso', 'agotado', 'merma_excesiva') NOT NULL DEFAULT 'disponible',
  `observaciones` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_lote`),
  UNIQUE INDEX `codigo_lote_UNIQUE` (`codigo_lote` ASC),
  INDEX `fk_lote_proveedor_idx` (`id_proveedor` ASC),
  INDEX `idx_fecha_compra` (`fecha_compra` ASC),
  INDEX `idx_estado` (`estado` ASC),
  CONSTRAINT `fk_lote_proveedor`
    FOREIGN KEY (`id_proveedor`)
    REFERENCES `proveedores` (`id_proveedor`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_lote_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lotes de fibra (materia prima principal)';

-- ============================================================================
-- MÓDULO DE INSUMOS SECUNDARIOS (BOLSAS PLÁSTICAS)
-- ============================================================================

-- Tabla: compras_bolsas
CREATE TABLE `compras_bolsas` (
  `id_compra_bolsa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha_compra` DATE NOT NULL,
  `id_proveedor` INT UNSIGNED NOT NULL,
  `peso_kg` DECIMAL(10,2) NOT NULL COMMENT 'Compra en kilogramos',
  `precio_total` DECIMAL(12,2) NOT NULL,
  `precio_por_kg` DECIMAL(10,2) NOT NULL,
  `tipo_bolsa` VARCHAR(100) NULL COMMENT 'Descripción del tipo de bolsa',
  `observaciones` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_compra_bolsa`),
  INDEX `fk_compra_bolsa_proveedor_idx` (`id_proveedor` ASC),
  INDEX `idx_fecha_compra` (`fecha_compra` ASC),
  CONSTRAINT `fk_compra_bolsa_proveedor`
    FOREIGN KEY (`id_proveedor`)
    REFERENCES `proveedores` (`id_proveedor`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_compra_bolsa_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Compras de bolsas plásticas (en kilogramos)';

-- ============================================================================
-- MÓDULO DE PRODUCCIÓN
-- ============================================================================

-- Tabla: producciones
CREATE TABLE `producciones` (
  `id_produccion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha_produccion` DATE NOT NULL,
  `id_lote_fibra` INT UNSIGNED NOT NULL,
  `id_operario` INT UNSIGNED NOT NULL,
  `cantidad_producida` INT UNSIGNED NOT NULL COMMENT 'Bolsas producidas',
  `peso_bolsas_consumido` DECIMAL(10,2) NOT NULL COMMENT 'Kg de bolsas plásticas usadas',
  `eficiencia_porcentual` DECIMAL(5,2) NULL COMMENT 'Calculado: (Real/Estimado)*100',
  `flag_merma_excesiva` BOOLEAN NOT NULL DEFAULT FALSE,
  `estado_validacion` ENUM('pendiente', 'aprobado', 'rechazado') NOT NULL DEFAULT 'pendiente',
  `id_supervisor` INT UNSIGNED NULL COMMENT 'Quien valida',
  `fecha_validacion` DATETIME NULL,
  `observaciones_validacion` TEXT NULL,
  `observaciones` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_produccion`),
  INDEX `fk_prod_lote_idx` (`id_lote_fibra` ASC),
  INDEX `fk_prod_operario_idx` (`id_operario` ASC),
  INDEX `fk_prod_supervisor_idx` (`id_supervisor` ASC),
  INDEX `idx_fecha` (`fecha_produccion` ASC),
  INDEX `idx_estado` (`estado_validacion` ASC),
  INDEX `idx_merma` (`flag_merma_excesiva` ASC),
  CONSTRAINT `fk_prod_lote`
    FOREIGN KEY (`id_lote_fibra`)
    REFERENCES `lotes_fibra` (`id_lote`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_operario`
    FOREIGN KEY (`id_operario`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_supervisor`
    FOREIGN KEY (`id_supervisor`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_prod_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de producción diaria';

-- ============================================================================
-- MÓDULO DE CLIENTES
-- ============================================================================

-- Tabla: clientes
CREATE TABLE `clientes` (
  `id_cliente` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(200) NOT NULL,
  `ruc` VARCHAR(20) NULL,
  `direccion` VARCHAR(255) NULL,
  `telefono` VARCHAR(20) NULL,
  `email` VARCHAR(100) NULL,
  `contacto_principal` VARCHAR(150) NULL,
  `limite_credito` DECIMAL(12,2) NULL DEFAULT 0.00,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`),
  INDEX `idx_estado` (`estado` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Clientes del negocio';

-- ============================================================================
-- MÓDULO DE VENTAS
-- ============================================================================

-- Tabla: ventas
CREATE TABLE `ventas` (
  `id_venta` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha_venta` DATE NOT NULL,
  `id_cliente` INT UNSIGNED NOT NULL,
  `cantidad_vendida` INT UNSIGNED NOT NULL COMMENT 'Bolsas',
  `precio_unitario` DECIMAL(10,2) NOT NULL COMMENT 'Precio manual',
  `precio_total` DECIMAL(12,2) NOT NULL COMMENT 'Calculado',
  `costo_unitario_referencia` DECIMAL(10,2) NULL COMMENT 'Costo calculado del sistema',
  `margen_porcentual` DECIMAL(5,2) NULL COMMENT '(Precio-Costo)/Precio * 100',
  `forma_pago` ENUM('efectivo', 'transferencia', 'cheque', 'credito') NOT NULL DEFAULT 'efectivo',
  `estado_pago` ENUM('pendiente', 'pagado', 'credito', 'cancelado') NOT NULL DEFAULT 'pendiente',
  `estado_entrega` ENUM('pendiente', 'entregado') NOT NULL DEFAULT 'pendiente',
  `fecha_pago` DATE NULL,
  `observaciones` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_venta`),
  INDEX `fk_venta_cliente_idx` (`id_cliente` ASC),
  INDEX `idx_fecha` (`fecha_venta` ASC),
  INDEX `idx_estado_pago` (`estado_pago` ASC),
  INDEX `idx_estado_entrega` (`estado_entrega` ASC),
  CONSTRAINT `fk_venta_cliente`
    FOREIGN KEY (`id_cliente`)
    REFERENCES `clientes` (`id_cliente`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_venta_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de ventas';

-- ============================================================================
-- MÓDULO DE LOGÍSTICA
-- ============================================================================

-- Tabla: choferes
CREATE TABLE `choferes` (
  `id_chofer` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(150) NOT NULL,
  `dni` VARCHAR(20) NULL,
  `licencia` VARCHAR(50) NULL,
  `telefono` VARCHAR(20) NULL,
  `vehiculo` VARCHAR(100) NULL COMMENT 'Placa o descripción',
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_chofer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Choferes para entregas';

-- Tabla: entregas
CREATE TABLE `entregas` (
  `id_entrega` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo_guia` VARCHAR(50) NOT NULL COMMENT 'GUIA-YYYY-NNNN',
  `id_venta` INT UNSIGNED NOT NULL,
  `id_chofer` INT UNSIGNED NOT NULL,
  `fecha_entrega` DATE NOT NULL,
  `hora_salida` TIME NULL,
  `hora_llegada` TIME NULL,
  `direccion_entrega` VARCHAR(255) NOT NULL,
  `nombre_receptor` VARCHAR(150) NULL,
  `dni_receptor` VARCHAR(20) NULL,
  `firma_recibido` BOOLEAN NOT NULL DEFAULT FALSE,
  `observaciones` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_entrega`),
  UNIQUE INDEX `codigo_guia_UNIQUE` (`codigo_guia` ASC),
  INDEX `fk_entrega_venta_idx` (`id_venta` ASC),
  INDEX `fk_entrega_chofer_idx` (`id_chofer` ASC),
  INDEX `idx_fecha` (`fecha_entrega` ASC),
  CONSTRAINT `fk_entrega_venta`
    FOREIGN KEY (`id_venta`)
    REFERENCES `ventas` (`id_venta`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_entrega_chofer`
    FOREIGN KEY (`id_chofer`)
    REFERENCES `choferes` (`id_chofer`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_entrega_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Guías de entrega';

-- ============================================================================
-- MÓDULO DE INVENTARIO
-- ============================================================================

-- Tabla: inventario
CREATE TABLE `inventario` (
  `id_inventario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo_item` ENUM('fibra', 'bolsas_plasticas', 'producto_terminado') NOT NULL,
  `cantidad` DECIMAL(12,2) NOT NULL COMMENT 'Kg para fibra/bolsas, unidades para producto',
  `unidad_medida` VARCHAR(20) NOT NULL COMMENT 'kg, unidades',
  `stock_minimo` DECIMAL(12,2) NULL DEFAULT 0,
  `fecha_ultima_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_inventario`),
  UNIQUE INDEX `tipo_item_UNIQUE` (`tipo_item` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Inventario actual consolidado';

-- Tabla: kardex
CREATE TABLE `kardex` (
  `id_kardex` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha_movimiento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_item` ENUM('fibra', 'bolsas_plasticas', 'producto_terminado') NOT NULL,
  `tipo_movimiento` ENUM('entrada', 'salida', 'ajuste', 'merma') NOT NULL,
  `cantidad` DECIMAL(12,2) NOT NULL,
  `unidad_medida` VARCHAR(20) NOT NULL,
  `saldo_anterior` DECIMAL(12,2) NOT NULL,
  `saldo_nuevo` DECIMAL(12,2) NOT NULL,
  `referencia_tipo` VARCHAR(50) NULL COMMENT 'compra_fibra, compra_bolsa, produccion, venta',
  `referencia_id` INT UNSIGNED NULL COMMENT 'ID del registro origen',
  `observaciones` VARCHAR(255) NULL,
  `usuario_registro` INT UNSIGNED NULL,
  PRIMARY KEY (`id_kardex`),
  INDEX `idx_tipo_item` (`tipo_item` ASC),
  INDEX `idx_fecha` (`fecha_movimiento` ASC),
  INDEX `idx_tipo_mov` (`tipo_movimiento` ASC),
  INDEX `fk_kardex_usuario_idx` (`usuario_registro` ASC),
  CONSTRAINT `fk_kardex_usuario`
    FOREIGN KEY (`usuario_registro`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de todos los movimientos de inventario';

-- ============================================================================
-- MÓDULO DE CONFIGURACIÓN
-- ============================================================================

-- Tabla: configuracion_sistema
CREATE TABLE `configuracion_sistema` (
  `id_config` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parametro` VARCHAR(100) NOT NULL,
  `valor` VARCHAR(255) NOT NULL,
  `tipo_dato` ENUM('entero', 'decimal', 'texto', 'boolean') NOT NULL,
  `descripcion` TEXT NULL,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `usuario_modificacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_config`),
  UNIQUE INDEX `parametro_UNIQUE` (`parametro` ASC),
  INDEX `fk_config_usuario_idx` (`usuario_modificacion` ASC),
  CONSTRAINT `fk_config_usuario`
    FOREIGN KEY (`usuario_modificacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Parámetros configurables del sistema';

-- Tabla: historial_configuracion
CREATE TABLE `historial_configuracion` (
  `id_historial_config` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parametro` VARCHAR(100) NOT NULL,
  `valor_anterior` VARCHAR(255) NOT NULL,
  `valor_nuevo` VARCHAR(255) NOT NULL,
  `fecha_cambio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_cambio` INT UNSIGNED NULL,
  `motivo` VARCHAR(255) NULL,
  PRIMARY KEY (`id_historial_config`),
  INDEX `idx_parametro` (`parametro` ASC),
  INDEX `idx_fecha` (`fecha_cambio` ASC),
  INDEX `fk_hist_config_usuario_idx` (`usuario_cambio` ASC),
  CONSTRAINT `fk_hist_config_usuario`
    FOREIGN KEY (`usuario_cambio`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cambios de configuración';

-- ============================================================================
-- DATOS INICIALES
-- ============================================================================

-- Insertar usuario administrador por defecto
-- Password: admin123 (hash con PASSWORD() o mejor con bcrypt en la aplicación)
INSERT INTO `usuarios` (`username`, `password_hash`, `nombre_completo`, `rol`, `estado`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'administrador', 'activo');

-- Configuración inicial del sistema
INSERT INTO `configuracion_sistema` (`parametro`, `valor`, `tipo_dato`, `descripcion`) VALUES
('cantidad_estimada_default', '70', 'entero', 'Cantidad estimada por defecto de bolsas por fardo'),
('factor_conversion_bolsas', '0.02', 'decimal', 'Factor de conversión: kg por bolsa plástica'),
('tolerancia_merma', '5', 'decimal', 'Porcentaje de tolerancia de merma'),
('stock_minimo_bolsas', '10', 'decimal', 'Stock mínimo de bolsas plásticas en kg'),
('stock_minimo_fibra', '100', 'decimal', 'Stock mínimo de fibra en kg'),
('margen_minimo_venta', '10', 'decimal', 'Porcentaje de margen mínimo sugerido'),
('timeout_sesion', '30', 'entero', 'Minutos de inactividad antes de cerrar sesión'),
('nombre_empresa', 'Taller de Napa Familiar', 'texto', 'Nombre del negocio'),
('ruc_empresa', '', 'texto', 'RUC del negocio');

-- Inventario inicial (todos en 0)
INSERT INTO `inventario` (`tipo_item`, `cantidad`, `unidad_medida`, `stock_minimo`) VALUES
('fibra', 0.00, 'kg', 100.00),
('bolsas_plasticas', 0.00, 'kg', 10.00),
('producto_terminado', 0.00, 'unidades', 50.00);

-- ============================================================================
-- TRIGGERS PARA AUTOMATIZACIÓN
-- ============================================================================

-- Trigger: Actualizar inventario de fibra al crear lote
DELIMITER $$
CREATE TRIGGER `trg_after_insert_lote_fibra`
AFTER INSERT ON `lotes_fibra`
FOR EACH ROW
BEGIN
    -- Actualizar inventario de fibra
    UPDATE `inventario` 
    SET `cantidad` = `cantidad` + NEW.peso_neto
    WHERE `tipo_item` = 'fibra';
    
    -- Registrar en kardex
    INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                          `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`, 
                          `observaciones`, `usuario_registro`)
    SELECT 'fibra', 'entrada', NEW.peso_neto, 'kg',
           `cantidad` - NEW.peso_neto, `cantidad`, 'compra_fibra', NEW.id_lote,
           CONCAT('Compra de lote ', NEW.codigo_lote), NEW.usuario_creacion
    FROM `inventario` WHERE `tipo_item` = 'fibra';
END$$
DELIMITER ;

-- Trigger: Actualizar inventario de bolsas al comprar
DELIMITER $$
CREATE TRIGGER `trg_after_insert_compra_bolsas`
AFTER INSERT ON `compras_bolsas`
FOR EACH ROW
BEGIN
    -- Actualizar inventario
    UPDATE `inventario` 
    SET `cantidad` = `cantidad` + NEW.peso_kg
    WHERE `tipo_item` = 'bolsas_plasticas';
    
    -- Registrar en kardex
    INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                          `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`, 
                          `usuario_registro`)
    SELECT 'bolsas_plasticas', 'entrada', NEW.peso_kg, 'kg',
           `cantidad` - NEW.peso_kg, `cantidad`, 'compra_bolsa', NEW.id_compra_bolsa,
           NEW.usuario_creacion
    FROM `inventario` WHERE `tipo_item` = 'bolsas_plasticas';
END$$
DELIMITER ;

-- Trigger: Procesar producción (descontar inventarios, actualizar lote)
DELIMITER $$
CREATE TRIGGER `trg_after_insert_produccion`
AFTER INSERT ON `producciones`
FOR EACH ROW
BEGIN
    DECLARE v_saldo_bolsas DECIMAL(12,2);
    DECLARE v_saldo_producto DECIMAL(12,2);
    
    -- Solo procesar si está validado como aprobado
    IF NEW.estado_validacion = 'aprobado' THEN
        
        -- Descontar bolsas plásticas
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` - NEW.peso_bolsas_consumido
        WHERE `tipo_item` = 'bolsas_plasticas';
        
        SELECT `cantidad` INTO v_saldo_bolsas 
        FROM `inventario` WHERE `tipo_item` = 'bolsas_plasticas';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`)
        VALUES ('bolsas_plasticas', 'salida', NEW.peso_bolsas_consumido, 'kg',
                v_saldo_bolsas + NEW.peso_bolsas_consumido, v_saldo_bolsas, 
                'produccion', NEW.id_produccion);
        
        -- Incrementar producto terminado
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` + NEW.cantidad_producida
        WHERE `tipo_item` = 'producto_terminado';
        
        SELECT `cantidad` INTO v_saldo_producto 
        FROM `inventario` WHERE `tipo_item` = 'producto_terminado';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`)
        VALUES ('producto_terminado', 'entrada', NEW.cantidad_producida, 'unidades',
                v_saldo_producto - NEW.cantidad_producida, v_saldo_producto, 
                'produccion', NEW.id_produccion);
        
        -- Actualizar producción acumulada del lote
        UPDATE `lotes_fibra` 
        SET `cantidad_producida_real` = `cantidad_producida_real` + NEW.cantidad_producida
        WHERE `id_lote` = NEW.id_lote_fibra;
        
    END IF;
END$$
DELIMITER ;

-- Trigger: Actualizar estado de lote al cambiar producción acumulada
DELIMITER $$
CREATE TRIGGER `trg_after_update_lote_produccion`
AFTER UPDATE ON `lotes_fibra`
FOR EACH ROW
BEGIN
    IF NEW.cantidad_producida_real != OLD.cantidad_producida_real THEN
        IF NEW.cantidad_producida_real = 0 THEN
            UPDATE `lotes_fibra` SET `estado` = 'disponible' WHERE `id_lote` = NEW.id_lote;
        ELSEIF NEW.cantidad_producida_real < NEW.cantidad_estimada_bolsas THEN
            UPDATE `lotes_fibra` SET `estado` = 'en_proceso' WHERE `id_lote` = NEW.id_lote;
        ELSE
            UPDATE `lotes_fibra` SET `estado` = 'agotado' WHERE `id_lote` = NEW.id_lote;
        END IF;
    END IF;
END$$
DELIMITER ;

-- Trigger: Descontar inventario al vender
DELIMITER $$
CREATE TRIGGER `trg_after_insert_venta`
AFTER INSERT ON `ventas`
FOR EACH ROW
BEGIN
    DECLARE v_saldo DECIMAL(12,2);
    
    -- Solo si la venta no está cancelada
    IF NEW.estado_pago != 'cancelado' THEN
        UPDATE `inventario` 
        SET `cantidad` = `cantidad` - NEW.cantidad_vendida
        WHERE `tipo_item` = 'producto_terminado';
        
        SELECT `cantidad` INTO v_saldo 
        FROM `inventario` WHERE `tipo_item` = 'producto_terminado';
        
        INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                              `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`)
        VALUES ('producto_terminado', 'salida', NEW.cantidad_vendida, 'unidades',
                v_saldo + NEW.cantidad_vendida, v_saldo, 'venta', NEW.id_venta);
    END IF;
END$$
DELIMITER ;

-- Trigger: Actualizar estado de venta al crear entrega
DELIMITER $$
CREATE TRIGGER `trg_after_insert_entrega`
AFTER INSERT ON `entregas`
FOR EACH ROW
BEGIN
    UPDATE `ventas` 
    SET `estado_entrega` = 'entregado'
    WHERE `id_venta` = NEW.id_venta;
END$$
DELIMITER ;

-- ============================================================================
-- VISTAS ÚTILES
-- ============================================================================

-- Vista: Resumen de producción por lote
CREATE OR REPLACE VIEW `v_resumen_lotes` AS
SELECT 
    l.id_lote,
    l.codigo_lote,
    l.fecha_compra,
    p.nombre AS proveedor,
    l.peso_neto,
    l.precio_total,
    l.cantidad_estimada_bolsas,
    l.cantidad_producida_real,
    l.estado,
    ROUND((l.cantidad_producida_real / l.cantidad_estimada_bolsas) * 100, 2) AS eficiencia_porcentual,
    CASE 
        WHEN l.cantidad_producida_real < (l.cantidad_estimada_bolsas * 0.95) THEN 'SI'
        ELSE 'NO'
    END AS tiene_merma_excesiva
FROM lotes_fibra l
INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
WHERE l.cantidad_producida_real > 0;

-- Vista: Producción validada por operario
CREATE OR REPLACE VIEW `v_produccion_validada` AS
SELECT 
    u.id_usuario,
    u.nombre_completo AS operario,
    u.tarifa_por_bolsa,
    DATE(pr.fecha_produccion) AS fecha,
    SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_aprobadas,
    SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida * u.tarifa_por_bolsa ELSE 0 END) AS monto_pagar
FROM usuarios u
INNER JOIN producciones pr ON u.id_usuario = pr.id_operario
GROUP BY u.id_usuario, u.nombre_completo, u.tarifa_por_bolsa, DATE(pr.fecha_produccion);

-- Vista: Estado de inventario con alertas
CREATE OR REPLACE VIEW `v_estado_inventario` AS
SELECT 
    tipo_item,
    cantidad,
    unidad_medida,
    stock_minimo,
    CASE 
        WHEN cantidad < stock_minimo * 0.5 THEN 'CRÍTICO'
        WHEN cantidad < stock_minimo THEN 'BAJO'
        ELSE 'NORMAL'
    END AS estado_alerta,
    fecha_ultima_actualizacion
FROM inventario;

-- ============================================================================
-- STORED PROCEDURES ÚTILES
-- ============================================================================

-- Procedimiento: Calcular costo unitario por producción
DELIMITER $$
CREATE PROCEDURE `sp_calcular_costo_unitario`(
    IN p_id_produccion INT
)
BEGIN
    DECLARE v_costo_fibra DECIMAL(12,4);
    DECLARE v_costo_bolsas DECIMAL(12,4);
    DECLARE v_costo_mano_obra DECIMAL(12,4);
    DECLARE v_cantidad DECIMAL(12,2);
    DECLARE v_costo_total DECIMAL(12,4);
    
    SELECT 
        (l.precio_total / l.peso_neto) * (l.peso_neto / NULLIF(l.cantidad_producida_real, 0)),
        pr.peso_bolsas_consumido * (SELECT AVG(precio_por_kg) FROM compras_bolsas ORDER BY id_compra_bolsa DESC LIMIT 5),
        u.tarifa_por_bolsa,
        pr.cantidad_producida
    INTO v_costo_fibra, v_costo_bolsas, v_costo_mano_obra, v_cantidad
    FROM producciones pr
    INNER JOIN lotes_fibra l ON pr.id_lote_fibra = l.id_lote
    INNER JOIN usuarios u ON pr.id_operario = u.id_usuario
    WHERE pr.id_produccion = p_id_produccion;
    
    SET v_costo_total = (v_costo_fibra + v_costo_bolsas + v_costo_mano_obra) / NULLIF(v_cantidad, 0);
    
    SELECT v_costo_total AS costo_unitario;
END$$
DELIMITER ;

-- Procedimiento: Generar código de lote
DELIMITER $$
CREATE PROCEDURE `sp_generar_codigo_lote`(
    OUT p_codigo_lote VARCHAR(50)
)
BEGIN
    DECLARE v_anio CHAR(4);
    DECLARE v_mes CHAR(2);
    DECLARE v_secuencia INT;
    
    SET v_anio = YEAR(CURDATE());
    SET v_mes = LPAD(MONTH(CURDATE()), 2, '0');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_lote, 12, 4) AS UNSIGNED)), 0) + 1 
    INTO v_secuencia
    FROM lotes_fibra
    WHERE codigo_lote LIKE CONCAT('LOTE-', v_anio, '-', v_mes, '-%');
    
    SET p_codigo_lote = CONCAT('LOTE-', v_anio, '-', v_mes, '-', LPAD(v_secuencia, 4, '0'));
END$$
DELIMITER ;

-- Procedimiento: Generar código de guía
DELIMITER $$
CREATE PROCEDURE `sp_generar_codigo_guia`(
    OUT p_codigo_guia VARCHAR(50)
)
BEGIN
    DECLARE v_anio CHAR(4);
    DECLARE v_secuencia INT;
    
    SET v_anio = YEAR(CURDATE());
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_guia, 11, 4) AS UNSIGNED)), 0) + 1 
    INTO v_secuencia
    FROM entregas
    WHERE codigo_guia LIKE CONCAT('GUIA-', v_anio, '-%');
    
    SET p_codigo_guia = CONCAT('GUIA-', v_anio, '-', LPAD(v_secuencia, 4, '0'));
END$$
DELIMITER ;

-- ============================================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================================================

-- Índices para búsquedas frecuentes
CREATE INDEX idx_lotes_fecha_estado ON lotes_fibra(fecha_compra, estado);
CREATE INDEX idx_prod_fecha_operario ON producciones(fecha_produccion, id_operario);
CREATE INDEX idx_ventas_fecha_cliente ON ventas(fecha_venta, id_cliente);
CREATE INDEX idx_kardex_fecha_item ON kardex(fecha_movimiento, tipo_item);

-- ============================================================================
-- RESTAURAR CONFIGURACIÓN
-- ============================================================================

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
