-- ============================================================================
-- MIGRACIÓN: Corrección de Comisiones y Gastos
-- Fecha: 19 de Enero, 2026
-- Descripción: Vincula el pago de comisiones con los gastos operativos para
--              garantizar el cálculo correcto de utilidades.
-- ============================================================================

USE sistema_napa;

-- 1. Asegurar que existe la tabla gastos_operativos (Inferida del Controlador)
CREATE TABLE IF NOT EXISTS `gastos_operativos` (
  `id_gasto` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha_gasto` DATE NOT NULL,
  `categoria` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `monto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` INT UNSIGNED NULL,
  PRIMARY KEY (`id_gasto`),
  INDEX `idx_fecha` (`fecha_gasto`),
  INDEX `idx_categoria` (`categoria`),
  CONSTRAINT `fk_gasto_usuario`
    FOREIGN KEY (`usuario_creacion`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de gastos operativos (incluyendo comisiones)';

-- 2. Actualizar el Procedimiento Almacenado de Pago de Comisiones
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
    DECLARE v_monto DECIMAL(12,2);
    DECLARE v_operario VARCHAR(150);
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_mensaje = 'Error al registrar el pago';
    END;
    
    START TRANSACTION;
    
    -- Verificar estado y obtener datos para el gasto
    SELECT c.estado, c.monto_total, u.nombre_completo, c.fecha_inicio, c.fecha_fin 
    INTO v_estado_actual, v_monto, v_operario, v_fecha_inicio, v_fecha_fin
    FROM comisiones c
    INNER JOIN usuarios u ON c.id_operario = u.id_usuario
    WHERE c.id_comision = p_id_comision;
    
    IF v_estado_actual = 'pagado' THEN
        SET p_mensaje = 'Esta comisión ya fue pagada anteriormente';
        ROLLBACK;
    ELSEIF v_estado_actual = 'anulado' THEN
        SET p_mensaje = 'Esta comisión está anulada';
        ROLLBACK;
    ELSE
        -- 1. Actualizar estado de la comisión
        UPDATE comisiones
        SET estado = 'pagado',
            fecha_pago = p_fecha_pago,
            metodo_pago = p_metodo_pago,
            numero_operacion = p_numero_operacion,
            usuario_pago = p_id_usuario_admin
        WHERE id_comision = p_id_comision;
        
        -- 2. INSERTAR AUTOMÁTICAMENTE EN GASTOS OPERATIVOS
        -- Esto es crítico para que las utilidades se calculen bien (Ingresos - Gastos)
        INSERT INTO gastos_operativos (
            fecha_gasto, 
            categoria, 
            descripcion, 
            monto, 
            usuario_creacion
        ) VALUES (
            p_fecha_pago,
            'Pago de Comisiones', -- Categoría específica
            CONCAT('Pago de comisión #', p_id_comision, ' - ', v_operario, ' (Periodo: ', DATE_FORMAT(v_fecha_inicio, '%d/%m'), ' al ', DATE_FORMAT(v_fecha_fin, '%d/%m'), ')'),
            v_monto,
            p_id_usuario_admin
        );
        
        SET p_mensaje = 'Pago registrado y contabilizado en gastos correctamente';
        
        COMMIT;
    END IF;
END$$

DELIMITER ;

SELECT '✅ Corrección aplicada: Los pagos de comisiones ahora descuentan utilidades automáticamente.' as resultado;
