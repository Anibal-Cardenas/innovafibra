<?php
/**
 * Controlador Base
 * Sistema de Gestión de Producción - Taller de Napa
 */

abstract class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cargar vista
     */
    protected function view($view, $data = []) {
        // Extraer variables para la vista
        extract($data);
        
        // Incluir header
        require_once VIEWS_PATH . '/layout/header.php';
        
        // Incluir vista específica
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: {$view}");
        }
        
        // Incluir footer
        require_once VIEWS_PATH . '/layout/footer.php';
    }
    
    /**
     * Cargar vista sin layout (header/footer)
     * Útil para reportes PDF, impresiones, o respuestas parciales HTML
     */
    protected function viewRaw($view, $data = []) {
        // Extraer variables para la vista
        extract($data);
        
        // Incluir vista específica
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: {$view}");
        }
    }
    
    /**
     * Retornar JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Verificar si es petición POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Verificar si es petición AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Obtener datos POST
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Obtener datos GET
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Verificar autenticación
     */
    protected function checkAuth() {
        requireAuth();
    }
    
    /**
     * Verificar rol
     */
    protected function checkRole($role) {
        requireRole($role);
    }
}
