<?php
/**
 * Controlador de Proveedores
 * Sistema de Gestión de Producción - Taller de Napa
 */

class ProveedoresController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    /**
     * Lista de proveedores
     */
    public function index() {
        $data = [
            'title' => 'Proveedores',
            'proveedores' => $this->getProveedores()
        ];
        
        $this->view('proveedores/lista', $data);
    }
    
    /**
     * Registrar nuevo proveedor
     */
    public function nuevo() {
        $data = [
            'title' => 'Nuevo Proveedor'
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarProveedor();
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/proveedores');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('proveedores/nuevo', $data);
    }
    
    /**
     * Editar proveedor
     */
    public function editar() {
        $idProveedor = $this->get('id');
        
        if (!$idProveedor) {
            setFlashMessage(MSG_ERROR, 'Proveedor no especificado');
            redirect(BASE_URL . '/proveedores');
        }
        
        $proveedor = $this->getProveedorById($idProveedor);
        
        if (!$proveedor) {
            setFlashMessage(MSG_ERROR, 'Proveedor no encontrado');
            redirect(BASE_URL . '/proveedores');
        }
        
        $data = [
            'title' => 'Editar Proveedor',
            'proveedor' => $proveedor
        ];
        
        if ($this->isPost()) {
            $result = $this->actualizarProveedor($idProveedor);
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/proveedores');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('proveedores/editar', $data);
    }
    
    /**
     * Eliminar proveedor (cambiar estado a inactivo)
     */
    public function eliminar() {
        $idProveedor = $this->post('id');
        
        if (!$idProveedor || !verifyCsrfToken($this->post('csrf_token'))) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        try {
            // Verificar si tiene compras asociadas
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) as total FROM lotes_fibra WHERE id_proveedor = ?
                 UNION ALL
                 SELECT COUNT(*) as total FROM compras_bolsas WHERE id_proveedor = ?"
            );
            $stmt->execute([$idProveedor, $idProveedor]);
            $totales = $stmt->fetchAll();
            $totalCompras = array_sum(array_column($totales, 'total'));
            
            if ($totalCompras > 0) {
                // No eliminar, solo desactivar
                $stmt = $this->db->prepare("UPDATE proveedores SET estado = 'inactivo' WHERE id_proveedor = ?");
                $stmt->execute([$idProveedor]);
                
                registrarAuditoria('proveedores', $idProveedor, AUDITORIA_UPDATE, 'Proveedor desactivado (tiene compras asociadas)');
                
                $this->json(['success' => true, 'message' => 'Proveedor desactivado (tiene compras asociadas)']);
            } else {
                // Eliminar físicamente
                $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
                $stmt->execute([$idProveedor]);
                
                registrarAuditoria('proveedores', $idProveedor, AUDITORIA_DELETE, 'Proveedor eliminado');
                
                $this->json(['success' => true, 'message' => 'Proveedor eliminado']);
            }
            
        } catch (PDOException $e) {
            error_log("Error al eliminar proveedor: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error al eliminar el proveedor'], 500);
        }
    }
    
    /**
     * Procesar nuevo proveedor
     */
    private function procesarProveedor() {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $validator = new Validator($_POST);
        $validator
            ->required('nombre', 'El nombre es requerido')
            ->required('tipo_proveedor', 'El tipo de proveedor es requerido')
            ->minLength('nombre', 3, 'El nombre debe tener al menos 3 caracteres');
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => implode('<br>', array_map(function($errors) {
                return implode('<br>', $errors);
            }, $validator->errors()))];
        }
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO proveedores 
                (nombre, ruc, direccion, telefono, email, contacto_principal, tipo_proveedor)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $this->post('nombre'),
                $this->post('ruc'),
                $this->post('direccion'),
                $this->post('telefono'),
                $this->post('email'),
                $this->post('contacto_principal'),
                $this->post('tipo_proveedor')
            ]);
            
            $idProveedor = $this->db->lastInsertId();
            
            registrarAuditoria('proveedores', $idProveedor, AUDITORIA_INSERT, "Proveedor registrado: {$this->post('nombre')}");
            
            return ['success' => true, 'message' => 'Proveedor registrado exitosamente'];
            
        } catch (PDOException $e) {
            error_log("Error al registrar proveedor: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar el proveedor'];
        }
    }
    
    /**
     * Actualizar proveedor
     */
    private function actualizarProveedor($idProveedor) {
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $validator = new Validator($_POST);
        $validator
            ->required('nombre')
            ->required('tipo_proveedor')
            ->minLength('nombre', 3);
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Por favor complete los campos requeridos'];
        }
        
        try {
            $stmt = $this->db->prepare(
                "UPDATE proveedores 
                SET nombre = ?, ruc = ?, direccion = ?, telefono = ?, 
                    email = ?, contacto_principal = ?, tipo_proveedor = ?, estado = ?
                WHERE id_proveedor = ?"
            );
            
            $stmt->execute([
                $this->post('nombre'),
                $this->post('ruc'),
                $this->post('direccion'),
                $this->post('telefono'),
                $this->post('email'),
                $this->post('contacto_principal'),
                $this->post('tipo_proveedor'),
                $this->post('estado'),
                $idProveedor
            ]);
            
            registrarAuditoria('proveedores', $idProveedor, AUDITORIA_UPDATE, "Proveedor actualizado: {$this->post('nombre')}");
            
            return ['success' => true, 'message' => 'Proveedor actualizado exitosamente'];
            
        } catch (PDOException $e) {
            error_log("Error al actualizar proveedor: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el proveedor'];
        }
    }
    
    /**
     * Obtener todos los proveedores
     */
    private function getProveedores() {
        $stmt = $this->db->query(
            "SELECT * FROM proveedores ORDER BY nombre"
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener proveedor por ID
     */
    private function getProveedorById($idProveedor) {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id_proveedor = ?");
        $stmt->execute([$idProveedor]);
        return $stmt->fetch();
    }
}
