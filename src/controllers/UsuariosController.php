<?php
/**
 * Controlador de Usuarios
 * Sistema de Gestión de Producción - Taller de Napa
 */

class UsuariosController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    /**
     * Lista de usuarios
     */
    public function index() {
        $filtroRol = $this->get('rol', '');
        $filtroEstado = $this->get('estado', '');
        
        $sql = "SELECT * FROM usuarios WHERE 1=1";
        $params = [];
        
        if ($filtroRol) {
            $sql .= " AND rol = ?";
            $params[] = $filtroRol;
        }
        
        if ($filtroEstado) {
            $sql .= " AND estado = ?";
            $params[] = $filtroEstado;
        }
        
        $sql .= " ORDER BY nombre_completo";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll();
        
        $data = [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'filtro_rol' => $filtroRol,
            'filtro_estado' => $filtroEstado
        ];
        
        $this->view('usuarios/lista', $data);
    }
    
    /**
     * Formulario para nuevo usuario
     */
    public function nuevo() {
        $data = [
            'title' => 'Nuevo Usuario',
            'usuario' => null,
            'error' => null
        ];
        
        if ($this->isPost()) {
            $result = $this->guardarUsuario();
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, 'Usuario creado exitosamente');
                redirect(BASE_URL . '/usuarios');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('usuarios/form', $data);
    }
    
    /**
     * Formulario para editar usuario
     */
    public function editar($idUsuario = null) {
        if (!is_numeric($idUsuario)) {
            setFlashMessage(MSG_ERROR, 'ID de usuario inválido');
            redirect(BASE_URL . '/usuarios');
        }
        
        // Obtener datos del usuario
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            setFlashMessage(MSG_ERROR, 'Usuario no encontrado');
            redirect(BASE_URL . '/usuarios');
        }
        
        $data = [
            'title' => 'Editar Usuario',
            'usuario' => $usuario,
            'error' => null
        ];
        
        if ($this->isPost()) {
            $result = $this->actualizarUsuario($idUsuario);
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, 'Usuario actualizado exitosamente');
                redirect(BASE_URL . '/usuarios');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('usuarios/form', $data);
    }
    
    /**
     * Cambiar estado de usuario (activar/desactivar)
     */
    public function cambiarEstado() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idUsuario = $this->post('id_usuario');
        $nuevoEstado = $this->post('estado');
        
        if (empty($idUsuario) || empty($nuevoEstado)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        if (!in_array($nuevoEstado, [ESTADO_ACTIVO, ESTADO_INACTIVO])) {
            $this->json(['success' => false, 'message' => 'Estado inválido'], 400);
        }
        
        // No permitir desactivar al usuario actual
        if ($idUsuario == getCurrentUserId()) {
            $this->json(['success' => false, 'message' => 'No puede desactivar su propio usuario'], 400);
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
            $stmt->execute([$nuevoEstado, $idUsuario]);
            
            $this->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente'
            ]);
        } catch (Exception $e) {
            logError('Error al cambiar estado: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ], 500);
        }
    }
    
    /**
     * Actualizar tarifa de operador
     */
    public function actualizarTarifa() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idUsuario = $this->post('id_usuario');
        $tarifaNueva = $this->post('tarifa_nueva');
        $motivo = $this->post('motivo', 'Actualización de tarifa');
        
        if (empty($idUsuario) || !is_numeric($tarifaNueva)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        if ($tarifaNueva < 0) {
            $this->json(['success' => false, 'message' => 'La tarifa debe ser un número positivo'], 400);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Obtener tarifa anterior
            $stmt = $this->db->prepare("SELECT tarifa_por_bolsa FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$idUsuario]);
            $tarifaAnterior = $stmt->fetchColumn();
            
            // Actualizar tarifa
            $stmt = $this->db->prepare("UPDATE usuarios SET tarifa_por_bolsa = ? WHERE id_usuario = ?");
            $stmt->execute([$tarifaNueva, $idUsuario]);
            
            // Registrar en historial si existe la tabla
            try {
                $stmt = $this->db->prepare(
                    "INSERT INTO historial_tarifas (id_usuario, tarifa_anterior, tarifa_nueva, usuario_autorizo, motivo)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $idUsuario,
                    $tarifaAnterior,
                    $tarifaNueva,
                    getCurrentUserId(),
                    $motivo
                ]);
            } catch (Exception $e) {
                // Si la tabla no existe, continuar sin registrar historial
            }
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Tarifa actualizada exitosamente'
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            logError('Error al actualizar tarifa: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar la tarifa'
            ], 500);
        }
    }
    
    /**
     * Resetear contraseña
     */
    public function resetearPassword() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idUsuario = $this->post('id_usuario');
        $nuevaPassword = $this->post('nueva_password');
        
        if (empty($idUsuario) || empty($nuevaPassword)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        if (strlen($nuevaPassword) < 6) {
            $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
        }
        
        try {
            $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
            
            $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?");
            $stmt->execute([$passwordHash, $idUsuario]);
            
            $this->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ]);
        } catch (Exception $e) {
            logError('Error al resetear contraseña: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar la contraseña'
            ], 500);
        }
    }
    
    /**
     * Guardar nuevo usuario
     */
    private function guardarUsuario() {
        // Validar CSRF
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $username = sanitize($this->post('username'));
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        $nombreCompleto = sanitize($this->post('nombre_completo'));
        $dni = sanitize($this->post('dni'));
        $email = sanitize($this->post('email'));
        $rol = $this->post('rol');
        $tarifaPorBolsa = $this->post('tarifa_por_bolsa', 0);
        $fechaIngreso = $this->post('fecha_ingreso');
        $estado = $this->post('estado', ESTADO_ACTIVO);
        
        // Validaciones
        if (empty($username) || empty($password) || empty($nombreCompleto) || empty($rol)) {
            return ['success' => false, 'message' => 'Complete los campos obligatorios'];
        }
        
        if ($password !== $passwordConfirm) {
            return ['success' => false, 'message' => 'Las contraseñas no coinciden'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        if (!in_array($rol, [ROL_ADMINISTRADOR, ROL_OPERADOR, ROL_VENDEDOR, ROL_SUPERVISOR, ROL_TRABAJADOR])) {
            return ['success' => false, 'message' => 'Rol inválido'];
        }
        
        // Verificar que el username no exista
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
        }
        
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $this->db->prepare(
                "INSERT INTO usuarios (username, password_hash, nombre_completo, dni, email, rol, 
                                      tarifa_por_bolsa, fecha_ingreso, estado, usuario_creacion)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $username,
                $passwordHash,
                $nombreCompleto,
                $dni ?: null,
                $email ?: null,
                $rol,
                $tarifaPorBolsa,
                $fechaIngreso ?: null,
                $estado,
                getCurrentUserId()
            ]);
            
            return ['success' => true, 'message' => 'Usuario creado exitosamente'];
            
        } catch (PDOException $e) {
            logError('Error al crear usuario: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el usuario'];
        }
    }
    
    /**
     * Actualizar usuario existente
     */
    private function actualizarUsuario($idUsuario) {
        // Validar CSRF
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $username = sanitize($this->post('username'));
        $nombreCompleto = sanitize($this->post('nombre_completo'));
        $dni = sanitize($this->post('dni'));
        $email = sanitize($this->post('email'));
        $rol = $this->post('rol');
        $tarifaPorBolsa = $this->post('tarifa_por_bolsa', 0);
        $fechaIngreso = $this->post('fecha_ingreso');
        $estado = $this->post('estado', ESTADO_ACTIVO);
        $cambiarPassword = $this->post('cambiar_password') === '1';
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        
        // Validaciones
        if (empty($username) || empty($nombreCompleto) || empty($rol)) {
            return ['success' => false, 'message' => 'Complete los campos obligatorios'];
        }
        
        if ($cambiarPassword) {
            if (empty($password)) {
                return ['success' => false, 'message' => 'Ingrese la nueva contraseña'];
            }
            
            if ($password !== $passwordConfirm) {
                return ['success' => false, 'message' => 'Las contraseñas no coinciden'];
            }
            
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
            }
        }
        
        if (!in_array($rol, [ROL_ADMINISTRADOR, ROL_OPERADOR, ROL_VENDEDOR, ROL_SUPERVISOR, ROL_TRABAJADOR])) {
            return ['success' => false, 'message' => 'Rol inválido'];
        }
        
        // Verificar que el username no esté en uso por otro usuario
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ? AND id_usuario != ?");
        $stmt->execute([$username, $idUsuario]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Obtener tarifa anterior para historial
            $stmt = $this->db->prepare("SELECT tarifa_por_bolsa FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$idUsuario]);
            $tarifaAnterior = $stmt->fetchColumn();
            
            if ($cambiarPassword) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare(
                    "UPDATE usuarios 
                     SET username = ?, password_hash = ?, nombre_completo = ?, dni = ?, email = ?, 
                         rol = ?, tarifa_por_bolsa = ?, fecha_ingreso = ?, estado = ?
                     WHERE id_usuario = ?"
                );
                
                $stmt->execute([
                    $username,
                    $passwordHash,
                    $nombreCompleto,
                    $dni ?: null,
                    $email ?: null,
                    $rol,
                    $tarifaPorBolsa,
                    $fechaIngreso ?: null,
                    $estado,
                    $idUsuario
                ]);
            } else {
                $stmt = $this->db->prepare(
                    "UPDATE usuarios 
                     SET username = ?, nombre_completo = ?, dni = ?, email = ?, 
                         rol = ?, tarifa_por_bolsa = ?, fecha_ingreso = ?, estado = ?
                     WHERE id_usuario = ?"
                );
                
                $stmt->execute([
                    $username,
                    $nombreCompleto,
                    $dni ?: null,
                    $email ?: null,
                    $rol,
                    $tarifaPorBolsa,
                    $fechaIngreso ?: null,
                    $estado,
                    $idUsuario
                ]);
            }
            
            // Registrar cambio de tarifa en historial si cambió
            if ($tarifaAnterior != $tarifaPorBolsa) {
                try {
                    $stmt = $this->db->prepare(
                        "INSERT INTO historial_tarifas (id_usuario, tarifa_anterior, tarifa_nueva, usuario_autorizo, motivo)
                         VALUES (?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $idUsuario,
                        $tarifaAnterior,
                        $tarifaPorBolsa,
                        getCurrentUserId(),
                        'Actualización de usuario'
                    ]);
                } catch (Exception $e) {
                    // Si la tabla no existe, continuar
                }
            }
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            logError('Error al actualizar usuario: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el usuario'];
        }
    }
}
