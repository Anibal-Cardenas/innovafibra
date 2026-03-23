<?php
/**
 * Controlador de Compras
 * Sistema de Gestión de Producción - Taller de Napa
 */

class ComprasController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkRole(ROL_ADMINISTRADOR);
    }
    
    /**
     * Ver detalle de un lote de fibra
     */
    public function detalleLote($id) {
        $lote = $this->getLoteById($id);
        
        if (!$lote) {
            setFlashMessage(MSG_ERROR, 'Lote no encontrado.');
            redirect(BASE_URL . '/compras/lotes');
        }
        
        $data = [
            'title' => 'Detalle del Lote: ' . $lote['codigo_lote'],
            'lote' => $lote,
            'cubos' => $this->getCubosByLoteId($id)
        ];
        
        $this->view('compras/detalle_lote', $data);
    }

    /**
     * Obtener lote por ID
     */
    private function getLoteById($id) {
        $stmt = $this->db->prepare(
            "SELECT 
                l.*,
                p.nombre as proveedor,
                cf.nombre as calidad,
                cf.color as calidad_color,
                u.nombre_completo as usuario
             FROM lotes_fibra l
             INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
             LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
             LEFT JOIN usuarios u ON l.usuario_creacion = u.id_usuario
             WHERE l.id_lote = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener cubos de un lote
     */
    private function getCubosByLoteId($idLote) {
        $stmt = $this->db->prepare(
            "SELECT * FROM cubos_fibra WHERE id_lote = ? ORDER BY numero_cubo ASC"
        );
        $stmt->execute([$idLote]);
        return $stmt->fetchAll();
    }

    /**
     * Lista de lotes de fibra
     */
    public function lotes() {
        $data = [
            'title' => 'Lotes de Fibra',
            'lotes' => $this->getLotes(),
            'db' => $this->db
        ];

        $this->view('compras/lotes', $data);
    }
    
    /**
     * Nueva compra de bolsas plásticas
     */
    public function nuevaBolsas() {
        $data = [
            'title' => 'Nueva Compra de Bolsas Plásticas',
            'proveedores' => $this->getProveedores(PROVEEDOR_BOLSAS),
            'factor_conversion' => (float)getConfigValue('factor_conversion_bolsas', DEFAULT_FACTOR_CONVERSION),
            'db' => $this->db
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarCompraBolsas();
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/compras/lotes');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('compras/nueva_bolsas', $data);
    }

    /**
     * Nueva compra de fibra
     */
    public function nuevaFibra() {
        $data = [
            'title' => 'Nueva Compra de Fibra',
            'proveedores' => $this->getProveedores(PROVEEDOR_FIBRA),
            'calidades_fibra' => $this->getCalidadesFibra(),
            'factor_conversion' => (float)getConfigValue('factor_conversion', DEFAULT_FACTOR_CONVERSION),
            'factor_conversion_cubo' => (float)getConfigValue('factor_conversion_cubo', DEFAULT_FACTOR_CONVERSION_CUBO),
            'cantidad_estimada_default' => (int)getConfigValue('cantidad_estimada_default', DEFAULT_CANTIDAD_ESTIMADA),
            'db' => $this->db
        ];

        if ($this->isPost()) {
            $result = $this->procesarCompraFibra();

            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/compras/lotes');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }

        $this->view('compras/nueva_fibra', $data);
    }

    /**
     * Procesar compra de fibra
     */
    private function procesarCompraFibra() {
        // Validar CSRF
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }

        // 1. Recoger datos Arrays
        // Se elimina el uso de pesos_bruto del formulario.
        // Se asume que peso_bruto será igual a peso_neto para cumplir con restricciones de BD.
        $pesosNeto = $this->post('pesos_neto') ?? [];
        $pesosBruto = $pesosNeto; // Asignamos peso neto como peso bruto
        $estimadasCubos = $this->post('cantidad_estimada_cubos') ?? [];

        // 2. Validaciones básicas
        $validator = new Validator($_POST);
        $validator
            ->required('fecha_compra')
            ->required('id_proveedor')
            ->required('id_calidad_fibra')
            ->required('precio_total')
            ->required('numero_cubos')
            ->numeric('precio_total')
            ->integer('numero_cubos')
            ->min('numero_cubos', 1);

        if ($validator->fails()) {
            return ['success' => false, 'message' => implode('<br>', array_map(function($errors) {
                return implode('<br>', $errors);
            }, $validator->errors()))];
        }

        // 3. Validar consistencia de arrays
        $numCubos = (int)$this->post('numero_cubos');
        // Validamos solo pesosNeto
        if (!is_array($pesosNeto) || count($pesosNeto) != $numCubos) {
            return ['success' => false, 'message' => 'La cantidad de pesos ingresados no coincide con el número de fardos.'];
        }

        // 4. Calcular Totales Real (Server Side)
        $totalPesoBruto = 0;
        $totalPesoNeto = 0;
        $totalEstimadasCalc = 0;
        
        foreach ($pesosNeto as $k => $pn) {
            $pnVal = (float)$pn;
            $pbVal = $pnVal; // Bruto = Neto
            $estVal = (int)($estimadasCubos[$k] ?? 0);
            
            if ($pnVal <= 0) {
                 return ['success' => false, 'message' => "El peso del fardo #" . ($k+1) . " debe ser mayor a cero."];
            }
            
            $totalPesoBruto += $pbVal;
            $totalPesoNeto += $pnVal;
            $totalEstimadasCalc += $estVal;
        }

        $cantidadEstimadaTotal = (int)$this->post('cantidad_estimada');
        // Si no se envió cantidad global o es 0, usamos la suma de las individuales si existe
        if ($cantidadEstimadaTotal <= 0 && $totalEstimadasCalc > 0) {
            $cantidadEstimadaTotal = $totalEstimadasCalc;
        }

        $precioTotal = (float)$this->post('precio_total');
        $precioPorKg = ($totalPesoNeto > 0) ? ($precioTotal / $totalPesoNeto) : 0;
        $rendimientoEstimado = ($totalPesoNeto > 0) ? ($cantidadEstimadaTotal / $totalPesoNeto) : 0;

        try {
            $this->db->beginTransaction();

            // 5. Generar Código Lote
            $stmt = $this->db->prepare("CALL sp_generar_codigo_lote(@codigo_lote)");
            $stmt->execute();
            $stmt->closeCursor();
            
            $result = $this->db->query("SELECT @codigo_lote as codigo")->fetch();
            $codigoLote = $result['codigo'];

            if (!$codigoLote) {
                throw new Exception("Error al generar código de lote.");
            }

            // 6. Insertar Lote
            $stmtLote = $this->db->prepare(
                "INSERT INTO lotes_fibra 
                (codigo_lote, fecha_compra, id_proveedor, id_calidad_fibra, 
                 peso_bruto, peso_neto, precio_total, precio_por_kg, 
                 cantidad_estimada_bolsas, rendimiento_estimado, 
                 numero_cubos, numero_guia, observaciones, usuario_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmtLote->execute([
                $codigoLote,
                $this->post('fecha_compra'),
                $this->post('id_proveedor'),
                $this->post('id_calidad_fibra'),
                $totalPesoBruto,
                $totalPesoNeto,
                $precioTotal,
                $precioPorKg,
                $cantidadEstimadaTotal,
                $rendimientoEstimado,
                $numCubos,
                $this->post('numero_guia') ?: null,
                $this->post('observaciones'),
                getCurrentUserId()
            ]);

            $idLote = $this->db->lastInsertId();

            // 7. Insertar Cubos
            $stmtCubo = $this->db->prepare(
                "INSERT INTO cubos_fibra 
                (id_lote, numero_cubo, peso_bruto, peso_neto, cantidad_estimada_bolsas, estado)
                VALUES (?, ?, ?, ?, ?, 'disponible')"
            );

            foreach ($pesosBruto as $k => $pb) {
                $stmtCubo->execute([
                    $idLote,
                    $k + 1,
                    (float)$pb,
                    (float)$pesosNeto[$k],
                    (int)($estimadasCubos[$k] ?? 0)
                ]);
            }
            
            // Auditoría
            registrarAuditoria('lotes_fibra', $idLote, AUDITORIA_INSERT, "Compra de fibra Lote $codigoLote");

            $this->db->commit();
            return ['success' => true, 'message' => "Compra registrada exitosamente. Lote: $codigoLote"];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en procesarCompraFibra: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Procesar compra de bolsas
     */
    private function procesarCompraBolsas() {
        // Validar CSRF
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        // Validar datos
        $validator = new Validator($_POST);
        $validator
            ->required('fecha_compra')
            ->required('id_proveedor')
            ->required('peso_kg')
            ->required('precio_total')
            ->numeric('peso_kg')
            ->numeric('precio_total')
            ->min('peso_kg', 0.01)
            ->min('precio_total', 0.01);
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Por favor complete todos los campos correctamente'];
        }
        
        try {
            $this->db->beginTransaction();
            
            $precioPorKg = $this->post('precio_total') / $this->post('peso_kg');
            
            $stmt = $this->db->prepare(
                "INSERT INTO compras_bolsas 
                (fecha_compra, id_proveedor, peso_kg, precio_total, precio_por_kg, tipo_bolsa, observaciones, usuario_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $this->post('fecha_compra'),
                $this->post('id_proveedor'),
                $this->post('peso_kg'),
                $this->post('precio_total'),
                $precioPorKg,
                $this->post('tipo_bolsa'),
                $this->post('observaciones'),
                getCurrentUserId()
            ]);
            
            $idCompra = $this->db->lastInsertId();
            
            registrarAuditoria('compras_bolsas', $idCompra, AUDITORIA_INSERT, 'Compra de bolsas plásticas');
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Compra de bolsas registrada exitosamente'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al registrar compra de bolsas: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar la compra'];
        }
    }
    
    /**
     * Obtener lotes de fibra
     */
    private function getLotes() {
        $stmt = $this->db->query(
            "SELECT 
                l.id_lote,
                l.codigo_lote,
                l.fecha_compra,
                p.nombre as proveedor,
                l.numero_cubos,
                l.peso_neto,
                l.precio_total,
                l.cantidad_estimada_bolsas,
                l.cantidad_producida_real,
                l.estado,
                cf.nombre as calidad,
                cf.color as calidad_color,
                CASE 
                    WHEN l.cantidad_producida_real > 0 
                    THEN ROUND((l.cantidad_producida_real / l.cantidad_estimada_bolsas) * 100, 2)
                    ELSE 0
                END as eficiencia
             FROM lotes_fibra l
             INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
             LEFT JOIN calidades_fibra cf ON l.id_calidad_fibra = cf.id_calidad_fibra
             ORDER BY l.fecha_compra DESC, l.codigo_lote DESC"
        );
        
        return $stmt->fetchAll();
    }

    /**
     * Lista de compras de bolsas
     */
    public function bolsas() {
        $data = [
            'title' => 'Compras de Bolsas',
            'compras' => $this->getComprasBolsas()
        ];

        $this->view('compras/bolsas', $data);
    }

    /**
     * Obtener compras de bolsas
     */
    private function getComprasBolsas() {
        $stmt = $this->db->query(
            "SELECT cb.id_compra_bolsa, cb.fecha_compra, p.nombre as proveedor, cb.peso_kg, cb.precio_total, cb.precio_por_kg, cb.tipo_bolsa, cb.observaciones, u.nombre_completo as usuario
             FROM compras_bolsas cb
             INNER JOIN proveedores p ON cb.id_proveedor = p.id_proveedor
             LEFT JOIN usuarios u ON cb.usuario_creacion = u.id_usuario
             ORDER BY cb.fecha_compra DESC, cb.id_compra_bolsa DESC"
        );

        return $stmt->fetchAll();
    }
    
    /**
     * Obtener proveedores por tipo
     */
    private function getProveedores($tipo) {
        $stmt = $this->db->prepare(
            "SELECT id_proveedor, nombre
             FROM proveedores
             WHERE tipo_proveedor = ? AND estado = ?
             ORDER BY nombre"
        );
        
        $stmt->execute([$tipo, ESTADO_ACTIVO]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener calidades de fibra
     */
    private function getCalidadesFibra() {
        $stmt = $this->db->query(
            "SELECT id_calidad_fibra, nombre, descripcion, color
             FROM calidades_fibra
             WHERE estado = 'activo'
             ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }
}
