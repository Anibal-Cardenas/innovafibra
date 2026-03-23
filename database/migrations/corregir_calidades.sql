-- ============================================================================
-- CORRECCIÓN: Eliminar calidad sobrante y ajustar ventas
-- ============================================================================

USE sistema_napa;

-- 1. Eliminar calidad de napa sobrante (Napa Básica)
-- Primero eliminar referencias en inventario
DELETE FROM inventario 
WHERE tipo_item = 'producto_terminado' 
  AND id_calidad_napa = 4;

-- Eliminar la calidad Napa Básica
DELETE FROM calidades_napa 
WHERE id_calidad_napa = 4;

-- 2. RECREAR vista de stock para ventas - solo mostrar con stock > 0
DROP VIEW IF EXISTS v_stock_ventas;

CREATE VIEW v_stock_ventas AS
SELECT 
    cn.id_calidad_napa,
    cn.nombre AS calidad_napa,
    cn.codigo,
    cn.precio_base_sugerido,
    COALESCE(i.cantidad, 0) AS stock_disponible,
    i.unidad_medida,
    CASE
        WHEN COALESCE(i.cantidad, 0) = 0 THEN 'Sin stock'
        WHEN i.cantidad <= i.stock_minimo THEN 'Stock bajo'
        ELSE 'Disponible'
    END AS estado_stock
FROM calidades_napa cn
LEFT JOIN inventario i ON i.tipo_item = 'producto_terminado' AND i.id_calidad_napa = cn.id_calidad_napa
WHERE cn.estado = 'activo'
  AND COALESCE(i.cantidad, 0) > 0  -- SOLO MOSTRAR CON STOCK
ORDER BY cn.codigo;

-- Verificar
SELECT 'Calidades de napa (debe ser 3):' AS info;
SELECT * FROM calidades_napa;

SELECT 'Stock disponible para ventas (solo con stock > 0):' AS info;
SELECT * FROM v_stock_ventas;
