-- ============================================================================
-- FIX: Recursion in trigger trg_produccion_aprobada_inventario
-- Date: 2026-01-14
-- Description: Removes the UPDATE producciones inside the AFTER UPDATE trigger
-- which causes Error 1442.
-- ============================================================================

USE sistema_napa;

DROP TRIGGER IF EXISTS trg_produccion_aprobada_inventario;

DELIMITER $$

CREATE TRIGGER trg_produccion_aprobada_inventario
AFTER UPDATE ON producciones
FOR EACH ROW
BEGIN
    DECLARE v_calidad_napa INT UNSIGNED;
    DECLARE v_peso_bolsas_consumido DECIMAL(12,2);
    DECLARE v_saldo_bolsas DECIMAL(12,2);
    DECLARE v_saldo_producto DECIMAL(12,2);
    DECLARE v_cantidad_producida INT;
    
    -- Solo actuar si cambió a 'aprobado'
    IF OLD.estado_validacion != 'aprobado' AND NEW.estado_validacion = 'aprobado' THEN
        
        -- Usar la calidad ya registrada en la producción
        SET v_calidad_napa = NEW.id_calidad_napa;
        
        -- Si por alguna razón es nula, intentar recuperarla (pero no actualizar la tabla producciones aquí)
        IF v_calidad_napa IS NULL THEN
             SELECT cf.id_calidad_napa_destino
             INTO v_calidad_napa
             FROM cubos_fibra c
             INNER JOIN lotes_fibra l ON c.id_lote = l.id_lote
             LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
             WHERE c.id_cubo = NEW.id_cubo
             LIMIT 1;
             
             IF v_calidad_napa IS NULL THEN
                SELECT MIN(id_calidad_napa) INTO v_calidad_napa FROM calidades_napa WHERE estado = 'activo';
             END IF;
        END IF;
        
        SET v_cantidad_producida = NEW.cantidad_producida;
        SET v_peso_bolsas_consumido = NEW.peso_bolsas_consumido;
        
        -- 1. ACTUALIZAR inventario de bolsas plásticas (SALIDA)
        SELECT cantidad INTO v_saldo_bolsas
        FROM inventario 
        WHERE tipo_item = 'bolsas_plasticas'
        LIMIT 1; 
        -- Removed FOR UPDATE to avoid potential deadlocks in simple triggers, though allowed.
        
        UPDATE inventario 
        SET cantidad = cantidad - v_peso_bolsas_consumido
        WHERE tipo_item = 'bolsas_plasticas';
        
        INSERT INTO kardex (tipo_item, tipo_movimiento, cantidad, unidad_medida, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, observaciones)
        VALUES ('bolsas_plasticas', 'salida', v_peso_bolsas_consumido, 'kg', v_saldo_bolsas, v_saldo_bolsas - v_peso_bolsas_consumido, 'produccion', NEW.id_produccion, CONCAT('Consumo en producción #', NEW.id_produccion));
        
        -- 2. ACTUALIZAR inventario de producto terminado POR CALIDAD (ENTRADA)
        -- Asegurar que existe el registro de inventario para esta calidad
        INSERT IGNORE INTO inventario (tipo_item, id_calidad_napa, cantidad, unidad_medida, stock_minimo)
        VALUES ('producto_terminado', v_calidad_napa, 0, 'unidades', 100);
        
        SELECT cantidad INTO v_saldo_producto
        FROM inventario 
        WHERE tipo_item = 'producto_terminado' 
          AND id_calidad_napa = v_calidad_napa
        LIMIT 1;
        
        UPDATE inventario 
        SET cantidad = cantidad + v_cantidad_producida
        WHERE tipo_item = 'producto_terminado'
          AND id_calidad_napa = v_calidad_napa;
        
        INSERT INTO kardex (tipo_item, id_calidad_napa, tipo_movimiento, cantidad, unidad_medida, saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, observaciones)
        VALUES ('producto_terminado', v_calidad_napa, 'entrada', v_cantidad_producida, 'unidades', v_saldo_producto, v_saldo_producto + v_cantidad_producida, 'produccion', NEW.id_produccion, CONCAT('Producción #', NEW.id_produccion, ' aprobada'));
        
        -- 3. ACTUALIZAR cubo: incrementar cantidad producida real
        UPDATE cubos_fibra 
        SET cantidad_producida_real = COALESCE(cantidad_producida_real, 0) + v_cantidad_producida,
            estado = IF(
                (cantidad_estimada_bolsas - (COALESCE(cantidad_producida_real, 0) + v_cantidad_producida)) <= 0,
                'agotado',
                'en_uso'
            )
        WHERE id_cubo = NEW.id_cubo;
        
        -- 4. ACTUALIZAR lote: recalcular totales desde cubos
        UPDATE lotes_fibra l
        SET 
            cantidad_producida_real = (
                SELECT COALESCE(SUM(c.cantidad_producida_real), 0)
                FROM cubos_fibra c
                WHERE c.id_lote = l.id_lote
            ),
            cantidad_estimada_bolsas = (
                SELECT COALESCE(SUM(c.cantidad_estimada_bolsas), 0)
                FROM cubos_fibra c
                WHERE c.id_lote = l.id_lote
            ),
            estado = CASE
                WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'agotado') = (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote) THEN 'agotado'
                WHEN (SELECT COUNT(*) FROM cubos_fibra WHERE id_lote = l.id_lote AND estado = 'en_uso') > 0 THEN 'en_proceso'
                ELSE 'disponible'
            END
        WHERE l.id_lote = (SELECT id_lote FROM cubos_fibra WHERE id_cubo = NEW.id_cubo);
        
    END IF;
END$$

DELIMITER ;
