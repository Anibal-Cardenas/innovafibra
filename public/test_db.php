<?php
/**
 * Script de prueba de conexión a Base de Datos
 * Sube este archivo a tu hosting y entra a: tudominio.com/test_db.php
 */

// Ajustar ruta para incluir la configuración
require_once '../config/database.php';

echo "<h1>Diagnóstico de Conexión</h1>";
echo "<p>Entorno detectado: <strong>" . ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' ? 'LOCAL (XAMPP)' : 'PRODUCCIÓN (HOSTINGER)') . "</strong></p>";
echo "<p>Host DB: <strong>" . DB_HOST . "</strong></p>";
echo "<p>Nombre DB: <strong>" . DB_NAME . "</strong></p>";
echo "<p>Usuario DB: <strong>" . DB_USER . "</strong></p>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "<h2 style='color: green;'>✅ ¡CONEXIÓN EXITOSA!</h2>";
    echo "<p>La base de datos responde correctamente. Si el sistema no carga, revisa el archivo .htaccess o las rutas.</p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ ERROR DE CONEXIÓN</h2>";
    echo "<p><strong>Mensaje del servidor:</strong> " . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h3>Posibles soluciones en Hostinger:</h3>";
    echo "<ul>";
    echo "<li>Verifica que el usuario <strong>" . DB_USER . "</strong> esté asignado a la base de datos <strong>" . DB_NAME . "</strong>.</li>";
    echo "<li>Verifica que la contraseña no tenga espacios al inicio o final.</li>";
    echo "<li>Asegúrate de haber importado el archivo SQL.</li>";
    echo "</ul>";
}