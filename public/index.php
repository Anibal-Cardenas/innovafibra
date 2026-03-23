<?php
/**
 * Punto de Entrada de la Aplicación
 * Sistema de Gestión de Producción - Taller de Napa
 */

// Cargar configuraciones
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Cargar helpers
require_once APP_PATH . '/helpers/functions.php';
require_once APP_PATH . '/helpers/session.php';
require_once APP_PATH . '/helpers/validation.php';
// Ejecutar migraciones automáticas (si es necesario)
require_once APP_PATH . '/helpers/migration_runner.php';

// Iniciar sesión (después de cargar configuraciones para aplicar ini_set)
session_start();

// Autoloader simple (puede reemplazarse con Composer en el futuro)
spl_autoload_register(function ($class) {
    $directories = [
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
        APP_PATH . '/services/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Obtener la ruta solicitada
$route = isset($_GET['route']) ? $_GET['route'] : '';

// Router simple
try {
    // Si no hay ruta, verificar si hay sesión activa
    if (empty($route)) {
        if (isLoggedIn()) {
            // Redirigir al dashboard
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        } else {
            // Mostrar login
            $route = 'auth/login';
        }
    }
    
    // Parsear la ruta
    $parts = explode('/', trim($route, '/'));
    $controllerSegment = !empty($parts[0]) ? $parts[0] : '';
    // Convertir nombres con guiones o underscores a PascalCase, p.ej. "calidades-fibra" -> "CalidadesFibraController"
    if (empty($controllerSegment)) {
        $controller = 'DashboardController';
    } else {
        $controller = str_replace(' ', '', ucwords(str_replace(['-','_'], ' ', $controllerSegment))) . 'Controller';
    }
    $rawAction = !empty($parts[1]) ? $parts[1] : 'index';
    // Normalizar acción: probar raw, variante con guiones->guion_bajo y guiones->camelCase
    $actionCandidates = [$rawAction];
    $actionCandidates[] = str_replace('-', '_', $rawAction);
    $camel = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $rawAction))));
    if ($camel !== $rawAction) {
        $actionCandidates[] = $camel;
    }
    
    // Verificar si el controlador existe
    $controllerFile = APP_PATH . '/controllers/' . $controller . '.php';
    
    if (!file_exists($controllerFile)) {
        throw new Exception("Controlador no encontrado: $controller");
    }
    
    // Incluir explícitamente el archivo del controlador para asegurar su carga
    // (Esto hace más explícito cualquier error de parseo/declared class en entornos donde el autoloader falle)
    require_once $controllerFile;

    // Instanciar el controlador
    $controllerInstance = new $controller();
    
    // Buscar la primera acción válida entre los candidatos
    $action = null;
    foreach ($actionCandidates as $candidate) {
        if (method_exists($controllerInstance, $candidate)) {
            $action = $candidate;
            break;
        }
    }

    if ($action === null) {
        throw new Exception("Acción no encontrada: $rawAction");
    }

    // Obtener parámetros adicionales de la URL y ejecutar la acción
    $params = array_slice($parts, 2);
    call_user_func_array([$controllerInstance, $action], $params);
    
} catch (Exception $e) {
    // Manejo de errores
    if (APP_ENV === 'development') {
        echo "<h1>Error</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        // En producción, mostrar página de error genérica
        http_response_code(500);
        include VIEWS_PATH . '/errors/500.php';
    }
}
