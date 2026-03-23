-- ============================================================================
-- MIGRACIÓN: Actualizar SP para obtener estado de comisión en resumen
-- Fecha: 19 de Enero, 2026
-- ============================================================================

USE sistema_napa;

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
        p.id_comision,
        c.estado AS estado_comision,  -- Nuevo campo
        c.fecha_pago                  -- Nuevo campo
    FROM producciones p
    INNER JOIN usuarios u ON p.id_operario = u.id_usuario
    LEFT JOIN comisiones c ON p.id_comision = c.id_comision -- Join para obtener estado
    WHERE p.id_operario = p_id_operario
      AND YEAR(p.fecha_produccion) = p_anio
      AND MONTH(p.fecha_produccion) = p_mes
    GROUP BY DATE(p.fecha_produccion), u.tarifa_por_bolsa, p.id_comision, c.estado, c.fecha_pago
    ORDER BY DATE(p.fecha_produccion) DESC;
END$$

DELIMITER ;

SELECT '✅ SP sp_resumen_comisiones_operario actualizado correctamente' as resultado;
