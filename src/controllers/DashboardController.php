<?php
/**
 * Dashboard Controller - UX Redesign
 * Enfoque: Claridad Financiera y Operativa
 */

class DashboardController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
    }
    
    public function index() {
        // Redirección según rol para UX personalizada
        if (isOperador()) {
            redirect(BASE_URL . '/produccion/mis_producciones');
        } elseif (isVendedor()) {
            redirect(BASE_URL . '/ventas');
        }

        // Obtener mes y año de filtro (o actuales por defecto)
        $mes = $this->get('mes', date('m'));
        $anio = $this->get('anio', date('Y'));

        // Lógica para Administrador: VISIÓN 360° DEL NEGOCIO
        $data = [
            'title' => 'Panel de Control',
            'financiero' => $this->getResumenFinancieroMes($mes, $anio),
            'alertas' => $this->getAlertasCriticas(),
            'produccion_hoy' => $this->getProduccionHoy(),
            'inventario_critico' => $this->getInventarioCritico(),
            'mes_seleccionado' => $mes,
            'anio_seleccionado' => $anio
        ];
        
        $this->view('dashboard/admin_ux', $data);
    }

    /**
     * Calcula el dinero real del mes seleccionado
     * Responde a: "¿Cómo vamos de plata este mes?"
     */
    private function getResumenFinancieroMes($mes, $anio) {
        // 1. Ingresos (Ventas no canceladas)
        $sqlVentas = "SELECT COALESCE(SUM(precio_total), 0) FROM ventas 
                      WHERE MONTH(fecha_venta) = ? AND YEAR(fecha_venta) = ? 
                      AND estado_pago != 'cancelado'";
        $stmt = $this->db->prepare($sqlVentas);
        $stmt->execute([$mes, $anio]);
        $ventas = (float)$stmt->fetchColumn();

        // 5. Otros Ingresos
        $sqlOtros = "SELECT COALESCE(SUM(monto), 0) FROM otros_ingresos 
                     WHERE MONTH(fecha_ingreso) = ? AND YEAR(fecha_ingreso) = ?";
        $stmt = $this->db->prepare($sqlOtros);
        $stmt->execute([$mes, $anio]);
        $otrosIngresos = (float)$stmt->fetchColumn();

        // 2. Gastos: Compras Fibra + Compras Bolsas
        $sqlComprasFibra = "SELECT COALESCE(SUM(precio_total), 0) FROM lotes_fibra 
                            WHERE MONTH(fecha_compra) = ? AND YEAR(fecha_compra) = ?";
        $stmt = $this->db->prepare($sqlComprasFibra);
        $stmt->execute([$mes, $anio]);
        $gastosFibra = (float)$stmt->fetchColumn();

        $sqlComprasBolsas = "SELECT COALESCE(SUM(precio_total), 0) FROM compras_bolsas 
                             WHERE MONTH(fecha_compra) = ? AND YEAR(fecha_compra) = ?";
        $stmt = $this->db->prepare($sqlComprasBolsas);
        $stmt->execute([$mes, $anio]);
        $gastosBolsas = (float)$stmt->fetchColumn();

        // 3. Gastos: Mano de Obra Estimada (Producción Aprobada * Tarifa)
        // Nota: Esto es una estimación operativa para el dashboard
        $sqlManoObra = "SELECT COALESCE(SUM(p.cantidad_producida * u.tarifa_por_bolsa), 0)
                        FROM producciones p
                        JOIN usuarios u ON p.id_operario = u.id_usuario
                        WHERE MONTH(p.fecha_produccion) = ? AND YEAR(p.fecha_produccion) = ?
                        AND p.estado_validacion = 'aprobado'";
        $stmt = $this->db->prepare($sqlManoObra);
        $stmt->execute([$mes, $anio]);
        $gastosManoObra = (float)$stmt->fetchColumn();

        // 4. Gastos Operativos (Luz, Agua, Sueldos Fijos, etc.)
        $sqlGastosOp = "SELECT COALESCE(SUM(monto), 0) FROM gastos_operativos 
                        WHERE MONTH(fecha_gasto) = ? AND YEAR(fecha_gasto) = ?";
        $stmt = $this->db->prepare($sqlGastosOp);
        $stmt->execute([$mes, $anio]);
        $gastosOperativos = (float)$stmt->fetchColumn();

        $totalGastos = $gastosFibra + $gastosBolsas + $gastosManoObra + $gastosOperativos;
        $totalIngresos = $ventas + $otrosIngresos;

        return [
            'ingresos' => $totalIngresos, // Mantenemos la key para compatibilidad, pero ahora incluye otros ingresos
            'ventas_netas' => $ventas,
            'otros_ingresos' => $otrosIngresos,
            'gastos' => $totalGastos,
            'utilidad' => $totalIngresos - $totalGastos
        ];
    }

    private function getAlertasCriticas() {
        // Solo lo urgente: Stock bajo o Mermas altas
        $alertas = [];
        
        // Stock bajo (Desactivado)
        $alertas['stock'] = [];

        // Producciones pendientes de validar
        $stmt = $this->db->query("SELECT COUNT(*) FROM producciones WHERE estado_validacion = 'pendiente'");
        $alertas['validaciones'] = $stmt->fetchColumn();

        return $alertas;
    }

    private function getProduccionHoy() {
        $stmt = $this->db->query("SELECT COALESCE(SUM(cantidad_producida), 0) FROM producciones 
                                  WHERE fecha_produccion = CURDATE() AND estado_validacion != 'rechazado'");
        return $stmt->fetchColumn();
    }

    private function getInventarioCritico() {
        // Vista simplificada para tarjetas
        return $this->db->query("SELECT 
            tipo_item, 
            cantidad, 
            unidad_medida,
            CASE WHEN tipo_item = 'producto_terminado' THEN 'Napa Lista' 
                 WHEN tipo_item = 'fibra' THEN 'Fibra (MP)'
                 ELSE 'Bolsas' END as nombre_amigable
            FROM inventario")->fetchAll();
    }
}