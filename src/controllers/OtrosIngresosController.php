<?php
/**
 * Controlador de Otros Ingresos
 */

class OtrosIngresosController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        // Permisos similares a Gastos, restringido a Administrador por defecto
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    public function index() {
        $mes = $this->get('mes', date('m'));
        $anio = $this->get('anio', date('Y'));
        
        $data = [
            'title' => 'Otros Ingresos',
            'ingresos' => $this->getIngresos($mes, $anio),
            'mes_seleccionado' => $mes,
            'anio_seleccionado' => $anio,
            'total_ingresos' => $this->getTotalIngresos($mes, $anio)
        ];
        
        $this->view('otros_ingresos/lista', $data);
    }
    
    public function nuevo() {
        $data = [
            'title' => 'Registrar Ingreso',
            'categorias' => [
                'Venta de Desperdicios', 'Servicios', 'Alquiler', 'Otros'
            ]
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarIngreso();
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/otros_ingresos');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('otros_ingresos/nuevo', $data);
    }
    
    public function editar($id) {
        $ingreso = $this->getIngresoById($id);
        if (!$ingreso) {
            setFlashMessage(MSG_ERROR, 'Ingreso no encontrado');
            redirect(BASE_URL . '/otros_ingresos');
        }

        $data = [
            'title' => 'Editar Ingreso',
            'ingreso' => $ingreso,
            'categorias' => [
                'Venta de Desperdicios', 'Servicios', 'Alquiler', 'Otros'
            ]
        ];

        if ($this->isPost()) {
            $result = $this->actualizarIngreso($id);
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/otros_ingresos');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }

        $this->view('otros_ingresos/editar', $data);
    }

    public function eliminar($id) {
        if (!$this->isPost()) {
            redirect(BASE_URL . '/otros_ingresos');
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM otros_ingresos WHERE id_ingreso = ?");
            $stmt->execute([$id]);
            setFlashMessage(MSG_SUCCESS, 'Ingreso eliminado correctamente');
        } catch (Exception $e) {
            setFlashMessage(MSG_ERROR, 'Error al eliminar: ' . $e->getMessage());
        }

        redirect(BASE_URL . '/otros_ingresos');
    }

    private function getIngresoById($id) {
        $stmt = $this->db->prepare("SELECT * FROM otros_ingresos WHERE id_ingreso = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function actualizarIngreso($id) {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token inválido'];
        }

        $validator = new Validator($_POST);
        $validator->required('fecha_ingreso')->required('categoria')->required('monto')->numeric('monto');

        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Complete los campos requeridos'];
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE otros_ingresos 
                 SET fecha_ingreso = ?, categoria = ?, descripcion = ?, monto = ? 
                 WHERE id_ingreso = ?"
            );
            $stmt->execute([
                $this->post('fecha_ingreso'),
                $this->post('categoria'),
                $this->post('descripcion'),
                $this->post('monto'),
                $id
            ]);
            return ['success' => true, 'message' => 'Ingreso actualizado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private function procesarIngreso() {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token inválido'];
        }
        
        $validator = new Validator($_POST);
        $validator->required('fecha_ingreso')->required('categoria')->required('monto')->numeric('monto');
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Complete los campos requeridos'];
        }
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO otros_ingresos (fecha_ingreso, categoria, descripcion, monto, usuario_creacion) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $this->post('fecha_ingreso'),
                $this->post('categoria'),
                $this->post('descripcion'),
                $this->post('monto'),
                getCurrentUserId()
            ]);
            return ['success' => true, 'message' => 'Ingreso registrado'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private function getIngresos($mes, $anio) {
        $stmt = $this->db->prepare(
            "SELECT * FROM otros_ingresos 
             WHERE MONTH(fecha_ingreso) = ? AND YEAR(fecha_ingreso) = ? 
             ORDER BY fecha_ingreso DESC"
        );
        $stmt->execute([$mes, $anio]);
        return $stmt->fetchAll();
    }
    
    private function getTotalIngresos($mes, $anio) {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM otros_ingresos WHERE MONTH(fecha_ingreso) = ? AND YEAR(fecha_ingreso) = ?"
        );
        $stmt->execute([$mes, $anio]);
        return $stmt->fetchColumn();
    }
}
