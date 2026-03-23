<?php
/**
 * Controlador de Gastos Operativos
 */

class GastosController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    public function index() {
        $mes = $this->get('mes', date('m'));
        $anio = $this->get('anio', date('Y'));
        
        $data = [
            'title' => 'Gastos Operativos',
            'gastos' => $this->getGastos($mes, $anio),
            'mes_seleccionado' => $mes,
            'anio_seleccionado' => $anio,
            'total_gastos' => $this->getTotalGastos($mes, $anio)
        ];
        
        $this->view('gastos/lista', $data);
    }
    
    public function nuevo() {
        $data = [
            'title' => 'Registrar Gasto',
            'categorias' => [
                'Alquiler Local', 'Luz', 'Flete', 'Gasolina', 
                'Repuestos', 'Mantenimiento', 'Sueldo Choferes', 'Otros'
            ]
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarGasto();
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/gastos');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('gastos/nuevo', $data);
    }
    
    public function editar($id) {
        $gasto = $this->getGastoById($id);
        if (!$gasto) {
            setFlashMessage(MSG_ERROR, 'Gasto no encontrado');
            redirect(BASE_URL . '/gastos');
        }

        $data = [
            'title' => 'Editar Gasto',
            'gasto' => $gasto,
            'categorias' => [
                'Alquiler Local', 'Luz', 'Flete', 'Gasolina', 
                'Repuestos', 'Mantenimiento', 'Sueldo Choferes', 'Otros'
            ]
        ];

        if ($this->isPost()) {
            $result = $this->actualizarGasto($id);
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/gastos');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }

        $this->view('gastos/editar', $data);
    }

    public function eliminar($id) {
        if (!$this->isPost()) {
            redirect(BASE_URL . '/gastos');
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM gastos_operativos WHERE id_gasto = ?");
            $stmt->execute([$id]);
            setFlashMessage(MSG_SUCCESS, 'Gasto eliminado correctamente');
        } catch (Exception $e) {
            setFlashMessage(MSG_ERROR, 'Error al eliminar: ' . $e->getMessage());
        }

        redirect(BASE_URL . '/gastos');
    }

    private function getGastoById($id) {
        $stmt = $this->db->prepare("SELECT * FROM gastos_operativos WHERE id_gasto = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function actualizarGasto($id) {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token inválido'];
        }

        $validator = new Validator($_POST);
        $validator->required('fecha_gasto')->required('categoria')->required('monto')->numeric('monto');

        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Complete los campos requeridos'];
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE gastos_operativos 
                 SET fecha_gasto = ?, categoria = ?, descripcion = ?, monto = ? 
                 WHERE id_gasto = ?"
            );
            $stmt->execute([
                $this->post('fecha_gasto'),
                $this->post('categoria'),
                $this->post('descripcion'),
                $this->post('monto'),
                $id
            ]);
            return ['success' => true, 'message' => 'Gasto actualizado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function importarSueldos() {
        if (!$this->isPost()) redirect(BASE_URL . '/gastos');
        
        $mes = $this->post('mes', date('m'));
        $anio = $this->post('anio', date('Y'));
        $fechaGasto = "$anio-$mes-01"; // Primer día del mes
        
        try {
            $this->db->beginTransaction();
            
            // Obtener choferes activos con sueldo > 0
            $stmt = $this->db->query("SELECT nombre_completo, sueldo FROM choferes WHERE estado = 'activo' AND sueldo > 0");
            $choferes = $stmt->fetchAll();
            
            $count = 0;
            $stmtInsert = $this->db->prepare(
                "INSERT INTO gastos_operativos (fecha_gasto, categoria, descripcion, monto, usuario_creacion) 
                 VALUES (?, 'Sueldo Choferes', ?, ?, ?)"
            );
            
            foreach ($choferes as $chofer) {
                $descripcion = "Sueldo Mensual - " . $chofer['nombre_completo'];
                $stmtInsert->execute([$fechaGasto, $descripcion, $chofer['sueldo'], getCurrentUserId()]);
                $count++;
            }
            
            $this->db->commit();
            setFlashMessage(MSG_SUCCESS, "Se importaron $count sueldos de choferes correctamente.");
            
        } catch (Exception $e) {
            $this->db->rollBack();
            setFlashMessage(MSG_ERROR, "Error al importar sueldos: " . $e->getMessage());
        }
        
        redirect(BASE_URL . '/gastos?mes=' . $mes . '&anio=' . $anio);
    }
    
    private function procesarGasto() {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token inválido'];
        }
        
        $validator = new Validator($_POST);
        $validator->required('fecha_gasto')->required('categoria')->required('monto')->numeric('monto');
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Complete los campos requeridos'];
        }
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO gastos_operativos (fecha_gasto, categoria, descripcion, monto, usuario_creacion) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $this->post('fecha_gasto'),
                $this->post('categoria'),
                $this->post('descripcion'),
                $this->post('monto'),
                getCurrentUserId()
            ]);
            return ['success' => true, 'message' => 'Gasto registrado'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private function getGastos($mes, $anio) {
        $stmt = $this->db->prepare(
            "SELECT * FROM gastos_operativos 
             WHERE MONTH(fecha_gasto) = ? AND YEAR(fecha_gasto) = ? 
             ORDER BY fecha_gasto DESC"
        );
        $stmt->execute([$mes, $anio]);
        return $stmt->fetchAll();
    }
    
    private function getTotalGastos($mes, $anio) {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto), 0) FROM gastos_operativos WHERE MONTH(fecha_gasto) = ? AND YEAR(fecha_gasto) = ?"
        );
        $stmt->execute([$mes, $anio]);
        return $stmt->fetchColumn();
    }
}