<?php
/**
 * Configuración de la Base de Datos
 * Sistema de Gestión de Producción - Taller de Napa
 */

// =================================================================
// 🛠️ CONFIGURACIÓN (EDITA SOLO ESTA PARTE)
// =================================================================

// Pon TRUE para trabajar en tu PC (XAMPP)
// Pon FALSE para subir a Hostinger
$usar_local = false; 

if ($usar_local) {
    // 🏠 DATOS LOCALES (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sistema_napa');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // 🌐 DATOS HOSTINGER (Ya configurados con tus datos)
    define('DB_HOST', 'localhost'); 
    define('DB_NAME', 'u636306511_napa_db'); 
    define('DB_USER', 'u636306511_napa'); 
    define('DB_PASS', 'Nagato@852741'); 
}
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase de Conexión a la Base de Datos
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Constructor privado para Singleton
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Establecer zona horaria a Lima/Perú (-05:00) para las consultas
            $this->connection->exec("SET time_zone = '-05:00'");
            
        } catch(PDOException $e) {
            // Mensaje de error amigable
            die("<div style='font-family:sans-serif; padding:20px; border:1px solid red; background:#ffeeee; color:red;'>
                    <h3>❌ Error de Conexión a Base de Datos</h3>
                    <p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>
                    <p>Verifica que el usuario <code>" . DB_USER . "</code> tenga permisos sobre la base de datos.</p>
                 </div>");
        }
    }
    
    /**
     * Obtener instancia única de la base de datos (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevenir clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización del objeto
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
