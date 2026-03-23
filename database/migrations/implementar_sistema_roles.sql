-- ============================================================================
-- MIGRACIÓN: Implementar Sistema de Roles y Comisiones
-- Sistema de Gestión de Producción - Taller de Napa
-- Fecha: 14 de Enero, 2026
-- Descripción: Actualizar sistema de roles para incluir administrador, 
--              vendedor y operador con sistema de comisiones
-- ============================================================================

USE `sistema_napa`;

-- ============================================================================
-- 1. ACTUALIZAR TABLA DE USUARIOS PARA NUEVOS ROLES
-- ============================================================================

-- Cambiar el ENUM de roles para incluir los nuevos roles
ALTER TABLE `usuarios` 
MODIFY COLUMN `rol` ENUM('administrador', 'operador', 'vendedor', 'trabajador', 'supervisor') 
NOT NULL DEFAULT 'operador'
COMMENT 'Roles: administrador (acceso total), operador (producción y comisiones), vendedor (solo ventas), trabajador/supervisor (legacy)';

-- Actualizar usuarios existentes de tipo 'trabajador' a 'operador'
UPDATE `usuarios` 
SET `rol` = 'operador' 
WHERE `rol` = 'trabajador';

-- ============================================================================
-- 2. CREAR TABLA DE COMISIONES
-- ============================================================================

