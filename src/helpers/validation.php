<?php
/**
 * Helper de Validaciones
 * Sistema de Gestión de Producción - Taller de Napa
 */

/**
 * Clase para manejar validaciones
 */
class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Validar campo requerido
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field][] = $message ?? "El campo {$field} es requerido";
        }
        return $this;
    }
    
    /**
     * Validar email
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "El campo {$field} debe ser un email válido";
        }
        return $this;
    }
    
    /**
     * Validar longitud mínima
     */
    public function minLength($field, $min, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = $message ?? "El campo {$field} debe tener al menos {$min} caracteres";
        }
        return $this;
    }
    
    /**
     * Validar longitud máxima
     */
    public function maxLength($field, $max, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = $message ?? "El campo {$field} no debe exceder {$max} caracteres";
        }
        return $this;
    }
    
    /**
     * Validar número
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?? "El campo {$field} debe ser numérico";
        }
        return $this;
    }
    
    /**
     * Validar entero
     */
    public function integer($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = $message ?? "El campo {$field} debe ser un número entero";
        }
        return $this;
    }
    
    /**
     * Validar decimal
     */
    public function decimal($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_FLOAT)) {
            $this->errors[$field][] = $message ?? "El campo {$field} debe ser un número decimal";
        }
        return $this;
    }
    
    /**
     * Validar valor mínimo
     */
    public function min($field, $min, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field][] = $message ?? "El campo {$field} debe ser al menos {$min}";
        }
        return $this;
    }
    
    /**
     * Validar valor máximo
     */
    public function max($field, $max, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] > $max) {
            $this->errors[$field][] = $message ?? "El campo {$field} no debe exceder {$max}";
        }
        return $this;
    }
    
    /**
     * Validar fecha
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (isset($this->data[$field])) {
            $d = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[$field][] = $message ?? "El campo {$field} no es una fecha válida";
            }
        }
        return $this;
    }
    
    /**
     * Validar que un valor esté en una lista
     */
    public function in($field, $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field][] = $message ?? "El campo {$field} contiene un valor no válido";
        }
        return $this;
    }
    
    /**
     * Validar formato de fecha dd/mm/yyyy
     */
    public function dateDMY($field, $message = null) {
        if (isset($this->data[$field])) {
            $pattern = '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/\d{4}$/';
            if (!preg_match($pattern, $this->data[$field])) {
                $this->errors[$field][] = $message ?? "El campo {$field} debe tener formato dd/mm/yyyy";
            }
        }
        return $this;
    }
    
    /**
     * Validación personalizada
     */
    public function custom($field, callable $callback, $message = null) {
        if (isset($this->data[$field])) {
            $result = $callback($this->data[$field]);
            if (!$result) {
                $this->errors[$field][] = $message ?? "El campo {$field} no es válido";
            }
        }
        return $this;
    }
    
    /**
     * Verificar si hay errores
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Verificar si la validación pasó
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Obtener todos los errores
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Obtener errores de un campo específico
     */
    public function getError($field) {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Obtener el primer error de un campo
     */
    public function getFirstError($field) {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Limpiar errores
     */
    public function clearErrors() {
        $this->errors = [];
        return $this;
    }
}

/**
 * Validar peso neto vs peso bruto
 */
function validarPesos($pesoNeto, $pesoBruto) {
    return $pesoNeto <= $pesoBruto;
}

/**
 * Validar stock suficiente
 */
function validarStockSuficiente($tipoItem, $cantidadRequerida) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT cantidad FROM inventario WHERE tipo_item = ?");
        $stmt->execute([$tipoItem]);
        $result = $stmt->fetch();
        
        if (!$result) return false;
        
        return $result['cantidad'] >= $cantidadRequerida;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Validar usuario único
 */
function validarUsernameUnico($username, $excludeId = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE username = ?";
        $params = [$username];
        
        if ($excludeId !== null) {
            $query .= " AND id_usuario != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] == 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Sanitizar datos para inserción
 */
function sanitizeData($data) {
    if (is_array($data)) {
        return array_map('sanitizeData', $data);
    }
    
    return trim(strip_tags($data));
}

/**
 * Validar rango de fechas
 */
function validarRangoFechas($fechaInicio, $fechaFin) {
    return strtotime($fechaInicio) <= strtotime($fechaFin);
}

/**
 * Validar formato de RUC (Perú)
 */
function validarRUC($ruc) {
    // RUC peruano: 11 dígitos
    return preg_match('/^\d{11}$/', $ruc);
}

/**
 * Validar formato de DNI (Perú)
 */
function validarDNI($dni) {
    // DNI peruano: 8 dígitos
    return preg_match('/^\d{8}$/', $dni);
}
