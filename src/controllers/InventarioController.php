<?php
/**
 * Controlador de Inventario General
 * Sistema de Gestión de Producción - Taller de Napa
 */

class InventarioController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        // Acceso permitido a Administradores y Supervisores
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    /**
     * Vista principal del inventario
     */
    public function index() {
        $data = [
            'title' => 'Inventario General',
            'stock_fibra' => $this->getStockFibra(),
            'stock_bolsas' => $this->getStockBolsas(),
            'stock_napa' => $this->getStockNapa(),
            'movimientos' => $this->getUltimosMovimientos()
        ];
        
        $this->view('inventario/index', $data);
    }
    
    /**
     * Editar item de inventario
     */
    public function editar($id) {
        $item = $this->getInventarioById($id);
        
        if (!$item) {
            setFlashMessage(MSG_ERROR, 'Item de inventario no encontrado.');
            redirect(BASE_URL . '/inventario');
        }
        
        $data = [
            'title' => 'Editar Inventario',
            'item' => $item
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarEdicion($id, $item);
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/inventario');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('inventario/editar', $data);
    }

    private function procesarEdicion($id, $itemActual) {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token inválido'];
        }
        
        $nuevoStock = $this->post('cantidad');
        $nuevoMinimo = $this->post('stock_minimo');
        $observacion = $this->post('observacion');
        
        if (!is_numeric($nuevoStock) || $nuevoStock < 0) {
            return ['success' => false, 'message' => 'La cantidad debe ser un número positivo.'];
        }
        
        if (!is_numeric($nuevoMinimo) || $nuevoMinimo < 0) {
            return ['success' => false, 'message' => 'El stock mínimo debe ser un número positivo.'];
        }
        
        $diferencia = $nuevoStock - $itemActual['cantidad'];
        
        try {
            $this->db->beginTransaction();
            
            // 1. Actualizar inventario
            $stmt = $this->db->prepare("UPDATE inventario SET cantidad = ?, stock_minimo = ? WHERE id_inventario = ?");
            $stmt->execute([$nuevoStock, $nuevoMinimo, $id]);
            
            // 2. Si hubo cambio en cantidad, registrar en Kardex
            if (abs($diferencia) > 0.001) {
                if (empty($observacion)) {
                    throw new Exception("Debe indicar un motivo para el ajuste de inventario.");
                }
                
                $stmtKardex = $this->db->prepare(
                    "INSERT INTO kardex (tipo_item, id_calidad_napa, tipo_movimiento, cantidad, unidad_medida, 
                                       saldo_anterior, saldo_nuevo, referencia_tipo, referencia_id, observaciones, usuario_registro) 
                     VALUES (?, ?, 'ajuste', ?, ?, ?, ?, 'manual', NULL, ?, ?)"
                );
                
                $stmtKardex->execute([
                    $itemActual['tipo_item'],
                    $itemActual['id_calidad_napa'],
                    abs($diferencia),
                    $itemActual['unidad_medida'],
                    $itemActual['cantidad'],
                    $nuevoStock,
                    $observacion,
                    getCurrentUserId()
                ]);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Inventario actualizado correctamente.'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    private function getInventarioById($id) {
        $stmt = $this->db->prepare("
            SELECT i.*, cn.nombre as nombre_calidad 
            FROM inventario i 
            LEFT JOIN calidades_napa cn ON i.id_calidad_napa = cn.id_calidad_napa 
            WHERE i.id_inventario = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene el stock de bolsas plásticas
     */
    private function getStockBolsas() {
        return $this->db->query("SELECT * FROM inventario WHERE tipo_item = 'bolsas_plasticas' AND cantidad > 0")->fetchAll();
    }

    /**
     * Obtiene el stock de producto terminado (Napa)
     */
    private function getStockNapa() {
        try {
            $sql = "SELECT 
                        i.*,
                        cn.nombre as nombre_calidad,
                        cn.codigo as codigo_calidad
                    FROM inventario i
                    LEFT JOIN calidades_napa cn ON i.id_calidad_napa = cn.id_calidad_napa
                    WHERE i.tipo_item = 'producto_terminado'
                    AND i.cantidad > 0
                    ORDER BY cn.codigo";
            return $this->db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene el stock de fibra disponible (agrupado por calidad) desde los cubos
     */
    private function getStockFibra() {
        $sql = "SELECT 
                    cf.nombre as calidad_fibra,
                    cf.color as color_calidad,
                    COUNT(c.id_cubo) as cantidad_cubos,
                    SUM(c.peso_neto) as peso_total
                FROM cubos_fibra c
                JOIN lotes_fibra l ON c.id_lote = l.id_lote
                LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
                WHERE c.estado = 'disponible'
                GROUP BY cf.id_calidad_fibra, cf.nombre, cf.color
                ORDER BY peso_total DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtiene los últimos movimientos del Kardex
     */
    private function getUltimosMovimientos() {
        $sql = "SELECT 
                    k.*,
                    k.observaciones as observacion,
                    CASE 
                        WHEN k.tipo_item = 'fibra' THEN 'Fibra (Materia Prima)'
                        WHEN k.tipo_item = 'bolsas_plasticas' THEN 'Bolsas Plásticas'
                        WHEN k.tipo_item = 'producto_terminado' THEN 
                            CASE 
                                WHEN cn.nombre IS NOT NULL THEN CONCAT('Napa - ', cn.nombre)
                                ELSE 'Napa (Producto Terminado)'
                            END
                        ELSE k.tipo_item 
                    END as item
                FROM kardex k
                LEFT JOIN calidades_napa cn ON k.id_calidad_napa = cn.id_calidad_napa
                ORDER BY k.fecha_movimiento DESC 
                LIMIT 50";
        return $this->db->query($sql)->fetchAll();
    }
}