-- Tabla: comisiones
-- Registra las comisiones calculadas para operadores por periodo
CREATE TABLE IF NOT EXISTS `comisiones` (
  `id_comision` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_operario` INT UNSIGNED NOT NULL,
  `fecha_inicio` DATE NOT NULL COMMENT 'Inicio del periodo',
  `fecha_fin` DATE NOT NULL COMMENT 'Fin del periodo',
  `total_bolsas_producidas` INT UNSIGNED NOT NULL DEFAULT 0,
  `tarifa_aplicada` DECIMAL(10,2) NOT NULL COMMENT 'Tarifa por bolsa en el periodo',
  `monto_comision` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `monto_bonificacion` DECIMAL(12,2) NULL DEFAULT 0.00 COMMENT 'Bonos adicionales',
  `monto_descuento` DECIMAL(12,2) NULL DEFAULT 0.00 COMMENT 'Descuentos por rechazos',
  `monto_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total a pagar',
  `estado` ENUM('pendiente', 'calculado', 'pagado', 'anulado') NOT NULL DEFAULT 'pendiente',
  `fecha_pago` DATE NULL,
  `metodo_pago` ENUM('efectivo', 'transferencia', 'cheque') NULL,
  `numero_operacion` VARCHAR(100) NULL COMMENT 'Número de transacción bancaria',
  `observaciones` TEXT NULL,
  `fecha_calculo` TIMESTAMP NULL COMMENT 'Cuándo se calculó',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creo` INT UNSIGNED NULL COMMENT 'Admin que calculó la comisión',
  `usuario_pago` INT UNSIGNED NULL COMMENT 'Admin que registró el pago',
  PRIMARY KEY (`id_comision`),
  INDEX `fk_comision_operario_idx` (`id_operario` ASC),
  INDEX `idx_periodo` (`fecha_inicio` ASC, `fecha_fin` ASC),
  INDEX `idx_estado` (`estado` ASC),
  INDEX `idx_fecha_pago` (`fecha_pago` ASC),
  UNIQUE INDEX `unique_operario_periodo` (`id_operario`, `fecha_inicio`, `fecha_fin`),
  CONSTRAINT `fk_comision_operario`
    FOREIGN KEY (`id_operario`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comision_usuario_creo`
    FOREIGN KEY (`usuario_creo`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comision_usuario_pago`
    FOREIGN KEY (`usuario_pago`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Comisiones calculadas para operadores por periodo';

-- ============================================================================
-- 3. CREAR TABLA DE DETALLE DE COMISIONES
-- ============================================================================

-- Tabla: comisiones_detalle
-- Detalla qué producciones forman parte de cada comisión
CREATE TABLE IF NOT EXISTS `comisiones_detalle` (
  `id_comision_detalle` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_comision` INT UNSIGNED NOT NULL,
  `id_produccion` INT UNSIGNED NOT NULL,
  `fecha_produccion` DATE NOT NULL,
  `cantidad_bolsas` INT UNSIGNED NOT NULL,
  `tarifa_por_bolsa` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `incluido_en_calculo` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id_comision_detalle`),
  INDEX `fk_detalle_comision_idx` (`id_comision` ASC),
  INDEX `fk_detalle_produccion_idx` (`id_produccion` ASC),
  UNIQUE INDEX `unique_comision_produccion` (`id_comision`, `id_produccion`),
  CONSTRAINT `fk_detalle_comision`
    FOREIGN KEY (`id_comision`)
    REFERENCES `comisiones` (`id_comision`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_produccion`
    FOREIGN KEY (`id_produccion`)
    REFERENCES `producciones` (`id_produccion`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detalle de producciones incluidas en cada comisión';

-- ============================================================================
-- 4. AÑADIR CAMPO PARA TRACKING DE COMISIONES EN PRODUCCIONES
-- ============================================================================

-- Añadir campo para saber si una producción ya fue incluida en una comisión
ALTER TABLE `producciones` 
ADD COLUMN `id_comision` INT UNSIGNED NULL COMMENT 'Comisión a la que pertenece esta producción',
ADD INDEX `fk_prod_comision_idx` (`id_comision` ASC);

ALTER TABLE `producciones`
ADD CONSTRAINT `fk_prod_comision`
  FOREIGN KEY (`id_comision`)
  REFERENCES `comisiones` (`id_comision`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- ============================================================================
-- 5. CREAR VISTAS PARA REPORTES DE COMISIONES
-- ============================================================================

-- Vista: Resumen de producción por operador (para cálculo de comisiones)
CREATE OR REPLACE VIEW `v_produccion_operador` AS
SELECT 
    u.id_usuario AS id_operario,
    u.username,
    u.nombre_completo AS operario,
    u.tarifa_por_bolsa,
    DATE(pr.fecha_produccion) AS fecha,
    COUNT(pr.id_produccion) AS total_producciones,
    SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_aprobadas,
    SUM(CASE WHEN pr.estado_validacion = 'rechazado' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_rechazadas,
    SUM(CASE WHEN pr.estado_validacion = 'pendiente' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_pendientes,
    SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida * u.tarifa_por_bolsa ELSE 0 END) AS monto_comision_dia,
    pr.id_comision
FROM usuarios u
INNER JOIN producciones pr ON u.id_usuario = pr.id_operario
WHERE u.rol IN ('operador', 'trabajador')
GROUP BY u.id_usuario, u.username, u.nombre_completo, u.tarifa_por_bolsa, DATE(pr.fecha_produccion), pr.id_comision;

-- Vista: Comisiones pendientes de pago
CREATE OR REPLACE VIEW `v_comisiones_pendientes` AS
SELECT 
    c.id_comision,
    u.username,
    u.nombre_completo AS operario,
    c.fecha_inicio,
    c.fecha_fin,
    c.total_bolsas_producidas,
    c.tarifa_aplicada,
    c.monto_comision,
    c.monto_bonificacion,
    c.monto_descuento,
    c.monto_total,
    c.estado,
    c.fecha_calculo,
    DATEDIFF(CURDATE(), c.fecha_fin) AS dias_pendiente
FROM comisiones c
INNER JOIN usuarios u ON c.id_operario = u.id_usuario
WHERE c.estado IN ('pendiente', 'calculado')
ORDER BY c.fecha_fin DESC;

-- Vista: Historial de comisiones pagadas
CREATE OR REPLACE VIEW `v_historial_comisiones` AS
SELECT 
    c.id_comision,
    u.username,
    u.nombre_completo AS operario,
    c.fecha_inicio,
    c.fecha_fin,
    c.total_bolsas_producidas,
    c.tarifa_aplicada,
    c.monto_total,
    c.estado,
    c.fecha_pago,
    c.metodo_pago,
    c.numero_operacion,
    admin.nombre_completo AS pagado_por
FROM comisiones c
INNER JOIN usuarios u ON c.id_operario = u.id_usuario
LEFT JOIN usuarios admin ON c.usuario_pago = admin.id_usuario
WHERE c.estado = 'pagado'
ORDER BY c.fecha_pago DESC;

-- ============================================================================
-- 6. STORED PROCEDURES PARA GESTIÓN DE COMISIONES
-- ============================================================================

-- Procedimiento: Calcular comisión de un operario por periodo
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_calcular_comision_operario`$$
CREATE PROCEDURE `sp_calcular_comision_operario`(
    IN p_id_operario INT UNSIGNED,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_usuario_admin INT UNSIGNED,
    OUT p_id_comision INT UNSIGNED,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_total_bolsas INT UNSIGNED DEFAULT 0;
    DECLARE v_tarifa DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_monto_comision DECIMAL(12,2) DEFAULT 0.00;
    DECLARE v_existe_comision INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_mensaje = 'Error al calcular la comisión';
        SET p_id_comision = NULL;
    END;
    
    START TRANSACTION;
    
    -- Verificar si ya existe una comisión para este periodo
    SELECT COUNT(*) INTO v_existe_comision
    FROM comisiones
    WHERE id_operario = p_id_operario
      AND fecha_inicio = p_fecha_inicio
      AND fecha_fin = p_fecha_fin
      AND estado != 'anulado';
    
    IF v_existe_comision > 0 THEN
        SET p_mensaje = 'Ya existe una comisión calculada para este periodo';
        SET p_id_comision = NULL;
        ROLLBACK;
    ELSE
        -- Obtener tarifa actual del operario
        SELECT tarifa_por_bolsa INTO v_tarifa
        FROM usuarios
        WHERE id_usuario = p_id_operario;
        
        -- Calcular total de bolsas aprobadas en el periodo
        SELECT COALESCE(SUM(cantidad_producida), 0) INTO v_total_bolsas
        FROM producciones
        WHERE id_operario = p_id_operario
          AND DATE(fecha_produccion) BETWEEN p_fecha_inicio AND p_fecha_fin
          AND estado_validacion = 'aprobado'
          AND id_comision IS NULL; -- Solo las que no están en otra comisión
        
        -- Calcular monto de comisión
        SET v_monto_comision = v_total_bolsas * v_tarifa;
        
        -- Insertar la comisión
        INSERT INTO comisiones (
            id_operario,
            fecha_inicio,
            fecha_fin,
            total_bolsas_producidas,
            tarifa_aplicada,
            monto_comision,
            monto_total,
            estado,
            fecha_calculo,
            usuario_creo
        ) VALUES (
            p_id_operario,
            p_fecha_inicio,
            p_fecha_fin,
            v_total_bolsas,
            v_tarifa,
            v_monto_comision,
            v_monto_comision, -- Por ahora igual, luego se pueden añadir bonos/descuentos
            'calculado',
            NOW(),
            p_id_usuario_admin
        );
        
        SET p_id_comision = LAST_INSERT_ID();
        
        -- Insertar el detalle de la comisión
        INSERT INTO comisiones_detalle (
            id_comision,
            id_produccion,
            fecha_produccion,
            cantidad_bolsas,
            tarifa_por_bolsa,
            subtotal
        )
        SELECT 
            p_id_comision,
            id_produccion,
            DATE(fecha_produccion),
            cantidad_producida,
            v_tarifa,
            cantidad_producida * v_tarifa
        FROM producciones
        WHERE id_operario = p_id_operario
          AND DATE(fecha_produccion) BETWEEN p_fecha_inicio AND p_fecha_fin
          AND estado_validacion = 'aprobado'
          AND id_comision IS NULL;
        
        -- Actualizar las producciones para marcarlas como incluidas en esta comisión
        UPDATE producciones
        SET id_comision = p_id_comision
        WHERE id_operario = p_id_operario
          AND DATE(fecha_produccion) BETWEEN p_fecha_inicio AND p_fecha_fin
          AND estado_validacion = 'aprobado'
          AND id_comision IS NULL;
        
        SET p_mensaje = CONCAT('Comisión calculada exitosamente. Total: S/ ', v_monto_comision, ' por ', v_total_bolsas, ' bolsas');
        
        COMMIT;
    END IF;
END$$
DELIMITER ;

-- Procedimiento: Registrar pago de comisión
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_registrar_pago_comision`$$
CREATE PROCEDURE `sp_registrar_pago_comision`(
    IN p_id_comision INT UNSIGNED,
    IN p_fecha_pago DATE,
    IN p_metodo_pago VARCHAR(50),
    IN p_numero_operacion VARCHAR(100),
    IN p_id_usuario_admin INT UNSIGNED,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_estado_actual VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_mensaje = 'Error al registrar el pago';
    END;
    
    START TRANSACTION;
    
    -- Verificar estado actual
    SELECT estado INTO v_estado_actual
    FROM comisiones
    WHERE id_comision = p_id_comision;
    
    IF v_estado_actual = 'pagado' THEN
        SET p_mensaje = 'Esta comisión ya fue pagada anteriormente';
        ROLLBACK;
    ELSEIF v_estado_actual = 'anulado' THEN
        SET p_mensaje = 'Esta comisión está anulada';
        ROLLBACK;
    ELSE
        -- Registrar el pago
        UPDATE comisiones
        SET estado = 'pagado',
            fecha_pago = p_fecha_pago,
            metodo_pago = p_metodo_pago,
            numero_operacion = p_numero_operacion,
            usuario_pago = p_id_usuario_admin
        WHERE id_comision = p_id_comision;
        
        SET p_mensaje = 'Pago registrado exitosamente';
        
        COMMIT;
    END IF;
END$$
DELIMITER ;

-- Procedimiento: Obtener resumen de comisiones por operario
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_resumen_comisiones_operario`$$
CREATE PROCEDURE `sp_resumen_comisiones_operario`(
    IN p_id_operario INT UNSIGNED,
    IN p_anio INT,
    IN p_mes INT
)
BEGIN
    SELECT 
        DATE(p.fecha_produccion) AS fecha,
        COUNT(p.id_produccion) AS total_producciones,
        SUM(CASE WHEN p.estado_validacion = 'aprobado' THEN p.cantidad_producida ELSE 0 END) AS bolsas_aprobadas,
        SUM(CASE WHEN p.estado_validacion = 'rechazado' THEN p.cantidad_producida ELSE 0 END) AS bolsas_rechazadas,
        SUM(CASE WHEN p.estado_validacion = 'pendiente' THEN p.cantidad_producida ELSE 0 END) AS bolsas_pendientes,
        u.tarifa_por_bolsa,
        SUM(CASE WHEN p.estado_validacion = 'aprobado' THEN p.cantidad_producida * u.tarifa_por_bolsa ELSE 0 END) AS comision_estimada,
        CASE WHEN p.id_comision IS NOT NULL THEN 'SI' ELSE 'NO' END AS incluida_en_comision,
        p.id_comision
    FROM producciones p
    INNER JOIN usuarios u ON p.id_operario = u.id_usuario
    WHERE p.id_operario = p_id_operario
      AND YEAR(p.fecha_produccion) = p_anio
      AND MONTH(p.fecha_produccion) = p_mes
    GROUP BY DATE(p.fecha_produccion), u.tarifa_por_bolsa, p.id_comision
    ORDER BY DATE(p.fecha_produccion) DESC;
END$$
DELIMITER ;

-- ============================================================================
-- 7. ACTUALIZAR TRIGGER DE PRODUCCIÓN VALIDADA
-- ============================================================================

-- Actualizar vista de producción validada con el nuevo esquema
DROP VIEW IF EXISTS `v_produccion_validada`;
CREATE OR REPLACE VIEW `v_produccion_validada` AS
SELECT 
    u.id_usuario,
    u.nombre_completo AS operario,
    u.tarifa_por_bolsa,
    DATE(pr.fecha_produccion) AS fecha,
    SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida ELSE 0 END) AS bolsas_aprobadas,
    SUM(CASE WHEN pr.estado_validacion = 'aprobado' THEN pr.cantidad_producida * u.tarifa_por_bolsa ELSE 0 END) AS monto_estimado,
    CASE WHEN pr.id_comision IS NOT NULL THEN 'Incluida' ELSE 'Pendiente' END AS estado_comision
FROM usuarios u
INNER JOIN producciones pr ON u.id_usuario = pr.id_operario
WHERE u.rol IN ('operador', 'trabajador')
GROUP BY u.id_usuario, u.nombre_completo, u.tarifa_por_bolsa, DATE(pr.fecha_produccion), pr.id_comision;

-- ============================================================================
-- 8. INSERTAR DATOS INICIALES DE EJEMPLO
-- ============================================================================

-- Crear usuario operador de ejemplo (si no existe)
INSERT IGNORE INTO `usuarios` (`username`, `password_hash`, `nombre_completo`, `rol`, `tarifa_por_bolsa`, `estado`) 
VALUES 
    ('operador1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez - Operador', 'operador', 0.50, 'activo'),
    ('vendedor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González - Vendedora', 'vendedor', 0.00, 'activo');

-- ============================================================================
-- FIN DE LA MIGRACIÓN
-- ============================================================================

SELECT '✓ Migración completada exitosamente' AS mensaje,
       'Se han creado las tablas de comisiones y actualizado los roles' AS detalle;

