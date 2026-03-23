<?php
/**
 * Controlador de Reportes
 * Sistema de Gestión de Producción - Taller de Napa
 */

class ReportesController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    /**
     * Reporte de mermas
     */
    public function mermas() {
        $fechaInicio = $this->get('fecha_inicio', date('Y-m-01'));
        $fechaFin = $this->get('fecha_fin', date('Y-m-d'));
        
        $data = [
            'title' => 'Reporte de Mermas',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'lotes' => $this->getLotesMerma($fechaInicio, $fechaFin),
            'resumen' => $this->getResumenMermas($fechaInicio, $fechaFin)
        ];
        
        $this->view('reportes/mermas', $data);
    }
    
    /**
     * Reporte de producción
     */
    public function produccion() {
        $fechaInicio = $this->get('fecha_inicio', date('Y-m-01'));
        $fechaFin = $this->get('fecha_fin', date('Y-m-d'));
        
        $data = [
            'title' => 'Reporte de Producción',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'produccion_diaria' => $this->getProduccionDiaria($fechaInicio, $fechaFin),
            'produccion_operarios' => $this->getProduccionPorOperario($fechaInicio, $fechaFin),
            'resumen' => $this->getResumenProduccion($fechaInicio, $fechaFin)
        ];
        
        $this->view('reportes/produccion', $data);
    }
    
    /**
     * Reporte de nómina (pago a trabajadores)
     */
    public function nomina() {
        $mes = $this->get('mes', date('Y-m'));
        
        $data = [
            'title' => 'Reporte de Nómina',
            'mes' => $mes,
            'nomina' => $this->getNomina($mes),
            'resumen' => $this->getResumenNomina($mes)
        ];
        
        $this->view('reportes/nomina', $data);
    }
    
    /**
     * Reporte de ventas
     */
    public function ventas() {
        $fechaInicio = $this->get('fecha_inicio', date('Y-m-01'));
        $fechaFin = $this->get('fecha_fin', date('Y-m-d'));
        
        $data = [
            'title' => 'Reporte de Ventas',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'ventas' => $this->getVentasReporte($fechaInicio, $fechaFin),
            'ventas_cliente' => $this->getVentasPorCliente($fechaInicio, $fechaFin),
            'resumen' => $this->getResumenVentas($fechaInicio, $fechaFin)
        ];
        
        $this->view('reportes/ventas', $data);
    }
    
    /**
     * Reporte de inventario
     */
    public function inventario() {
        $data = [
            'title' => 'Reporte de Inventario',
            'inventario' => $this->getInventarioActual(),
            'movimientos' => $this->getUltimosMovimientos()
        ];
        
        $this->view('reportes/inventario', $data);
    }
    
    /**
     * Obtener lotes con merma
     */
    private function getLotesMerma($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                l.codigo_lote,
                l.fecha_compra,
                l.peso_neto,
                l.cantidad_estimada_bolsas,
                l.cantidad_producida_real,
                (l.cantidad_estimada_bolsas - l.cantidad_producida_real) as merma,
                CASE 
                    WHEN l.cantidad_estimada_bolsas > 0 
                    THEN ((l.cantidad_producida_real / l.cantidad_estimada_bolsas) * 100)
                    ELSE 0
                END as eficiencia,
                l.estado,
                COUNT(p.id_produccion) as num_producciones,
                SUM(CASE WHEN p.flag_merma_excesiva = 1 THEN 1 ELSE 0 END) as producciones_con_merma
             FROM lotes_fibra l
             LEFT JOIN producciones p ON l.id_lote = p.id_lote_fibra
             WHERE l.fecha_compra BETWEEN ? AND ?
             GROUP BY l.id_lote
             HAVING merma > 0
             ORDER BY eficiencia ASC"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    /**
     * Resumen de mermas
     */
    private function getResumenMermas($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total_lotes,
                SUM(l.cantidad_estimada_bolsas) as estimado_total,
                SUM(l.cantidad_producida_real) as producido_total,
                SUM(l.cantidad_estimada_bolsas - l.cantidad_producida_real) as merma_total,
                AVG(CASE 
                    WHEN l.cantidad_estimada_bolsas > 0 
                    THEN ((l.cantidad_producida_real / l.cantidad_estimada_bolsas) * 100)
                    ELSE 0
                END) as eficiencia_promedio,
                COUNT(CASE WHEN l.estado = 'merma_excesiva' THEN 1 END) as lotes_merma_excesiva
             FROM lotes_fibra l
             WHERE l.fecha_compra BETWEEN ? AND ?"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetch();
    }
    
    /**
     * Producción diaria
     */
    private function getProduccionDiaria($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                p.fecha_produccion,
                COUNT(*) as num_producciones,
                SUM(p.cantidad_producida) as total_producido,
                AVG(p.eficiencia_porcentual) as eficiencia_promedio,
                SUM(CASE WHEN p.flag_merma_excesiva = 1 THEN 1 ELSE 0 END) as con_merma
             FROM producciones p
             WHERE p.fecha_produccion BETWEEN ? AND ?
               AND p.estado_validacion = 'aprobado'
             GROUP BY p.fecha_produccion
             ORDER BY p.fecha_produccion"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    /**
     * Producción por operario
     */
    private function getProduccionPorOperario($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                u.nombre_completo,
                COUNT(*) as num_producciones,
                SUM(p.cantidad_producida) as total_producido,
                AVG(p.eficiencia_porcentual) as eficiencia_promedio,
                u.tarifa_por_bolsa,
                SUM(CASE 
                    WHEN p.estado_validacion = 'aprobado' 
                    THEN p.cantidad_producida * u.tarifa_por_bolsa
                    ELSE 0
                END) as total_pagar
             FROM producciones p
             INNER JOIN usuarios u ON p.id_operario = u.id_usuario
             WHERE p.fecha_produccion BETWEEN ? AND ?
             GROUP BY p.id_operario
             ORDER BY total_producido DESC"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    /**
     * Resumen de producción
     */
    private function getResumenProduccion($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total_producciones,
                SUM(p.cantidad_producida) as total_producido,
                AVG(p.eficiencia_porcentual) as eficiencia_promedio,
                SUM(CASE WHEN p.flag_merma_excesiva = 1 THEN 1 ELSE 0 END) as con_merma,
                SUM(CASE WHEN p.estado_validacion = 'aprobado' THEN 1 ELSE 0 END) as aprobadas,
                SUM(CASE WHEN p.estado_validacion = 'rechazado' THEN 1 ELSE 0 END) as rechazadas,
                SUM(CASE WHEN p.estado_validacion = 'pendiente' THEN 1 ELSE 0 END) as pendientes
             FROM producciones p
             WHERE p.fecha_produccion BETWEEN ? AND ?"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetch();
    }
    
    /**
     * Nómina del mes
     */
    private function getNomina($mes) {
        $fechaInicio = $mes . '-01';
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));
        
        $stmt = $this->db->prepare(
            "SELECT 
                u.nombre_completo,
                u.tarifa_por_bolsa,
                COUNT(p.id_produccion) as num_producciones,
                SUM(CASE WHEN p.estado_validacion = 'aprobado' THEN p.cantidad_producida ELSE 0 END) as bolsas_aprobadas,
                SUM(CASE WHEN p.estado_validacion = 'rechazado' THEN p.cantidad_producida ELSE 0 END) as bolsas_rechazadas,
                SUM(CASE 
                    WHEN p.estado_validacion = 'aprobado' 
                    THEN p.cantidad_producida * u.tarifa_por_bolsa
                    ELSE 0
                END) as total_pagar
             FROM usuarios u
             LEFT JOIN producciones p ON u.id_usuario = p.id_operario 
                AND p.fecha_produccion BETWEEN ? AND ?
             WHERE u.rol = 'trabajador' AND u.estado = 'activo'
             GROUP BY u.id_usuario
             ORDER BY total_pagar DESC"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    /**
     * Resumen de nómina
     */
    private function getResumenNomina($mes) {
        $fechaInicio = $mes . '-01';
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));
        
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(DISTINCT u.id_usuario) as num_trabajadores,
                SUM(CASE 
                    WHEN p.estado_validacion = 'aprobado' 
                    THEN p.cantidad_producida * u.tarifa_por_bolsa
                    ELSE 0
                END) as total_nomina,
                AVG(u.tarifa_por_bolsa) as tarifa_promedio
             FROM usuarios u
             LEFT JOIN producciones p ON u.id_usuario = p.id_operario 
                AND p.fecha_produccion BETWEEN ? AND ?
             WHERE u.rol = 'trabajador' AND u.estado = 'activo'"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetch();
    }
    
    /**
     * Ventas para reporte
     */
    private function getVentasReporte($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                v.fecha_venta,
                v.codigo_guia_remision,
                c.nombre as cliente,
                v.cantidad,
                v.precio_unitario,
                v.precio_total,
                v.costo_total
             FROM ventas v
             INNER JOIN clientes c ON v.id_cliente = c.id_cliente
             WHERE v.fecha_venta BETWEEN ? AND ?
             ORDER BY v.fecha_venta DESC"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    /**
     * Ventas por cliente
     */
    private function getVentasPorCliente($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                c.nombre,
                COUNT(v.id_venta) as num_ventas,
                SUM(v.cantidad) as total_bolsas,
                SUM(v.precio_total) as total_ventas
             FROM ventas v
             INNER JOIN clientes c ON v.id_cliente = c.id_cliente
             WHERE v.fecha_venta BETWEEN ? AND ?
             GROUP BY v.id_cliente
             ORDER BY total_ventas DESC"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    /**
     * Resumen de ventas
     */
    private function getResumenVentas($fechaInicio, $fechaFin) {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as num_ventas,
                SUM(v.cantidad) as total_bolsas,
                SUM(v.precio_total) as total_ventas,
                SUM(v.costo_total) as total_costos
             FROM ventas v
             WHERE v.fecha_venta BETWEEN ? AND ?"
        );
        
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetch();
    }
    
    /**
     * Inventario actual
     */
    private function getInventarioActual() {
        $stmt = $this->db->query(
            "SELECT * FROM v_estado_inventario ORDER BY tipo_item"
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Últimos movimientos de inventario
     */
    private function getUltimosMovimientos() {
        $stmt = $this->db->query(
            "SELECT 
                k.fecha_movimiento,
                k.tipo_item,
                k.tipo_movimiento,
                k.cantidad,
                k.unidad_medida,
                k.documento_referencia,
                k.observaciones
             FROM kardex k
             ORDER BY k.fecha_movimiento DESC
             LIMIT 50"
        );
        
        return $stmt->fetchAll();
    }
}
