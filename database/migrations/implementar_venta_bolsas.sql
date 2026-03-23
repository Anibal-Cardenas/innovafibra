-- Implementación de venta de bolsas (insumos)
-- Fecha: 31 de Enero, 2026

-- 1. Modificar columna cantidad para permitir decimales (Kg)
ALTER TABLE `ventas` MODIFY `cantidad_vendida` DECIMAL(12,2) NOT NULL COMMENT 'Bolsas o Kg';

-- 2. Agregar tipo de venta para distinguir producto terminado de insumos
-- Se agrega al final o después de id_cliente
ALTER TABLE `ventas` ADD `tipo_venta` ENUM('producto', 'insumo') NOT NULL DEFAULT 'producto' AFTER `id_cliente`;

-- 3. Actualizar Trigger de Venta
DROP TRIGGER IF EXISTS `trg_after_insert_venta`;

DELIMITER $$
CREATE TRIGGER `trg_after_insert_venta`
AFTER INSERT ON `ventas`
FOR EACH ROW
BEGIN
    DECLARE v_saldo DECIMAL(12,2);
    
    -- Solo si la venta no está cancelada
    IF NEW.estado_pago != 'cancelado' THEN
        
        IF NEW.tipo_venta = 'producto' THEN
            -- Lógica para producto terminado (Napa)
            -- Se descuenta de la calidad específica si existe, o general si es NULL
            UPDATE `inventario` 
            SET `cantidad` = `cantidad` - NEW.cantidad_vendida
            WHERE `tipo_item` = 'producto_terminado' 
            AND (`id_calidad_napa` = NEW.id_calidad_napa OR (NEW.id_calidad_napa IS NULL AND `id_calidad_napa` IS NULL))
            LIMIT 1;
            
            -- Obtener saldo actualizado
            SELECT SUM(`cantidad`) INTO v_saldo 
            FROM `inventario` 
            WHERE `tipo_item` = 'producto_terminado'
            AND (`id_calidad_napa` = NEW.id_calidad_napa OR (NEW.id_calidad_napa IS NULL AND `id_calidad_napa` IS NULL));
            
            -- Kardex
            INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `id_calidad_napa`, `cantidad`, `unidad_medida`, 
                                  `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`, `observaciones`)
            VALUES ('producto_terminado', 'salida', NEW.id_calidad_napa, NEW.cantidad_vendida, 'unidades',
                    COALESCE(v_saldo, 0) + NEW.cantidad_vendida, COALESCE(v_saldo, 0), 'venta', NEW.id_venta, 'Venta de producto terminado');
                    
        ELSEIF NEW.tipo_venta = 'insumo' THEN
            -- Lógica para bolsas (Kg)
            UPDATE `inventario` 
            SET `cantidad` = `cantidad` - NEW.cantidad_vendida
            WHERE `tipo_item` = 'bolsas_plasticas';
            
            SELECT `cantidad` INTO v_saldo 
            FROM `inventario` WHERE `tipo_item` = 'bolsas_plasticas';
            
            INSERT INTO `kardex` (`tipo_item`, `tipo_movimiento`, `cantidad`, `unidad_medida`, 
                                  `saldo_anterior`, `saldo_nuevo`, `referencia_tipo`, `referencia_id`, `observaciones`)
            VALUES ('bolsas_plasticas', 'salida', NEW.cantidad_vendida, 'kg',
                    COALESCE(v_saldo, 0) + NEW.cantidad_vendida, COALESCE(v_saldo, 0), 'venta', NEW.id_venta, 'Venta de insumos (bolsas)');
        END IF;

    END IF;
END$$
DELIMITER ;
