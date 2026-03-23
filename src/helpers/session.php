<?php
/**
 * Helper de Sesiones
 * Sistema de Gestión de Producción - Taller de Napa
 */

/**
 * Verificar si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtener el ID del usuario actual
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtener el rol del usuario actual
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Obtener el nombre del usuario actual
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? '';
}

/**
 * Verificar si el usuario es administrador
 */
function isAdmin() {
    return getCurrentUserRole() === ROL_ADMINISTRADOR;
}

/**
 * Verificar si el usuario es supervisor
 */
function isSupervisor() {
    return getCurrentUserRole() === ROL_SUPERVISOR;
}

/**
 * Verificar si el usuario es trabajador
 */
function isTrabajador() {
    return getCurrentUserRole() === ROL_TRABAJADOR;
}

/**
 * Verificar si el usuario es operador
 */
function isOperador() {
    $role = getCurrentUserRole();
    return $role === ROL_OPERADOR || $role === ROL_TRABAJADOR;
}

/**
 * Verificar si el usuario es vendedor
 */
function isVendedor() {
    return getCurrentUserRole() === ROL_VENDEDOR;
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function hasRole($role) {
    return getCurrentUserRole() === $role;
}

/**
 * Verificar si el usuario tiene uno de varios roles
 */
function hasAnyRole($roles) {
    $currentRole = getCurrentUserRole();
    return in_array($currentRole, $roles);
}

/**
 * Requerir autenticación - redirige al login si no está logueado
 */
function requireAuth() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/auth/login');
    }
}

/**
 * Requerir rol específico
 */
function requireRole($role) {
    requireAuth();
    
    if (getCurrentUserRole() !== $role) {
        setFlashMessage(MSG_ERROR, 'No tiene permisos para acceder a esta sección');
        redirect(BASE_URL . '/dashboard');
    }
}

/**
 * Requerir uno de varios roles
 */
function requireAnyRole($roles) {
    requireAuth();
    
    if (!hasAnyRole($roles)) {
        setFlashMessage(MSG_ERROR, 'No tiene permisos para acceder a esta sección');
        redirect(BASE_URL . '/dashboard');
    }
}

/**
 * Verificar timeout de sesión
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        if (isset($_SESSION['last_activity'])) {
            $inactividad = time() - $_SESSION['last_activity'];
            
            if ($inactividad > SESSION_TIMEOUT) {
                // Sesión expirada
                session_destroy();
                session_start();
                setFlashMessage(MSG_WARNING, 'Su sesión ha expirado por inactividad');
                redirect(BASE_URL . '/auth/login');
            }
        }
        
        // Actualizar último tiempo de actividad
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Iniciar sesión de usuario
 */
function loginUser($userId, $username, $nombre, $rol) {
    // Regenerar ID de sesión para prevenir session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['user_name'] = $nombre;
    $_SESSION['user_role'] = $rol;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Registrar login en auditoría
    registrarAuditoria('usuarios', $userId, AUDITORIA_LOGIN, 'Usuario inició sesión');
}

/**
 * Cerrar sesión de usuario
 */
function logoutUser() {
    if (isLoggedIn()) {
        // Registrar logout en auditoría
        registrarAuditoria('usuarios', getCurrentUserId(), AUDITORIA_LOGOUT, 'Usuario cerró sesión');
    }
    
    // Destruir todas las variables de sesión
    $_SESSION = [];
    
    // Destruir la cookie de sesión
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Iniciar nueva sesión limpia
    session_start();
}

/**
 * Obtener tiempo de sesión activa (en minutos)
 */
function getSessionDuration() {
    if (isset($_SESSION['login_time'])) {
        return round((time() - $_SESSION['login_time']) / 60);
    }
    return 0;
}

/**
 * Obtener tiempo restante de sesión (en minutos)
 */
function getSessionTimeRemaining() {
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        $remaining = SESSION_TIMEOUT - $elapsed;
        return max(0, round($remaining / 60));
    }
    return 0;
}

/**
 * Proteger contra CSRF
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtener campo oculto con token CSRF
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
// Verificar timeout en cada solicitud
checkSessionTimeout();
