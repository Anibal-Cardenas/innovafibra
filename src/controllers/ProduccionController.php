<?php
/**
 * Controlador de Producción
 * Sistema de Gestión de Producción - Taller de Napa
 */

class ProduccionController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
    }
    
    /**
     * Lista de producciones
     */
    public function index() {
        $this->checkRole(ROL_ADMINISTRADOR);
        
        $data = [
            'title' => 'Producciones',
            'producciones' => $this->getProducciones()
        ];
        
        $this->view('produccion/lista', $data);
    }
    
    /**
     * Registrar nueva producción
     */
    public function nueva() {
        // Permitir a administradores, supervisores y operadores
        requireAnyRole([ROL_ADMINISTRADOR, ROL_SUPERVISOR, ROL_OPERADOR, ROL_TRABAJADOR]);
        
        $data = [
            'title' => 'Registrar Producción',
            'cubos' => $this->getCubosDisponibles(),
            'operadores' => $this->getOperadores(),
            'calidades_producto' => $this->getCalidadesProducto(),
            'factor_conversion' => getConfigValue('factor_conversion_bolsas', DEFAULT_FACTOR_CONVERSION)
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarProduccion();
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                
                if (isTrabajador()) {
                    redirect(BASE_URL . '/produccion/misproducciones');
                } elseif (isOperador()) {
                    redirect(BASE_URL . '/produccion/misproducciones');
                } else {
                    redirect(BASE_URL . '/produccion');
                }
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('produccion/nueva', $data);
    }

    /**
     * Obtener lista de operadores disponibles
     */
    private function getOperadores() {
        $stmt = $this->db->query(
            "SELECT id_usuario, nombre_completo 
             FROM usuarios 
             WHERE rol IN ('operador', 'trabajador') AND estado = 'activo' 
             ORDER BY nombre_completo ASC"
        );
        return $stmt->fetchAll();
    }
    
    /**
     * Validar producción (supervisor/admin)
     */
    public function validar() {
        requireAnyRole([ROL_ADMINISTRADOR, ROL_SUPERVISOR]);
        
        $data = [
            'title' => 'Validar Producción',
            'producciones_pendientes' => $this->getProduccionesPendientes()
        ];
        
        $this->view('produccion/validar', $data);
    }
    
    /**
     * Procesar validación (AJAX)
     */
    public function procesarValidacion() {
        requireAnyRole([ROL_ADMINISTRADOR, ROL_SUPERVISOR]);
        
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idProduccion = $this->post('id_produccion');
        $decision = $this->post('decision'); // 'aprobado' o 'rechazado'
        $observaciones = $this->post('observaciones');
        
        // Validar
        if (empty($idProduccion) || empty($decision)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        if (!in_array($decision, [VALIDACION_APROBADO, VALIDACION_RECHAZADO])) {
            $this->json(['success' => false, 'message' => 'Decisión inválida'], 400);
        }
        
        if ($decision === VALIDACION_RECHAZADO && empty($observaciones)) {
            $this->json(['success' => false, 'message' => 'Debe ingresar el motivo del rechazo'], 400);
        }
        
        try {
            $this->ejecutarValidacion($idProduccion, $decision, $observaciones);
            
            $mensaje = $decision === VALIDACION_APROBADO 
                ? 'Producción aprobada exitosamente' 
                : 'Producción rechazada';
            
            $this->json(['success' => true, 'message' => $mensaje]);
            
        } catch (PDOException $e) {
            // DETECCIÓN DE ERROR 1172 (Trigger duplicados)
            // Si ocurre este error, forzamos la reparación de triggers y reintentamos
            if ($e->getCode() == '42000' && strpos($e->getMessage(), '1172') !== false) {
                try {
                    $this->repararTriggers();
                    $this->ejecutarValidacion($idProduccion, $decision, $observaciones);
                    
                    $mensaje = $decision === VALIDACION_APROBADO 
                        ? 'Producción aprobada exitosamente (Recuperado)' 
                        : 'Producción rechazada';
                    
                    $this->json(['success' => true, 'message' => $mensaje]);
                    return;
                } catch (Exception $ex) {
                    error_log("Error al reintentar validación: " . $ex->getMessage());
                }
            }
            
            error_log("Error al validar producción: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error al procesar la validación: ' . $e->getMessage()], 200);
        }
    }

    private function ejecutarValidacion($idProduccion, $decision, $observaciones) {
        $this->db->beginTransaction();
        try {
            
            // Obtener datos de la producción antes de actualizar
            $stmt = $this->db->prepare(
                "SELECT id_cubo, id_lote_fibra, cantidad_producida, id_comision 
                 FROM producciones 
                 WHERE id_produccion = ?"
            );
            $stmt->execute([$idProduccion]);
            $produccion = $stmt->fetch();

            if (!$produccion) {
                throw new Exception("Producción no encontrada");
            }

            // Validar si pertenece a una comisión y procesar retiro AUTOMÁTICO
            if ($decision === VALIDACION_RECHAZADO && !empty($produccion['id_comision'])) {
                $idComision = $produccion['id_comision'];
                
                // Verificar estado de la comisión
                $stmtCom = $this->db->prepare("SELECT estado FROM comisiones WHERE id_comision = ?");
                $stmtCom->execute([$idComision]);
                $estadoComision = $stmtCom->fetchColumn();
                
                if ($estadoComision === 'pagado') {
                    throw new Exception("No se puede rechazar automáticamente: La comisión asociada ya fue PAGADA. Debe gestionar el reintegro manualmente.");
                }
                
                // Obtener datos del detalle para restar montos exactos
                $stmtDetalle = $this->db->prepare(
                    "SELECT subtotal, cantidad_bolsas FROM comisiones_detalle 
                     WHERE id_comision = ? AND id_produccion = ?"
                );
                $stmtDetalle->execute([$idComision, $idProduccion]);
                $detalle = $stmtDetalle->fetch();
                
                if ($detalle) {
                    $montoRestar = $detalle['subtotal'];
                    $cantidadRestar = $detalle['cantidad_bolsas'];
                    
                    // 1. Actualizar cabecera de comisión (Restar totales)
                    $stmtUpdateCom = $this->db->prepare(
                        "UPDATE comisiones 
                         SET total_bolsas_producidas = GREATEST(0, total_bolsas_producidas - ?),
                             monto_comision = GREATEST(0, monto_comision - ?),
                             monto_total = GREATEST(0, monto_total - ?)
                         WHERE id_comision = ?"
                    );
                    $stmtUpdateCom->execute([$cantidadRestar, $montoRestar, $montoRestar, $idComision]);
                    
                    // 2. Eliminar el detalle
                    $stmtDelDetalle = $this->db->prepare(
                        "DELETE FROM comisiones_detalle WHERE id_comision = ? AND id_produccion = ?"
                    );
                    $stmtDelDetalle->execute([$idComision, $idProduccion]);
                    
                    // 3. Registrar auditoría de la modificación automática
                    registrarAuditoria(
                        'comisiones',
                        $idComision,
                        AUDITORIA_UPDATE,
                        "Ajuste automático por rechazo de producción #{$idProduccion} (-S/ {$montoRestar})"
                    );
                }
            }
            
            // Actualizar estado de validación
            // Intentamos actualizar asumiendo que existe la columna observaciones_validacion
            try {
                $stmt = $this->db->prepare(
                    "UPDATE producciones 
                     SET estado_validacion = ?,
                         id_supervisor = ?,
                         fecha_validacion = NOW(),
                         observaciones_validacion = ?
                     WHERE id_produccion = ?"
                );
                
                $stmt->execute([
                    $decision,
                    getCurrentUserId(),
                    $observaciones,
                    $idProduccion
                ]);
            } catch (PDOException $e) {
                // Si falla por columna no encontrada (42S22), intentamos sin esa columna
                // y agregamos la observación al campo general si es necesario
                if ($e->getCode() == '42S22') {
                    $nota = $observaciones ? " [Validación {$decision}: {$observaciones}]" : "";
                    
                    $stmt = $this->db->prepare(
                        "UPDATE producciones 
                         SET estado_validacion = ?,
                             id_supervisor = ?,
                             fecha_validacion = NOW(),
                             observaciones = CONCAT(IFNULL(observaciones, ''), ?)
                         WHERE id_produccion = ?"
                    );
                    
                    $stmt->execute([
                        $decision,
                        getCurrentUserId(),
                        $nota,
                        $idProduccion
                    ]);
                } else {
                    throw $e; // Re-lanzar si es otro error
                }
            }
            
            // Si se rechaza, revertir cambios en Lote y Cubo
            if ($decision === VALIDACION_RECHAZADO && $produccion) {
                $cant = $produccion['cantidad_producida'];
                $idLote = $produccion['id_lote_fibra'];
                $idCubo = $produccion['id_cubo'];

                // 1. Revertir LOTE (Restar cantidad y verificar estado)
                if ($idLote) {
                    $stmtLote = $this->db->prepare(
                        "UPDATE lotes_fibra 
                         SET cantidad_producida_real = GREATEST(0, cantidad_producida_real - ?),
                             estado = IF(cantidad_producida_real < cantidad_estimada_bolsas, 'en_proceso', estado)
                         WHERE id_lote = ?"
                    );
                    $stmtLote->execute([$cant, $idLote]);
                }

                // 2. Revertir CUBO
                if ($idCubo) {
                    // Restamos la cantidad
                    $stmtCuboUpdate = $this->db->prepare(
                        "UPDATE cubos_fibra 
                         SET cantidad_producida_real = GREATEST(0, cantidad_producida_real - ?)
                         WHERE id_cubo = ?"
                    );
                    $stmtCuboUpdate->execute([$cant, $idCubo]);
                    
                    // Verificamos si quedó en 0 para liberarlo
                    $stmtCheckCubo = $this->db->prepare("SELECT cantidad_producida_real FROM cubos_fibra WHERE id_cubo = ?");
                    $stmtCheckCubo->execute([$idCubo]);
                    $cuboState = $stmtCheckCubo->fetch();
                    
                    if ($cuboState && $cuboState['cantidad_producida_real'] <= 0) {
                        // Volver a disponible
                        $stmtReset = $this->db->prepare(
                            "UPDATE cubos_fibra 
                             SET estado = 'disponible', 
                                 fecha_uso = NULL,
                                 cantidad_producida_real = 0 
                             WHERE id_cubo = ?"
                        );
                        $stmtReset->execute([$idCubo]);
                        
                        registrarAuditoria('cubos_fibra', $idCubo, AUDITORIA_UPDATE, "Cubo revertido a disponible (Rechazo Prod #{$idProduccion})");
                    }
                }
                
                // Registrar en auditoría la reversión general
                 registrarAuditoria(
                    'producciones',
                    $idProduccion,
                    AUDITORIA_UPDATE,
                    "Producción RECHAZADA y revertida (Cant: {$cant}) por " . getCurrentUserName()
                );
            } else {
                 // Auditoría normal (Aprobado)
                 registrarAuditoria(
                    'producciones',
                    $idProduccion,
                    AUDITORIA_UPDATE,
                    "Producción {$decision} por " . getCurrentUserName()
                );
            }
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function repararTriggers() {
        // Usar la función global de migración para asegurar consistencia y limpieza profunda
        // Aseguramos que el archivo esté incluido para poder usar la función
        if (!function_exists('reinstall_produccion_triggers')) {
            require_once APP_PATH . '/helpers/migration_runner.php';
        }

        if (function_exists('reinstall_produccion_triggers')) {
            reinstall_produccion_triggers();
        } else {
            // Fallback por si el helper no está cargado (aunque debería estarlo por index.php)
            $this->db->exec("DROP TRIGGER IF EXISTS trg_produccion_aprobada_inventario");
            $this->db->exec("DROP TRIGGER IF EXISTS trg_produccion_validar_stock");
            $this->db->exec("DROP TRIGGER IF EXISTS trg_produccion_check_stock");
        }
    }
    
    /**
     * Ver mis producciones (trabajador)
     */
    public function misproducciones() {
        // Permitir a operadores y trabajadores ver sus propias producciones
        requireAnyRole([ROL_OPERADOR, ROL_TRABAJADOR]);
        $data = [
            'title' => 'Mis Producciones',
            'producciones' => $this->getMisProducciones()
        ];
        
        $this->view('produccion/misproducciones', $data);
    }
    
    /**
     * Procesar nueva producción
     */
    private function procesarProduccion() {
        // Validar CSRF
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $fechaProduccion = $this->post('fecha_produccion');
        $idCubo = $this->post('id_cubo');
        $observaciones = $this->post('observaciones');
        
        // Obtener arrays de operadores y cantidades
        $operadores = $this->post('operadores'); // Array de IDs
        $cantidades = $this->post('cantidades'); // Array de cantidades
        
        // Si el usuario es operador/trabajador, forzamos su ID si no envió lista
        if ((isTrabajador() || isOperador()) && empty($operadores)) {
             $operadores = [getCurrentUserId()];
             $cantidades = [$this->post('cantidad_producida')];
        }

        if (empty($operadores) || empty($cantidades) || count($operadores) !== count($cantidades)) {
            return ['success' => false, 'message' => 'Debe asignar al menos un operador con su cantidad producida'];
        }

        // Calcular total producido
        $cantidadTotalProducida = 0;
        foreach ($cantidades as $cant) {
            $cantidadTotalProducida += (int)$cant;
        }

        // Validar
        $validator = new Validator($_POST);
        $validator
            ->required('fecha_produccion')
            ->required('id_cubo');
        
        if ($cantidadTotalProducida <= 0) {
            return ['success' => false, 'message' => 'La cantidad total producida debe ser mayor a 0'];
        }
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Por favor complete todos los campos correctamente'];
        }
        
        try {
            // Obtener información del cubo
            $stmt = $this->db->prepare(
                "SELECT c.cantidad_estimada_bolsas, c.estado, c.id_lote,
                       c.cantidad_producida_real, c.peso_neto
                 FROM cubos_fibra c
                 WHERE c.id_cubo = ?"
            );
            $stmt->execute([$idCubo]);
            $cubo = $stmt->fetch();
            
            if (!$cubo) {
                return ['success' => false, 'message' => 'Fardo no encontrado'];
            }
            
            if (!in_array($cubo['estado'], ['disponible', 'en_uso'])) {
                return ['success' => false, 'message' => 'El fardo seleccionado no está disponible'];
            }
            
            // Calcular peso de bolsas consumido TOTAL
            $factorConversion = (float)getConfigValue('factor_conversion_bolsas', DEFAULT_FACTOR_CONVERSION);
            
            // Permitir ingreso manual del peso de bolsas si se proporciona en el formulario
            if ($this->post('peso_bolsas_consumido') && is_numeric($this->post('peso_bolsas_consumido'))) {
                $pesoBolsasTotal = (float)$this->post('peso_bolsas_consumido');
            } else {
                $pesoBolsasTotal = calcularPesoBolsas($cantidadTotalProducida, $factorConversion);
            }
            
            // Validar stock de bolsas plásticas
            if (!validarStockSuficiente(INVENTARIO_BOLSAS, $pesoBolsasTotal)) {
                return [
                    'success' => false,
                    'message' => "Stock insuficiente de bolsas plásticas. Se requieren {$pesoBolsasTotal} kg"
                ];
            }
            
            // Calcular eficiencia basada en el cubo (Global para la sesión)
            $eficiencia = calcularEficiencia($cantidadTotalProducida, $cubo['cantidad_estimada_bolsas']);
            
            // Detectar merma excesiva
            $tolerancia = (float)getConfigValue('tolerancia_merma', DEFAULT_TOLERANCIA_MERMA);
            $flagMerma = detectarMermaExcesiva($cantidadTotalProducida, $cubo['cantidad_estimada_bolsas'], $tolerancia);
            
            // Determinar calidad del producto basada en calidad del insumo
            $idCalidadProducto = $this->determinarCalidadProducto($cubo['id_lote']);
            
            // Si hay merma excesiva, las observaciones son obligatorias
            if ($flagMerma && empty($observaciones)) {
                return [
                    'success' => false,
                    'message' => 'Debe ingresar observaciones cuando hay merma excesiva'
                ];
            }
            
            $this->db->beginTransaction();
            
            // Insertar producciones por cada operador
            $stmtInsert = $this->db->prepare(
                "INSERT INTO producciones 
                (fecha_produccion, id_lote_fibra, id_cubo, id_operario, id_calidad_napa, cantidad_producida, 
                 peso_bolsas_consumido, eficiencia_porcentual, flag_merma_excesiva,
                 observaciones, usuario_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            foreach ($operadores as $index => $idOperario) {
                $cant = (int)$cantidades[$index];
                if ($cant <= 0) continue;

                // Distribuir peso de bolsas proporcionalmente
                $pesoBolsasProporcional = ($cant / $cantidadTotalProducida) * $pesoBolsasTotal;

                $stmtInsert->execute([
                    $fechaProduccion,
                    $cubo['id_lote'],
                    $idCubo,
                    $idOperario,
                    $idCalidadProducto,
                    $cant,
                    $pesoBolsasProporcional,
                    $eficiencia, // Eficiencia del lote completo se registra en cada uno
                    $flagMerma ? 1 : 0,
                    $observaciones, // Observaciones generales se copian
                    getCurrentUserId()
                ]);

                // Auditoría individual
                $idProduccion = $this->db->lastInsertId();
                registrarAuditoria(
                    'producciones',
                    $idProduccion,
                    AUDITORIA_INSERT,
                    "Producción registrada: {$cant} bolsas por operador ID {$idOperario}"
                );
            }
            
            // --- ACTUALIZACIÓN DE INVENTARIO DE FIBRA (UNA SOLA VEZ POR EL TOTAL) ---
            
            // 1. Marcar el cubo como AGOTADO y registrar lo que produjo (Total)
            $stmtCubo = $this->db->prepare(
                "UPDATE cubos_fibra 
                 SET estado = 'agotado', 
                     cantidad_producida_real = ?,
                     fecha_uso = NOW()
                 WHERE id_cubo = ?"
            );
            $stmtCubo->execute([$cantidadTotalProducida, $idCubo]);

            // 2. Actualizar el acumulado del Lote Padre
            $stmtLote = $this->db->prepare(
                "UPDATE lotes_fibra 
                 SET cantidad_producida_real = cantidad_producida_real + ?,
                     estado = IF(cantidad_producida_real >= cantidad_estimada_bolsas, 'agotado', 'en_proceso')
                 WHERE id_lote = ?"
            );
            $stmtLote->execute([$cantidadTotalProducida, $cubo['id_lote']]);

            $this->db->commit();
            
            $mensaje = "Producción registrada exitosamente ({$cantidadTotalProducida} bolsas). Pendiente de validación.";
            if ($flagMerma) {
                $mensaje .= " ⚠️ ALERTA: Se detectó merma excesiva (Eficiencia: {$eficiencia}%)";
            }
            
            return ['success' => true, 'message' => $mensaje];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al registrar producción: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar la producción: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener cubos disponibles para producción
     */
    private function getCubosDisponibles() {
        $stmt = $this->db->query(
            "SELECT 
                c.id_cubo,
                c.numero_cubo,
                l.codigo_lote,
                c.peso_neto,
                c.cantidad_estimada_bolsas,
                c.cantidad_producida_real,
                GREATEST(0, CAST(c.cantidad_estimada_bolsas AS SIGNED) - CAST(c.cantidad_producida_real AS SIGNED)) as pendiente,
                CONCAT(l.codigo_lote, ' - Fardo ', c.numero_cubo, ' (', c.peso_neto, ' kg) - ', COALESCE(cf.nombre, 'Sin Calidad')) as descripcion,
                cf.nombre as calidad_fibra,
                cn.nombre as calidad_napa_producira,
                cn.codigo as codigo_napa
             FROM cubos_fibra c
             INNER JOIN lotes_fibra l ON c.id_lote = l.id_lote
             LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
             LEFT JOIN calidades_napa cn ON cf.id_calidad_napa_destino = cn.id_calidad_napa
             WHERE c.estado IN ('disponible', 'en_uso')
             ORDER BY l.fecha_compra DESC, c.numero_cubo ASC"
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener todas las producciones
     */
    private function getProducciones() {
        $stmt = $this->db->query(
            "SELECT 
                     p.id_produccion,
                     p.fecha_produccion,
                     l.codigo_lote,
                     u.nombre_completo as operario,
                     p.cantidad_producida,
                p.eficiencia_porcentual,
                p.flag_merma_excesiva,
                p.estado_validacion,
                     s.nombre_completo as supervisor
                     , COALESCE(c.peso_neto, 0) as peso_fardo
             FROM producciones p
             INNER JOIN lotes_fibra l ON p.id_lote_fibra = l.id_lote
                 LEFT JOIN cubos_fibra c ON p.id_cubo = c.id_cubo
             INNER JOIN usuarios u ON p.id_operario = u.id_usuario
             LEFT JOIN usuarios s ON p.id_supervisor = s.id_usuario
             ORDER BY p.fecha_produccion DESC, p.id_produccion DESC"
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener producciones pendientes de validación
     */
    private function getProduccionesPendientes() {
        $stmt = $this->db->query(
            "SELECT 
                     p.id_produccion,
                     p.fecha_produccion,
                     l.codigo_lote,
                     l.cantidad_estimada_bolsas,
                     u.nombre_completo as operario,
                     p.cantidad_producida,
                p.eficiencia_porcentual,
                p.flag_merma_excesiva,
                p.observaciones,
                COALESCE(c.peso_neto, 0) as peso_fardo
             FROM producciones p
             INNER JOIN lotes_fibra l ON p.id_lote_fibra = l.id_lote
                 LEFT JOIN cubos_fibra c ON p.id_cubo = c.id_cubo
             INNER JOIN usuarios u ON p.id_operario = u.id_usuario
             WHERE p.estado_validacion = 'pendiente'
             ORDER BY p.fecha_produccion ASC"
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener producciones del usuario actual
     */
    private function getMisProducciones() {
        // Excluir montos de producciones que ya fueron liquidadas (comisión pagada).
        // Si una producción pertenece a una comisión pagada, no se debe mostrar como "a cobrar".
        $stmt = $this->db->prepare(
              "SELECT 
                     p.id_produccion,
                     p.fecha_produccion,
                     l.codigo_lote,
                     p.cantidad_producida,
                     p.eficiencia_porcentual,
                     p.flag_merma_excesiva,
                     p.estado_validacion,
                     u.tarifa_por_bolsa,
                     COALESCE(c2.peso_neto, 0) as peso_fardo,
                CASE 
                    WHEN p.estado_validacion = 'aprobado' 
                         AND (p.id_comision IS NULL OR (c.estado IS NOT NULL AND c.estado != 'pagado'))
                    THEN p.cantidad_producida * u.tarifa_por_bolsa
                    ELSE 0
                END as monto_pagar
             FROM producciones p
             INNER JOIN lotes_fibra l ON p.id_lote_fibra = l.id_lote
             INNER JOIN usuarios u ON p.id_operario = u.id_usuario
                 LEFT JOIN comisiones c ON p.id_comision = c.id_comision
                 LEFT JOIN cubos_fibra c2 ON p.id_cubo = c2.id_cubo
            
             WHERE p.id_operario = ?
             ORDER BY p.fecha_produccion DESC"
        );
        
        $stmt->execute([getCurrentUserId()]);
        $rows = $stmt->fetchAll();
        // Map peso_fardo from c2.peso_neto if present
        foreach ($rows as &$r) {
            if (!isset($r['peso_fardo'])) {
                // try to map from c2 if driver returned it with alias
                $r['peso_fardo'] = isset($r['peso_neto']) ? $r['peso_neto'] : 0;
            }
        }
        return $rows;
    }
    
    /**
     * Determinar calidad del producto (napa) basada en la calidad de FIBRA del lote
     */
    private function determinarCalidadProducto($idLote) {
        // Obtener la calidad de la fibra del lote
        $stmt = $this->db->prepare(
            "SELECT cf.nombre, cn.id_calidad_napa
             FROM lotes_fibra l
             LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
             LEFT JOIN calidades_napa cn ON (
                 (cf.nombre LIKE '%Virgen%' AND cn.codigo = 'A') OR
                 (cf.nombre LIKE '%Cristalizada%' AND cn.codigo = 'B') OR
                 (cf.nombre LIKE '%Reciclada%' AND cn.codigo = 'C') OR
                 (cf.nombre LIKE '%Estándar%' AND cn.codigo = 'D')
             )
             WHERE l.id_lote = ? AND cn.estado = 'activo'
             LIMIT 1"
        );
        
        $stmt->execute([$idLote]);
        $mapping = $stmt->fetch();
        
        if ($mapping && $mapping['id_calidad_napa']) {
            return $mapping['id_calidad_napa'];
        }
        
        // Fallback: calidad estándar (código B)
        $stmt = $this->db->query(
            "SELECT id_calidad_napa 
             FROM calidades_napa 
             WHERE codigo = 'B' AND estado = 'activo'
             LIMIT 1"
        );
        
        $fallback = $stmt->fetch();
        return $fallback ? $fallback['id_calidad_napa'] : 2; // Asume ID 2 = Standard
    }
    
    /**
     * Obtener calidades de producto (napa)
     */
    private function getCalidadesProducto() {
        $stmt = $this->db->query(
            "SELECT id_calidad_napa, nombre, codigo, precio_base_sugerido
             FROM calidades_napa
             WHERE estado = 'activo'
             ORDER BY precio_base_sugerido DESC"
        );
        return $stmt->fetchAll();
    }
}
