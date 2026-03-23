<?php
/**
 * Configuración General de la Aplicación
 * Sistema de Gestión de Producción - Taller de Napa
 */

// Información de la aplicación
define('APP_NAME', 'INNOVA FIBRA');
define('APP_VERSION', '1.0.0');

// --- DETECCIÓN AUTOMÁTICA DE ENTORNO Y URL (NO TOCAR) ---

// 1. Detectar si es Desarrollo (Local) o Producción
$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['HTTP_HOST'] === 'localhost';
// define('APP_ENV', $isLocal ? 'development' : 'production');
define('APP_ENV', 'development'); // ⚠️ MODO DEBUG ACTIVADO: Para ver el error real

// 2. Detectar URL base automáticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); // Ej: /Napa/public o /
$scriptDir = str_replace('\\', '/', $scriptDir); // Normalizar slashes para Windows
$scriptDir = rtrim($scriptDir, '/'); // Quitar slash final si existe

define('BASE_URL', $protocol . $host . $scriptDir);
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', BASE_URL . '/css');
define('JS_URL', BASE_URL . '/js');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');
define('UPLOADS_PATH', ROOT_PATH . '/public/uploads');

// Zona horaria
date_default_timezone_set('America/Lima');

// Configuración de sesión
// Intentar aplicar configuraciones de sesión solo si la sesión no está activa
if (function_exists('session_status') && session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si se usa HTTPS
    // Configuración para sesión "infinita" (1 año)
    ini_set('session.gc_maxlifetime', 31536000);
    ini_set('session.cookie_lifetime', 31536000);
} else {
    // Si la sesión ya está activa, omitimos cambiar las directivas para evitar warnings
}

// Tiempo de expiración de sesión (Desactivado - 1 año)
define('SESSION_TIMEOUT', 31536000);

// Configuración de errores
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// Configuración de uploads
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf']);

// Formato de fechas
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('TIME_FORMAT', 'H:i');

// Paginación
define('ITEMS_PER_PAGE', 20);

// Configuración de email (para futuro)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', '');
define('SMTP_FROM_NAME', APP_NAME);
