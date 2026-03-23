-- ============================================================================
-- Script auxiliar: Asignar tarifas a operadores
-- Ejecutar DESPUÉS de implementar_sistema_roles.sql
-- ============================================================================

USE `sistema_napa`;

-- Ejemplo: Actualizar tarifa de un operador específico
-- Reemplaza 'nombre_usuario' con el username real y 0.50 con la tarifa deseada

-- UPDATE usuarios 
-- SET tarifa_por_bolsa = 0.50 
-- WHERE username = 'operador1';

-- ============================================================================
-- Actualizar tarifas en bloque (ejemplo)
-- ============================================================================

-- Asignar tarifa de S/ 0.50 a todos los operadores que tienen tarifa 0
UPDATE usuarios 
SET tarifa_por_bolsa = 0.50 
WHERE rol IN ('operador', 'trabajador') 
  AND (tarifa_por_bolsa IS NULL OR tarifa_por_bolsa = 0.00)
  AND estado = 'activo';

-- ============================================================================
-- Consultar operadores y sus tarifas
-- ============================================================================

SELECT 
    id_usuario,
    username,
    nombre_completo,
    rol,
    tarifa_por_bolsa,
    estado,
    fecha_ingreso
FROM usuarios
WHERE rol IN ('operador', 'trabajador')
ORDER BY nombre_completo;

-- ============================================================================
-- Verificar si hay producciones sin comisión calculada
-- ============================================================================

SELECT 
    u.nombre_completo AS operador,
    DATE(p.fecha_produccion) AS fecha,
    COUNT(*) AS total_producciones,
    SUM(p.cantidad_producida) AS total_bolsas,
    u.tarifa_por_bolsa,
    SUM(p.cantidad_producida * u.tarifa_por_bolsa) AS comision_estimada
FROM producciones p
INNER JOIN usuarios u ON p.id_operario = u.id_usuario
WHERE p.estado_validacion = 'aprobado'
  AND p.id_comision IS NULL
  AND u.rol IN ('operador', 'trabajador')
GROUP BY u.nombre_completo, DATE(p.fecha_produccion), u.tarifa_por_bolsa
ORDER BY fecha DESC;

-- ============================================================================
-- Consultar comisiones calculadas
-- ============================================================================

SELECT * FROM v_comisiones_pendientes;

-- ============================================================================
