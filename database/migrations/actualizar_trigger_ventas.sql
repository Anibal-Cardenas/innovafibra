-- ============================================================================
-- ACTUALIZAR TRIGGER DE VENTAS para inventario por calidad
-- ============================================================================

USE sistema_napa;

DROP TRIGGER IF EXISTS trg_after_insert_venta;

DELIMITER $$

CREATE TRIGGER trg_after_insert_venta
AFTER INSERT ON ventas
FOR EACH ROW
BEGIN
    DECLARE v_saldo DECIMAL(12,2);
    DECLARE v_id_calidad INT UNSIGNED;
    
    -- Solo si la venta no está cancelada
    IF NEW.estado_pago != 'cancelado' THEN
        
        -- Usar id_calidad_napa directamente
        SET v_id_calidad = NEW.id_calidad_napa;
        
        -- Obtener saldo anterior
        SELECT cantidad INTO v_saldo
        FROM inventario 
        WHERE tipo_item = 'producto_terminado' 
          AND id_calidad_napa = v_id_calidad
        FOR UPDATE;
        
        -- Actualizar inventario por calidad específica
        UPDATE inventario
        SET cantidad = cantidad - NEW.cantidad_vendida
        WHERE tipo_item = 'producto_terminado'
          AND id_calidad_napa = v_id_calidad;
        
        -- Registrar en kardex con calidad
        INSERT INTO kardex (tipo_item, id_calidad_napa, tipo_movimiento, cantidad, unidad_medida,
                           saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, descripcion)
        VALUES ('producto_terminado', v_id_calidad, 'salida', NEW.cantidad_vendida, 'unidades',
                v_saldo, v_saldo - NEW.cantidad_vendida, 'venta', NEW.id_venta,
                CONCAT('Venta #', NEW.id_venta));
    END IF;
END$$

DELIMITER ;

-- Verificar
SELECT 'Trigger actualizado correctamente' AS resultado;
