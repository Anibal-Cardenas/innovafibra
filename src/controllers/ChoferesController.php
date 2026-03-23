<?php
/**
 * Controlador de Choferes
 * Sistema de Gestión de Producción - Taller de Napa
 */

class ChoferesController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    /**
     * Lista de choferes
     */
    public function index() {
        $data = [
            'title' => 'Choferes',
            'choferes' => $this->getChoferes()
        ];
        
        $this->view('choferes/lista', $data);
    }
    
    /**
     * Registrar nuevo chofer
     */
    public function nuevo() {
        $data = [
            'title' => 'Nuevo Chofer'
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarChofer();
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/choferes');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('choferes/nuevo', $data);
    }
    
    /**
     * Editar chofer
     */
    public function editar() {
        $idChofer = $this->get('id');
        
        if (!$idChofer) {
            setFlashMessage(MSG_ERROR, 'Chofer no especificado');
            redirect(BASE_URL . '/choferes');
        }
        
        $chofer = $this->getChoferById($idChofer);
        
        if (!$chofer) {
            setFlashMessage(MSG_ERROR, 'Chofer no encontrado');
            redirect(BASE_URL . '/choferes');
        }
        
        $data = [
            'title' => 'Editar Chofer',
            'chofer' => $chofer
        ];
        
        if ($this->isPost()) {
            $result = $this->actualizarChofer($idChofer);
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/choferes');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('choferes/editar', $data);
    }
    
    /**
     * Procesar nuevo chofer
     */
    private function procesarChofer() {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $validator = new Validator($_POST);
        $validator
            ->required('nombre_completo', 'El nombre es requerido')
            ->minLength('nombre_completo', 3);
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Por favor complete los campos requeridos'];
        }
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO choferes 
                (nombre_completo, dni, licencia, telefono, sueldo, vehiculo)
                VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $this->post('nombre_completo'),
                $this->post('dni'),
                $this->post('licencia'),
                $this->post('telefono'),
                $this->post('sueldo') ?: 0,
                $this->post('vehiculo')
            ]);
            
            $idChofer = $this->db->lastInsertId();
            
            registrarAuditoria('choferes', $idChofer, AUDITORIA_INSERT, "Chofer registrado: {$this->post('nombre_completo')}");
            
            return ['success' => true, 'message' => 'Chofer registrado exitosamente'];
            
        } catch (PDOException $e) {
            error_log("Error al registrar chofer: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar el chofer'];
        }
    }
    
    /**
     * Actualizar chofer
     */
    private function actualizarChofer($idChofer) {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $validator = new Validator($_POST);
        $validator
            ->required('nombre_completo')
            ->minLength('nombre_completo', 3);
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Por favor complete los campos requeridos'];
        }
        
        try {
            $stmt = $this->db->prepare(
                "UPDATE choferes 
                SET nombre_completo = ?, dni = ?, licencia = ?, 
                    telefono = ?, sueldo = ?, vehiculo = ?, estado = ?
                WHERE id_chofer = ?"
            );
            
            $stmt->execute([
                $this->post('nombre_completo'),
                $this->post('dni'),
                $this->post('licencia'),
                $this->post('telefono'),
                $this->post('sueldo') ?: 0,
                $this->post('vehiculo'),
                $this->post('estado'),
                $idChofer
            ]);
            
            registrarAuditoria('choferes', $idChofer, AUDITORIA_UPDATE, "Chofer actualizado: {$this->post('nombre_completo')}");
            
            return ['success' => true, 'message' => 'Chofer actualizado exitosamente'];
            
        } catch (PDOException $e) {
            error_log("Error al actualizar chofer: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el chofer'];
        }
    }
    
    /**
     * Obtener todos los choferes
     */
    private function getChoferes() {
        $stmt = $this->db->query(
            "SELECT * FROM choferes ORDER BY nombre_completo"
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener chofer por ID
     */
    private function getChoferById($idChofer) {
        $stmt = $this->db->prepare("SELECT * FROM choferes WHERE id_chofer = ?");
        $stmt->execute([$idChofer]);
        return $stmt->fetch();
    }
}
