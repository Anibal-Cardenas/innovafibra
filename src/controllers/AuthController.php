<?php
/**
 * Controlador de Autenticación
 * Sistema de Gestión de Producción - Taller de Napa
 */

class AuthController extends BaseController {
    
    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if (isLoggedIn()) {
            redirect(BASE_URL . '/dashboard');
        }
        
        $data = [
            'title' => 'Iniciar Sesión',
            'error' => null
        ];
        
        // Procesar formulario
        if ($this->isPost()) {
            $username = sanitize($this->post('username'));
            $password = $this->post('password');
            
            // Validar campos
            if (empty($username) || empty($password)) {
                $data['error'] = 'Por favor ingrese usuario y contraseña';
            } else {
                // Intentar autenticar
                $user = $this->authenticate($username, $password);
                
                if ($user) {
                    // Login exitoso
                    loginUser(
                        $user['id_usuario'],
                        $user['username'],
                        $user['nombre_completo'],
                        $user['rol']
                    );
                    
                    // Redirigir según rol
                    if ($user['rol'] === ROL_OPERADOR || $user['rol'] === ROL_TRABAJADOR) {
                        redirect(BASE_URL . '/produccion/misproducciones');
                    } elseif ($user['rol'] === ROL_VENDEDOR) {
                        redirect(BASE_URL . '/ventas');
                    } else {
                        redirect(BASE_URL . '/dashboard');
                    }
                } else {
                    $data['error'] = 'Usuario o contraseña incorrectos';
                }
            }
        }
        
        // Mostrar vista de login (sin header/footer)
        require_once VIEWS_PATH . '/auth/login.php';
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        logoutUser();
        setFlashMessage(MSG_INFO, 'Sesión cerrada exitosamente');
        redirect(BASE_URL . '/auth/login');
    }
    
    /**
     * Autenticar usuario
     */
    private function authenticate($username, $password) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id_usuario, username, password_hash, nombre_completo, rol, estado 
                 FROM usuarios 
                 WHERE username = ? AND estado = ?"
            );
            
            $stmt->execute([$username, ESTADO_ACTIVO]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }
}
