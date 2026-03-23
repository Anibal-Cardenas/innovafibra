<?php
/**
 * Controlador de Calidades de Fibra
 * Permite al administrador gestionar las calidades de fibra disponibles
 */

class CalidadesFibraController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    /**
     * Lista de calidades de fibra
     */
    public function index() {
        $data = [
            'title' => 'Calidades de Fibra',
            'calidades' => $this->getCalidades()
        ];
        
        $this->view('calidades_fibra/lista', $data);
    }
    
    /**
     * Nueva calidad de fibra
     */
    public function nuevo() {
        $data = ['title' => 'Nueva Calidad de Fibra'];
        
        if ($this->isPost()) {
            $result = $this->procesarNuevaCalidad();
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/calidades-fibra');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('calidades_fibra/nuevo', $data);
    }
    
    /**
     * Editar calidad de fibra
     */
    public function editar() {
        $idCalidad = $this->get('id');
        
        if (!$idCalidad) {
            setFlashMessage(MSG_ERROR, 'Calidad no especificada');
            redirect(BASE_URL . '/calidades-fibra');
        }
        
        $calidad = $this->getCalidadById($idCalidad);
        
        if (!$calidad) {
            setFlashMessage(MSG_ERROR, 'Calidad no encontrada');
            redirect(BASE_URL . '/calidades-fibra');
        }
        
        $data = [
            'title' => 'Editar Calidad de Fibra',
            'calidad' => $calidad
        ];
        
        if ($this->isPost()) {
            $result = $this->actualizarCalidad($idCalidad);
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/calidades-fibra');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('calidades_fibra/editar', $data);
    }
    
    /**
     * Procesar nueva calidad
     */
    private function procesarNuevaCalidad() {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $nombre = trim($this->post('nombre'));
        $descripcion = trim($this->post('descripcion'));
        $color = $this->post('color', 'info');
        
        // Validar
        $validator = new Validator($_POST);
        $validator
            ->required('nombre', 'El nombre es requerido')
            ->minLength('nombre', 3, 'El nombre debe tener al menos 3 caracteres');
        
        if ($validator->fails()) {
            $errors = $validator->getErrors();
            return ['success' => false, 'message' => implode('<br>', array_map(function($e) { 
                return implode('<br>', $e); 
            }, $errors))];
        }
        
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare(
                "INSERT INTO calidades_fibra 
                (nombre, descripcion, color, estado, usuario_creacion)
                VALUES (?, ?, ?, 'activo', ?)"
            );
            
            $stmt->execute([
                $nombre,
                $descripcion,
                $color,
                getCurrentUserId()
            ]);
            
            $idCalidad = $this->db->lastInsertId();
            
            registrarAuditoria(
                'calidades_fibra',
                $idCalidad,
                AUDITORIA_INSERT,
                "Calidad de fibra creada: {$nombre}"
            );
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Calidad de fibra registrada exitosamente'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Ya existe una calidad con ese nombre'];
            }
            
            error_log("Error al registrar calidad de fibra: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar la calidad'];
        }
    }
    
    /**
     * Actualizar calidad
     */
    private function actualizarCalidad($idCalidad) {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $nombre = trim($this->post('nombre'));
        $descripcion = trim($this->post('descripcion'));
        $color = $this->post('color', 'info');
        $estado = $this->post('estado');
        
        // Validar
        $validator = new Validator($_POST);
        $validator
            ->required('nombre')
            ->minLength('nombre', 3)
            ->required('estado');
        
        if ($validator->fails()) {
            $errors = $validator->getErrors();
            return ['success' => false, 'message' => implode('<br>', array_map(function($e) { 
                return implode('<br>', $e); 
            }, $errors))];
        }
        
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare(
                "UPDATE calidades_fibra 
                 SET nombre = ?, descripcion = ?, color = ?, estado = ?
                 WHERE id_calidad_fibra = ?"
            );
            
            $stmt->execute([
                $nombre,
                $descripcion,
                $color,
                $estado,
                $idCalidad
            ]);
            
            registrarAuditoria(
                'calidades_fibra',
                $idCalidad,
                AUDITORIA_UPDATE,
                "Calidad de fibra actualizada: {$nombre}"
            );
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Calidad actualizada exitosamente'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Ya existe una calidad con ese nombre'];
            }
            
            error_log("Error al actualizar calidad: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la calidad'];
        }
    }
    
    /**
     * Obtener todas las calidades
     */
    private function getCalidades() {
        $stmt = $this->db->query(
            "SELECT * FROM calidades_fibra ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener calidad por ID
     */
    private function getCalidadById($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM calidades_fibra WHERE id_calidad_fibra = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
