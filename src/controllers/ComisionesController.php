<?php
/**
 * Controlador de Comisiones
 * Sistema de Gestión de Producción - Taller de Napa
 */

class ComisionesController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
    }
    
    /**
     * Vista principal de comisiones (según rol)
     */
    public function index() {
        if (isAdmin()) {
            // Administrador: Ver todas las comisiones
            return $this->adminIndex();
        } elseif (isOperador()) {
            // Operador: Ver sus propias comisiones
            return $this->misComisiones();
        } else {
            setFlashMessage(MSG_ERROR, 'No tiene permisos para acceder a esta sección');
            redirect(BASE_URL . '/dashboard');
        }
    }
    
    /**
     * Panel de administración de comisiones (solo admin)
     */
    private function adminIndex() {
        $this->checkRole(ROL_ADMINISTRADOR);
        
        // Verificar si las tablas existen
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'comisiones'");
            $tableExists = $stmt->fetch();
            
            if (!$tableExists) {
                // Las tablas no existen, mostrar mensaje de instalación
                $data = [
                    'title' => 'Administrar Comisiones',
                    'tabla_no_existe' => true
                ];
                $this->view('comisiones/admin', $data);
                return;
            }
        } catch (PDOException $e) {
            // Error al verificar, asumir que no existe
            $data = [
                'title' => 'Administrar Comisiones',
                'tabla_no_existe' => true
            ];
            $this->view('comisiones/admin', $data);
            return;
        }
        
        // Obtener operadores activos
        $stmt = $this->db->prepare(
            "SELECT id_usuario, nombre_completo, tarifa_por_bolsa 
             FROM usuarios 
             WHERE rol IN (?, ?) AND estado = ?
             ORDER BY nombre_completo"
        );
        $stmt->execute([ROL_OPERADOR, ROL_TRABAJADOR, ESTADO_ACTIVO]);
        $operadores = $stmt->fetchAll();

        // Obtener producción pendiente de procesar (sin comisión asignada)
        $produccionPendiente = [];
        try {
            // Verificar si la columna id_comision existe en producciones
            $colExists = $this->db->query("SHOW COLUMNS FROM producciones LIKE 'id_comision'")->rowCount() > 0;
            
            if ($colExists) {
                $stmt = $this->db->query(
                    "SELECT 
                        u.id_usuario,
                        u.nombre_completo,
                        u.tarifa_por_bolsa,
                        COUNT(p.id_produccion) as total_producciones,
                        SUM(p.cantidad_producida) as total_bolsas,
                        SUM(p.cantidad_producida * u.tarifa_por_bolsa) as monto_estimado,
                        MIN(DATE(p.fecha_produccion)) as fecha_inicio,
                        MAX(DATE(p.fecha_produccion)) as fecha_fin
                    FROM usuarios u
                    INNER JOIN producciones p ON u.id_usuario = p.id_operario
                    WHERE p.estado_validacion = 'aprobado' 
                      AND p.id_comision IS NULL
                    GROUP BY u.id_usuario, u.nombre_completo, u.tarifa_por_bolsa
                    ORDER BY monto_estimado DESC"
                );
                $produccionPendiente = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            // Ignorar error si la estructura no está actualizada
            error_log("Error obteniendo producción pendiente: " . $e->getMessage());
        }
        
        // Obtener comisiones pendientes de pago
        try {
            $stmt = $this->db->query(
                "SELECT * FROM v_comisiones_pendientes 
                 ORDER BY fecha_fin DESC
                 LIMIT 20"
            );
            $comisionesPendientes = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Si la vista no existe, consultar directamente
            try {
                $stmt = $this->db->query(
                    "SELECT c.*, u.username, u.nombre_completo as operario,
                            DATEDIFF(CURDATE(), c.fecha_fin) as dias_pendiente
                     FROM comisiones c
                     INNER JOIN usuarios u ON c.id_operario = u.id_usuario
                     WHERE c.estado IN ('pendiente', 'calculado')
                     ORDER BY c.fecha_fin DESC
                     LIMIT 20"
                );
                $comisionesPendientes = $stmt->fetchAll();
            } catch (PDOException $e2) {
                $comisionesPendientes = [];
            }
        }
        
        // Obtener últimas comisiones pagadas
        try {
            $stmt = $this->db->query(
                "SELECT * FROM v_historial_comisiones 
                 ORDER BY fecha_pago DESC
                 LIMIT 10"
            );
            $historialPagos = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Si la vista no existe, consultar directamente
            try {
                $stmt = $this->db->query(
                    "SELECT c.*, u.username, u.nombre_completo as operario,
                            admin.nombre_completo as pagado_por
                     FROM comisiones c
                     INNER JOIN usuarios u ON c.id_operario = u.id_usuario
                     LEFT JOIN usuarios admin ON c.usuario_pago = admin.id_usuario
                     WHERE c.estado = 'pagado'
                     ORDER BY c.fecha_pago DESC
                     LIMIT 10"
                );
                $historialPagos = $stmt->fetchAll();
            } catch (PDOException $e2) {
                $historialPagos = [];
            }
        }
        
        // Agrupar comisiones pendientes por operador
        $comisionesAgrupadas = [];
        foreach ($comisionesPendientes as $com) {
            $idOp = $com['id_operario'] ?? $com['username']; // Fallback a username si no hay ID
            
            if (!isset($comisionesAgrupadas[$idOp])) {
                $comisionesAgrupadas[$idOp] = [
                    'nombre' => $com['operario'],
                    'total_monto' => 0,
                    'total_bolsas' => 0,
                    'max_dias_pendiente' => 0,
                    'items' => []
                ];
            }
            
            $comisionesAgrupadas[$idOp]['total_monto'] += $com['monto_total'];
            $comisionesAgrupadas[$idOp]['total_bolsas'] += $com['total_bolsas_producidas'];
            $comisionesAgrupadas[$idOp]['max_dias_pendiente'] = max($comisionesAgrupadas[$idOp]['max_dias_pendiente'], $com['dias_pendiente']);
            $comisionesAgrupadas[$idOp]['items'][] = $com;
        }

        $data = [
            'title' => 'Administrar Comisiones',
            'operadores' => $operadores,
            'produccion_pendiente' => $produccionPendiente,
            'comisiones_pendientes' => $comisionesPendientes, // Mantener para compatibilidad si fuera necesario
            'comisiones_agrupadas' => $comisionesAgrupadas,
            'historial_pagos' => $historialPagos,
            'tabla_no_existe' => false
        ];
        
        $this->view('comisiones/admin', $data);
    }
    
    /**
     * Ver comisiones del operador logueado
     */
    public function misComisiones() {
        requireAnyRole([ROL_OPERADOR, ROL_TRABAJADOR]);
        
        // Verificar si las tablas existen
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'comisiones'");
            $tableExists = $stmt->fetch();
            
            if (!$tableExists) {
                // Las tablas no existen, mostrar mensaje
                $data = [
                    'title' => 'Mis Comisiones',
                    'tabla_no_existe' => true
                ];
                $this->view('comisiones/mis_comisiones', $data);
                return;
            }
        } catch (PDOException $e) {
            $data = [
                'title' => 'Mis Comisiones',
                'tabla_no_existe' => true
            ];
            $this->view('comisiones/mis_comisiones', $data);
            return;
        }
        
        $idOperario = getCurrentUserId();
        $mes = $this->get('mes', date('m'));
        $anio = $this->get('anio', date('Y'));
        
        // Resumen de producción del mes
        $stmt = $this->db->prepare(
            "CALL sp_resumen_comisiones_operario(?, ?, ?)"
        );
        $stmt->execute([$idOperario, $anio, $mes]);
        $produccionMes = $stmt->fetchAll();
        $stmt->closeCursor();
        
        // Obtener comisiones del operario
        $stmt = $this->db->prepare(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM comisiones_detalle WHERE id_comision = c.id_comision) as num_producciones
             FROM comisiones c
             WHERE c.id_operario = ?
             ORDER BY c.fecha_fin DESC
             LIMIT 12"
        );
        $stmt->execute([$idOperario]);
        $misComisiones = $stmt->fetchAll();
        
        // Calcular totales del mes a partir del resumen (SP)
        $totalBolsas = 0;
        $totalComisionEstimada = 0;
        $totalPagado = 0;
        $totalPendiente = 0;

        foreach ($produccionMes as $dia) {
            $totalBolsas += $dia['bolsas_aprobadas'];
            $montoDia = $dia['comision_estimada'];
            $totalComisionEstimada += $montoDia;

            // Verificar estado devuelto por el SP (si lo tiene)
            if (isset($dia['estado_comision']) && $dia['estado_comision'] === 'pagado') {
                $totalPagado += $montoDia;
            } else {
                $totalPendiente += $montoDia;
            }
        }

        // Asegurar que los pagos registrados por el administrador estén reflejados:
        // Consultar la tabla `comisiones` directamente para el periodo seleccionado
        try {
            $stmt = $this->db->prepare(
                "SELECT 
                    SUM(CASE WHEN estado = 'pagado' THEN monto_total ELSE 0 END) as suma_pagado,
                    SUM(CASE WHEN estado IN ('pendiente','calculado') THEN monto_total ELSE 0 END) as suma_pendiente
                 FROM comisiones
                 WHERE id_operario = ? AND YEAR(fecha_fin) = ? AND MONTH(fecha_fin) = ?"
            );
            $stmt->execute([$idOperario, $anio, $mes]);
            $sumas = $stmt->fetch();
            // Sólo sobrescribimos si la consulta devuelve valores NO NULL (es decir, la tabla existe y la consulta fue aplicada)
            if ($sumas && ($sumas['suma_pagado'] !== null || $sumas['suma_pendiente'] !== null)) {
                $totalPagado = (float)($sumas['suma_pagado'] ?? 0);
                $totalPendiente = (float)($sumas['suma_pendiente'] ?? 0);
            }
        } catch (PDOException $e) {
            // Si falla la consulta (estructura no existente), mantenemos los valores calculados por el SP
            error_log('No se pudo sincronizar totales de comisiones: ' . $e->getMessage());
        }
        
        // Obtener tarifa actual
        $stmt = $this->db->prepare("SELECT tarifa_por_bolsa FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$idOperario]);
        $tarifaActual = $stmt->fetchColumn();

        // Calcular total a cobrar por producciones (misma lógica que en Mis Producciones)
        try {
            $stmt = $this->db->prepare(
                "SELECT 
                    SUM(CASE WHEN p.estado_validacion = 'aprobado' 
                             AND (p.id_comision IS NULL OR (c.estado IS NOT NULL AND c.estado != 'pagado'))
                             THEN p.cantidad_producida * u.tarifa_por_bolsa ELSE 0 END) as total_a_cobrar_producciones
                 FROM producciones p
                 INNER JOIN usuarios u ON p.id_operario = u.id_usuario
                 LEFT JOIN comisiones c ON p.id_comision = c.id_comision
                 WHERE p.id_operario = ?"
            );
            $stmt->execute([$idOperario]);
            $row = $stmt->fetch();
            $total_a_cobrar_producciones = $row ? (float)$row['total_a_cobrar_producciones'] : 0.0;
        } catch (PDOException $e) {
            error_log('Error calculando total a cobrar (producciones): ' . $e->getMessage());
            $total_a_cobrar_producciones = 0.0;
        }
        
        $data = [
            'title' => 'Mis Comisiones',
            'mes' => $mes,
            'anio' => $anio,
            'produccion_mes' => $produccionMes,
            'mis_comisiones' => $misComisiones,
            'total_bolsas_mes' => $totalBolsas,
            'total_comision_estimada' => $totalComisionEstimada,
            'total_pagado' => $totalPagado,
            'total_pendiente' => $totalPendiente,
            'total_a_cobrar_producciones' => $total_a_cobrar_producciones,
            'tarifa_actual' => $tarifaActual,
            'meses' => [
                '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
                '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
                '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
            ]
        ];
        
        $this->view('comisiones/mis_comisiones', $data);
    }
    
    /**
     * Calcular comisión para un operario (AJAX)
     */
    public function calcular() {
        $this->checkRole(ROL_ADMINISTRADOR);
        
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idOperario = $this->post('id_operario');
        $fechaInicio = $this->post('fecha_inicio');
        $fechaFin = $this->post('fecha_fin');
        
        // Validar datos
        if (empty($idOperario) || empty($fechaInicio) || empty($fechaFin)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        // Validar fechas
        if (strtotime($fechaInicio) > strtotime($fechaFin)) {
            $this->json(['success' => false, 'message' => 'La fecha de inicio debe ser anterior a la fecha fin'], 400);
        }
        
        try {
            // Llamar al stored procedure
            $stmt = $this->db->prepare("CALL sp_calcular_comision_operario(?, ?, ?, ?, @id_comision, @mensaje)");
            $stmt->execute([
                $idOperario, 
                $fechaInicio, 
                $fechaFin, 
                getCurrentUserId()
            ]);
            $stmt->closeCursor();
            
            // Obtener resultados
            $result = $this->db->query("SELECT @id_comision as id_comision, @mensaje as mensaje")->fetch();
            
            if ($result['id_comision']) {
                // Obtener datos de la comisión creada
                $stmt = $this->db->prepare(
                    "SELECT c.*, u.nombre_completo 
                     FROM comisiones c
                     INNER JOIN usuarios u ON c.id_operario = u.id_usuario
                     WHERE c.id_comision = ?"
                );
                $stmt->execute([$result['id_comision']]);
                $comision = $stmt->fetch();
                
                $this->json([
                    'success' => true,
                    'message' => $result['mensaje'],
                    'comision' => $comision
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => $result['mensaje']
                ]);
            }
        } catch (Exception $e) {
            logError('Error al calcular comisión: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error al calcular la comisión: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ver detalle de una comisión
     */
    public function detalle($idComision = null) {
        if (!is_numeric($idComision)) {
            setFlashMessage(MSG_ERROR, 'ID de comisión inválido');
            redirect(BASE_URL . '/comisiones');
        }
        
        // Obtener datos de la comisión
        $stmt = $this->db->prepare(
            "SELECT c.*, u.nombre_completo, u.username, u.dni,
                    admin_creo.nombre_completo as calculado_por,
                    admin_pago.nombre_completo as pagado_por
             FROM comisiones c
             INNER JOIN usuarios u ON c.id_operario = u.id_usuario
             LEFT JOIN usuarios admin_creo ON c.usuario_creo = admin_creo.id_usuario
             LEFT JOIN usuarios admin_pago ON c.usuario_pago = admin_pago.id_usuario
             WHERE c.id_comision = ?"
        );
        $stmt->execute([$idComision]);
        $comision = $stmt->fetch();
        
        if (!$comision) {
            setFlashMessage(MSG_ERROR, 'Comisión no encontrada');
            redirect(BASE_URL . '/comisiones');
        }
        
        // Verificar permisos
        if (!isAdmin() && $comision['id_operario'] != getCurrentUserId()) {
            setFlashMessage(MSG_ERROR, 'No tiene permisos para ver esta comisión');
            redirect(BASE_URL . '/comisiones');
        }
        
        // Obtener detalle de producciones
        $stmt = $this->db->prepare(
            "SELECT cd.*, p.fecha_produccion, p.observaciones
             FROM comisiones_detalle cd
             INNER JOIN producciones p ON cd.id_produccion = p.id_produccion
             WHERE cd.id_comision = ?
             ORDER BY cd.fecha_produccion DESC"
        );
        $stmt->execute([$idComision]);
        $detalle = $stmt->fetchAll();
        
        $data = [
            'title' => 'Detalle de Comisión #' . $idComision,
            'comision' => $comision,
            'detalle' => $detalle
        ];
        
        $this->view('comisiones/detalle', $data);
    }
    
    /**
     * Registrar pago de comisión (solo admin)
     */
    public function registrarPago() {
        $this->checkRole(ROL_ADMINISTRADOR);
        
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idComision = $this->post('id_comision');
        $fechaPago = $this->post('fecha_pago');
        $metodoPago = $this->post('metodo_pago');
        $numeroOperacion = $this->post('numero_operacion');
        
        // Validar datos
        if (empty($idComision) || empty($fechaPago) || empty($metodoPago)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        try {
            // Llamar al stored procedure
            $stmt = $this->db->prepare("CALL sp_registrar_pago_comision(?, ?, ?, ?, ?, @mensaje)");
            $stmt->execute([
                $idComision,
                $fechaPago,
                $metodoPago,
                $numeroOperacion,
                getCurrentUserId()
            ]);
            $stmt->closeCursor();
            
            // Obtener resultado
            $result = $this->db->query("SELECT @mensaje as mensaje")->fetch();
            
            $this->json([
                'success' => true,
                'message' => $result['mensaje']
            ]);
        } catch (Exception $e) {
            logError('Error al registrar pago: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Anular una comisión (solo admin)
     */
    public function anular() {
        $this->checkRole(ROL_ADMINISTRADOR);
        
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idComision = $this->post('id_comision');
        $motivo = $this->post('motivo');
        
        if (empty($idComision) || empty($motivo)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verificar que no esté pagada
            $stmt = $this->db->prepare("SELECT estado FROM comisiones WHERE id_comision = ?");
            $stmt->execute([$idComision]);
            $estado = $stmt->fetchColumn();
            
            if ($estado === 'pagado') {
                $this->json(['success' => false, 'message' => 'No se puede anular una comisión ya pagada']);
                return;
            }
            
            // Anular la comisión
            $stmt = $this->db->prepare(
                "UPDATE comisiones 
                 SET estado = 'anulado', observaciones = CONCAT(COALESCE(observaciones, ''), '\n[ANULADA] ', ?)
                 WHERE id_comision = ?"
            );
            $stmt->execute([$motivo, $idComision]);
            
            // Liberar las producciones asociadas
            $stmt = $this->db->prepare(
                "UPDATE producciones SET id_comision = NULL WHERE id_comision = ?"
            );
            $stmt->execute([$idComision]);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Comisión anulada exitosamente'
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            logError('Error al anular comisión: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error al anular la comisión'
            ], 500);
        }
    }
    
    /**
     * Generar reporte de comisiones en PDF
     */
    public function reportePDF($idComision = null) {
        if (!is_numeric($idComision)) {
            setFlashMessage(MSG_ERROR, 'ID de comisión inválido');
            redirect(BASE_URL . '/comisiones');
        }
        
        // Obtener datos de la comisión
        $stmt = $this->db->prepare(
            "SELECT c.*, u.nombre_completo, u.username, u.dni
             FROM comisiones c
             INNER JOIN usuarios u ON c.id_operario = u.id_usuario
             WHERE c.id_comision = ?"
        );
        $stmt->execute([$idComision]);
        $comision = $stmt->fetch();
        
        if (!$comision) {
            setFlashMessage(MSG_ERROR, 'Comisión no encontrada');
            redirect(BASE_URL . '/comisiones');
        }
        
        // Verificar permisos
        if (!isAdmin() && $comision['id_operario'] != getCurrentUserId()) {
            setFlashMessage(MSG_ERROR, 'No tiene permisos para ver esta comisión');
            redirect(BASE_URL . '/comisiones');
        }
        
        // TODO: Implementar generación de PDF
        // Por ahora, mostrar vista HTML para imprimir
        $data = [
            'title' => 'Reporte de Comisión',
            'comision' => $comision
        ];
        
        $this->view('comisiones/reporte_pdf', $data);
    }
    
    /**
     * Actualizar tarifa de un operario (solo admin)
     */
    public function actualizarTarifa() {
        $this->checkRole(ROL_ADMINISTRADOR);
        
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $idUsuario = $this->post('id_usuario');
        $tarifaNueva = $this->post('tarifa_nueva');
        $motivo = $this->post('motivo');
        
        if (empty($idUsuario) || empty($tarifaNueva)) {
            $this->json(['success' => false, 'message' => 'Datos incompletos'], 400);
        }
        
        // Validar que la tarifa sea un número positivo
        if (!is_numeric($tarifaNueva) || $tarifaNueva < 0) {
            $this->json(['success' => false, 'message' => 'La tarifa debe ser un número positivo'], 400);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Obtener tarifa anterior
            $stmt = $this->db->prepare("SELECT tarifa_por_bolsa FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$idUsuario]);
            $tarifaAnterior = $stmt->fetchColumn();
            
            // Actualizar tarifa
            $stmt = $this->db->prepare(
                "UPDATE usuarios SET tarifa_por_bolsa = ? WHERE id_usuario = ?"
            );
            $stmt->execute([$tarifaNueva, $idUsuario]);
            
            // Registrar en historial
            $stmt = $this->db->prepare(
                "INSERT INTO historial_tarifas (id_usuario, tarifa_anterior, tarifa_nueva, usuario_autorizo, motivo)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $idUsuario,
                $tarifaAnterior,
                $tarifaNueva,
                getCurrentUserId(),
                $motivo
            ]);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Tarifa actualizada exitosamente'
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            logError('Error al actualizar tarifa: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar la tarifa'
            ], 500);
        }
    }
}
