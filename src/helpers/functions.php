<?php
/**
 * Funciones Helper Generales
 * Sistema de Gestión de Producción - Taller de Napa
 */

/**
 * Escapar output HTML
 */
function h($string) {
    if ($string === null) return '';
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirigir a una URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formatear fecha y hora
 */
function formatDateTime($datetime, $format = DATETIME_FORMAT) {
    if (empty($datetime)) return '';
    $timestamp = strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Formatear número decimal
 */
function formatDecimal($number, $decimals = 2) {
    if ($number === null || $number === '') {
        $number = 0;
    }
    return number_format((float)$number, $decimals, '.', ',');
}

/**
 * Formatear moneda
 */
function formatCurrency($amount, $decimals = 2) {
    if ($amount === null || $amount === '') {
        $amount = 0;
    }
    return MONEDA_SIMBOLO . ' ' . number_format((float)$amount, $decimals, '.', ',');
}

/**
 * Formatear entero (sin decimales), segura contra null
 */
function formatInteger($number) {
    if ($number === null || $number === '') {
        $number = 0;
    }
    return number_format((int)$number);
}

/**
 * Generar código de lote
 */
function generarCodigoLote() {
    $db = Database::getInstance()->getConnection();
    $year = date('Y');
    $month = date('m');
    
    $query = "SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_lote, 12, 4) AS UNSIGNED)), 0) + 1 AS secuencia
              FROM lotes_fibra
              WHERE codigo_lote LIKE CONCAT(:prefix, '-', :year, '-', :month, '-%')";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':prefix' => PREFIX_LOTE,
        ':year' => $year,
        ':month' => $month
    ]);
    
    $result = $stmt->fetch();
    $secuencia = str_pad($result['secuencia'], 4, '0', STR_PAD_LEFT);
    
    return PREFIX_LOTE . '-' . $year . '-' . $month . '-' . $secuencia;
}

/**
 * Generar código de guía
 */
function generarCodigoGuia() {
    $db = Database::getInstance()->getConnection();
    $year = date('Y');
    
    $query = "SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_guia, 11, 4) AS UNSIGNED)), 0) + 1 AS secuencia
              FROM entregas
              WHERE codigo_guia LIKE CONCAT(:prefix, '-', :year, '-%')";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':prefix' => PREFIX_GUIA,
        ':year' => $year
    ]);
    
    $result = $stmt->fetch();
    $secuencia = str_pad($result['secuencia'], 4, '0', STR_PAD_LEFT);
    
    return PREFIX_GUIA . '-' . $year . '-' . $secuencia;
}

/**
 * Obtener valor de configuración
 */
function getConfigValue($parametro, $default = null) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT valor FROM configuracion_sistema WHERE parametro = ?");
        $stmt->execute([$parametro]);
        $result = $stmt->fetch();
        
        return $result ? $result['valor'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Establecer mensaje flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtener y limpiar mensaje flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Sanitizar input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Debug (solo en desarrollo)
 */
function dd($data) {
    if (APP_ENV === 'development') {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}

/**
 * Calcular eficiencia
 */
function calcularEficiencia($produccionReal, $cantidadEstimada) {
    if ($cantidadEstimada == 0) return 0;
    return round(($produccionReal / $cantidadEstimada) * 100, 2);
}

/**
 * Detectar merma excesiva
 */
function detectarMermaExcesiva($produccionReal, $cantidadEstimada, $tolerancia = null) {
    if ($tolerancia === null) {
        $tolerancia = (float)getConfigValue('tolerancia_merma', DEFAULT_TOLERANCIA_MERMA);
    }
    
    $umbral = $cantidadEstimada * (1 - $tolerancia / 100);
    return $produccionReal < $umbral;
}

/**
 * Calcular peso de bolsas plásticas consumido
 */
function calcularPesoBolsas($cantidad, $factorConversion = null) {
    if ($factorConversion === null) {
        $factorConversion = (float)getConfigValue('factor_conversion_bolsas', DEFAULT_FACTOR_CONVERSION);
    }
    
    return round($cantidad * $factorConversion, 2);
}

/**
 * Obtener estado de alerta de inventario
 */
function getEstadoAlertaInventario($cantidad, $stockMinimo) {
    if ($cantidad < $stockMinimo * 0.5) {
        return ALERTA_CRITICO;
    } elseif ($cantidad < $stockMinimo) {
        return ALERTA_BAJO;
    }
    return ALERTA_NORMAL;
}

/**
 * Obtener clase CSS según estado de alerta
 */
function getAlertaClass($estado) {
    switch ($estado) {
        case ALERTA_CRITICO:
            return 'alert-danger';
        case ALERTA_BAJO:
            return 'alert-warning';
        case ALERTA_NORMAL:
            return 'alert-success';
        default:
            return 'alert-info';
    }
}

/**
 * Registrar en auditoría
 */
function registrarAuditoria($tabla, $idRegistro, $accion, $descripcion = null, $datosAnteriores = null, $datosNuevos = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $query = "INSERT INTO auditoria 
                  (id_usuario, tabla_afectada, id_registro, accion, descripcion, datos_anteriores, datos_nuevos, ip_address) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $tabla,
            $idRegistro,
            $accion,
            $descripcion,
            $datosAnteriores ? json_encode($datosAnteriores) : null,
            $datosNuevos ? json_encode($datosNuevos) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error en auditoría: " . $e->getMessage());
        return false;
    }
}

/**
 * Generar SELECT de opciones HTML
 */
function generateSelectOptions($data, $valueField, $textField, $selectedValue = null) {
    $options = '<option value="">Seleccione...</option>';
    
    foreach ($data as $item) {
        $value = $item[$valueField];
        $text = $item[$textField];
        $selected = ($value == $selectedValue) ? 'selected' : '';
        
        $options .= "<option value=\"{$value}\" {$selected}>{$text}</option>";
    }
    
    return $options;
}

/**
 * Obtener nombre del mes en español
 */
function getNombreMes($mes) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    
    return $meses[(int)$mes] ?? '';
}

/**
 * Truncar texto
 */
function truncate($text, $length = 50, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}
