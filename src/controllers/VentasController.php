<?php
/**
 * Controlador de Ventas
 * Sistema de Gestión de Producción - Taller de Napa
 */

class VentasController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        // Permitir acceso a admin y vendedor
        requireAnyRole([ROL_ADMINISTRADOR, ROL_VENDEDOR]);
    }
    
    /**
     * Lista de ventas
     */
    public function index() {
        $desde = $this->get('desde');
        $hasta = $this->get('hasta');

        $data = [
            'title' => 'Ventas',
            'ventas' => $this->getVentas($desde, $hasta),
            'filtro_desde' => $desde,
            'filtro_hasta' => $hasta
        ];
        
        $this->view('ventas/lista', $data);
    }
    
    /**
     * Registrar nueva venta
     */
    public function nueva() {
        $data = [
            'title' => 'Nueva Venta',
            'clientes' => $this->getClientes(),
            'choferes' => $this->getChoferes(),
            'calidades_producto' => $this->getCalidadesConStock()
        ];
        
        if ($this->isPost()) {
            $result = $this->procesarVenta();
            
            if ($result['success']) {
                setFlashMessage(MSG_SUCCESS, $result['message']);
                redirect(BASE_URL . '/ventas');
            } else {
                $data['error'] = $result['message'];
                $data['old'] = $_POST;
            }
        }
        
        $this->view('ventas/nueva', $data);
    }
    
    /**
     * Ver detalle de venta
     */
    public function detalle() {
        $idVenta = $this->get('id');
        
        if (!$idVenta) {
            setFlashMessage(MSG_ERROR, 'Venta no especificada');
            redirect(BASE_URL . '/ventas');
        }
        
        $venta = $this->getVentaById($idVenta);
        
        if (!$venta) {
            setFlashMessage(MSG_ERROR, 'Venta no encontrada');
            redirect(BASE_URL . '/ventas');
        }
        
        $data = [
            'title' => 'Detalle de Venta',
            'venta' => $venta,
            'detalles' => $this->extraerDetallesVenta($venta)
        ];
        
        $this->view('ventas/detalle', $data);
    }
    
    /**
     * Generar guía de remisión (PDF)
     */
    public function guia() {
        $idVenta = $this->get('id');
        
        if (!$idVenta) {
            setFlashMessage(MSG_ERROR, 'Venta no especificada');
            redirect(BASE_URL . '/ventas');
        }
        
        $venta = $this->getVentaById($idVenta);
        
        if (!$venta) {
            setFlashMessage(MSG_ERROR, 'Venta no encontrada');
            redirect(BASE_URL . '/ventas');
        }
        
        // Aquí iría la generación del PDF
        // Por ahora, mostrar vista simple
        $data = [
            'title' => 'Orden de Salida',
            'venta' => $venta,
            'detalles' => $this->extraerDetallesVenta($venta)
        ];
        
        $this->viewRaw('ventas/guia', $data);
    }
    
    /**
     * Generar comprobante de venta (Ticket/Factura)
     */
    public function comprobante() {
        $idVenta = $this->get('id');
        
        if (!$idVenta) {
            setFlashMessage(MSG_ERROR, 'Venta no especificada');
            redirect(BASE_URL . '/ventas');
        }
        
        $venta = $this->getVentaById($idVenta);
        
        if (!$venta) {
            setFlashMessage(MSG_ERROR, 'Venta no encontrada');
            redirect(BASE_URL . '/ventas');
        }
        
        $data = [
            'title' => 'Ticket de Venta',
            'venta' => $venta,
            'detalles' => $this->extraerDetallesVenta($venta)
        ];
        
        $this->viewRaw('ventas/comprobante', $data);
    }
    
    /**
     * Cancelar una venta
     */
    public function cancelar() {
        if (!isAdmin() && !isVendedor()) {
            setFlashMessage(MSG_ERROR, 'No tiene permisos para realizar esta acción.');
            redirect(BASE_URL . '/ventas');
        }

        $idVenta = $this->post('id_venta');
        if (!$idVenta) {
            setFlashMessage(MSG_ERROR, 'Venta no especificada.');
            redirect(BASE_URL . '/ventas');
        }

        try {
            $this->db->beginTransaction();

            // 1. Obtener la venta y verificar estado
            $venta = $this->getVentaById($idVenta);
            if (!$venta) {
                throw new Exception("Venta no encontrada.");
            }

            if ($venta['estado_pago'] === 'cancelado') {
                throw new Exception("Esta venta ya ha sido cancelada.");
            }

            // 2. Actualizar estado de la venta
            $stmt = $this->db->prepare("UPDATE ventas SET estado_pago = 'cancelado' WHERE id_venta = ?");
            $stmt->execute([$idVenta]);

            // 3. Restaurar inventario y registrar en Kardex
            $detalles = $this->extraerDetallesVenta($venta);
            
            foreach ($detalles as $item) {
                $cantidad = $item['cantidad'];
                // Obtener ID de calidad desde el detalle o usar el principal de la venta
                // Nota: extraerDetallesVenta devuelve array estandarizado, pero aseguramos tener id_calidad
                $idCalidad = isset($item['id_calidad']) ? $item['id_calidad'] : 
                             (isset($item['id_calidad_napa']) ? $item['id_calidad_napa'] : $venta['id_calidad_napa']);
                
                // Si no hay ID calidad específico, intentar deducirlo o usar null (general)
                if (!$idCalidad) $idCalidad = $venta['id_calidad_napa'];

                // Restaurar inventario
                $stmtInv = $this->db->prepare("UPDATE inventario SET cantidad = cantidad + ? WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = ?");
                $stmtInv->execute([$cantidad, $idCalidad]);

                // Registrar entrada en Kardex (Devolución)
                $stmtKardex = $this->db->prepare(
                    "INSERT INTO kardex (tipo_movimiento, tipo_item, id_calidad_napa, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones, usuario_registro) 
                     VALUES ('entrada', 'producto_terminado', ?, ?, 'unidades', NOW(), ?, ?, ?)"
                );
                $stmtKardex->execute([
                    $idCalidad, 
                    $cantidad, 
                    "VENTA-$idVenta", 
                    "Cancelación de venta #$idVenta",
                    getCurrentUserId()
                ]);
            }

            // 4. Auditoría
            registrarAuditoria('ventas', $idVenta, 'UPDATE', "Venta cancelada por usuario " . getCurrentUserId());

            $this->db->commit();
            setFlashMessage(MSG_SUCCESS, 'Venta cancelada correctamente. El stock ha sido restaurado.');

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al cancelar venta: " . $e->getMessage());
            setFlashMessage(MSG_ERROR, 'Error al cancelar la venta: ' . $e->getMessage());
        }

        redirect(BASE_URL . '/ventas');
    }

    /**
     * Calcular precio y costo (AJAX)
     */
    public function calcularPrecio() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }
        
        $cantidad = (int)$this->post('cantidad');
        $precioUnitario = (float)$this->post('precio_unitario');
        
        if ($cantidad <= 0 || $precioUnitario <= 0) {
            $this->json(['success' => false, 'message' => 'Datos inválidos'], 400);
        }
        
        try {
            // Calcular costo unitario usando función almacenada
            $costoUnitario = $this->calcularCostoReferencia();
            
            // Calcular totales
            $precioTotal = $cantidad * $precioUnitario;
            $costoTotal = $cantidad * $costoUnitario;
            
            $this->json([
                'success' => true,
                'costo_unitario' => $costoUnitario,
                'precio_total' => $precioTotal,
                'costo_total' => $costoTotal
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al calcular precio: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error al calcular'], 500);
        }
    }
    
    /**
     * Procesar nueva venta
     */
    private function procesarVenta() {
        // Validar CSRF
        if (!verifyCsrfToken($this->post('csrf_token'))) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }
        
        $fechaVenta = $this->post('fecha_venta');
        $idCliente = $this->post('id_cliente');
        $idChofer = $this->post('id_chofer');
        $productos = $this->post('productos'); // Array de productos
        $fechaEntrega = $this->post('fecha_entrega_estimada');
        $observaciones = $this->post('observaciones');
        
        // Validar
        $validator = new Validator($_POST);
        $validator
            ->required('fecha_venta')
            ->required('id_chofer')
            ->required('fecha_entrega_estimada');
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Por favor complete todos los campos correctamente'];
        }
        
        if (empty($productos) || !is_array($productos)) {
            return ['success' => false, 'message' => 'Debe agregar al menos un producto'];
        }

        try {
            // Procesar productos y calcular totales
            $totalCantidad = 0;
            $totalPrecio = 0;
            $totalCosto = 0;
            $itemsProcesados = [];
            
            // Encontrar el producto principal (mayor cantidad) para usar como referencia en la BD
            $productoPrincipal = null;
            $maxQty = 0;

            foreach ($productos as $item) {
                $idCalidad = (int)$item['id_calidad'];
                $cantidad = (float)$item['cantidad'];
                
                // Validación estricta: Solo las bolsas (999999) pueden tener decimales
                if ($idCalidad != 999999) {
                    if (floor($cantidad) != $cantidad) {
                         return [
                            'success' => false,
                            'message' => "El producto seleccionado solo permite cantidades enteras (sin decimales)."
                        ];
                    }
                    $cantidad = (int)$cantidad;
                }

                $precio = (float)$item['precio'];

                if ($cantidad <= 0 || $precio <= 0) continue;

                // Validar stock individual
                $stockDisponible = $this->getStockPorCalidad($idCalidad);
                if ($cantidad > $stockDisponible) {
                    $nombreProd = ($idCalidad == 999999) ? 'Bolsas (Insumo)' : 'Producto';
                    return [
                        'success' => false,
                        'message' => "Stock insuficiente para $nombreProd. Solicitado: $cantidad, Disponible: $stockDisponible"
                    ];
                }

                $costoUnitario = $this->calcularCostoReferencia($idCalidad);
                
                $itemsProcesados[] = array_merge($item, ['costo' => $costoUnitario, 'subtotal' => $cantidad * $precio]);

                $totalCantidad += $cantidad;
                $totalPrecio += ($cantidad * $precio);
                $totalCosto += ($cantidad * $costoUnitario);
                
                if ($cantidad > $maxQty) {
                    $maxQty = $cantidad;
                    $productoPrincipal = $itemsProcesados[count($itemsProcesados)-1];
                }
            }

            if (!$productoPrincipal) {
                 return ['success' => false, 'message' => 'No se encontraron productos válidos'];
            }

            $precioPromedio = ($totalCantidad > 0) ? ($totalPrecio / $totalCantidad) : 0;
            $costoPromedio = ($totalCantidad > 0) ? ($totalCosto / $totalCantidad) : 0;

            $this->db->beginTransaction();

            $direccionEntrega = 'Por definir'; // Valor por defecto para la guía

            // Si no se seleccionó cliente, pero se ingresó un nombre, crear cliente rápido
            $nuevoClienteNombre = trim($this->post('nuevo_cliente_nombre'));
            if (empty($idCliente)) {
                if (!empty($nuevoClienteNombre)) {
                    $nuevoRuc = trim($this->post('nuevo_cliente_ruc'));
                    $nuevoTelefono = trim($this->post('nuevo_cliente_telefono'));
                    $nuevaDireccion = trim($this->post('nuevo_cliente_direccion'));

                    $stmtCliente = $this->db->prepare(
                        "INSERT INTO clientes (nombre, ruc, telefono, direccion, estado, fecha_creacion) VALUES (?, ?, ?, ?, 'activo', NOW())"
                    );
                    $stmtCliente->execute([
                        $nuevoClienteNombre, 
                        $nuevoRuc ?: null, 
                        $nuevoTelefono ?: null, 
                        $nuevaDireccion ?: null
                    ]);
                    $idCliente = $this->db->lastInsertId();
                    
                    if (!empty($nuevaDireccion)) $direccionEntrega = $nuevaDireccion;

                    // Registrar auditoría de cliente creado
                    registrarAuditoria('clientes', $idCliente, AUDITORIA_INSERT, "Cliente rápido creado: {$nuevoClienteNombre}");
                } else {
                    // Cliente "Público General"
                    $stmtGen = $this->db->prepare("SELECT id_cliente FROM clientes WHERE nombre = 'Público General' LIMIT 1");
                    $stmtGen->execute();
                    $genId = $stmtGen->fetchColumn();
                    
                    if ($genId) {
                        $idCliente = $genId;
                    } else {
                        // Crear cliente general si no existe
                        $stmtCreate = $this->db->prepare("INSERT INTO clientes (nombre, estado, fecha_creacion) VALUES ('Público General', 'activo', NOW())");
                        $stmtCreate->execute();
                        $idCliente = $this->db->lastInsertId();
                    }
                }
            } else {
                // Cliente existente: Obtener su dirección para la guía
                $stmtDir = $this->db->prepare("SELECT direccion FROM clientes WHERE id_cliente = ?");
                $stmtDir->execute([$idCliente]);
                $dirDb = $stmtDir->fetchColumn();
                if (!empty($dirDb)) $direccionEntrega = $dirDb;
            }

            // Determinar nombre de columna de calidad (compatibilidad entre versiones de esquema)
            $colCalidad = 'id_calidad_napa';
            try {
                $checkCol = $this->db->query("SHOW COLUMNS FROM ventas LIKE 'id_calidad_producto'");
                if ($checkCol->rowCount() > 0) {
                    $colCalidad = 'id_calidad_producto';
                }
            } catch (Exception $e) { }

            // Preparar JSON de detalles para guardar en observaciones
            $jsonDetalle = json_encode($itemsProcesados, JSON_UNESCAPED_UNICODE);
            $obsFinal = $observaciones . "\n[DETALLE_SISTEMA]" . $jsonDetalle;

            // Determinar Tipo de Venta y ID Calidad para Header
            $tipoVenta = 'producto';
            $idCalidadHeader = $productoPrincipal['id_calidad'];
            
            if ($idCalidadHeader == 999999) {
                $tipoVenta = 'insumo';
                $idCalidadHeader = null; // Para BD
            }

            // Insertar venta
            $sql = "INSERT INTO ventas 
                (fecha_venta, id_cliente, $colCalidad, tipo_venta, cantidad_vendida, precio_unitario, precio_total,
                 costo_unitario_referencia, margen_porcentual, observaciones, usuario_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $fechaVenta,
                $idCliente,
                $idCalidadHeader,
                $tipoVenta,
                $totalCantidad,
                $precioPromedio,
                $totalPrecio,
                $costoPromedio,
                0, // Margen porcentual (deshabilitado)
                $obsFinal,
                getCurrentUserId()
            ]);
            
            $idVenta = $this->db->lastInsertId();
            
            // --- CORRECCIÓN DE INVENTARIO (CRÍTICO) ---
            $cantidadRestaurar = $totalCantidad - $productoPrincipal['cantidad'];
            
            if ($cantidadRestaurar > 0) {
                if ($tipoVenta == 'producto') {
                     // Restaurar a Producto Terminado
                     $this->db->prepare("UPDATE inventario SET cantidad = cantidad + ? WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = ?")
                              ->execute([$cantidadRestaurar, $idCalidadHeader]);
                     
                     // Kardex (Ajuste entrada)
                     $this->db->prepare("INSERT INTO kardex (tipo_movimiento, tipo_item, id_calidad_napa, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones) VALUES ('ajuste', 'producto_terminado', ?, ?, 'unidades', NOW(), ?, 'Ajuste venta multiproducto (Restauracion)')")
                              ->execute([$idCalidadHeader, $cantidadRestaurar, "VENTA-$idVenta"]);
                              
                } else {
                     // Restaurar a Bolsas (Insumo)
                     $this->db->prepare("UPDATE inventario SET cantidad = cantidad + ? WHERE tipo_item = 'bolsas_plasticas'")
                              ->execute([$cantidadRestaurar]);
                     
                     // Kardex
                     $this->db->prepare("INSERT INTO kardex (tipo_movimiento, tipo_item, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones) VALUES ('ajuste', 'bolsas_plasticas', ?, 'kg', NOW(), ?, 'Ajuste venta multiproducto (Restauracion)')")
                              ->execute([$cantidadRestaurar, "VENTA-$idVenta"]);
                }
            }
            
            // 2. Descontar los OTROS items
            foreach ($itemsProcesados as $item) {
                if ($item['id_calidad'] == $productoPrincipal['id_calidad'] && $item['cantidad'] == $productoPrincipal['cantidad']) continue;
                
                $qty = (float)$item['cantidad'];
                $idCal = (int)$item['id_calidad'];
                
                if ($idCal == 999999) {
                    $this->db->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE tipo_item = 'bolsas_plasticas'")->execute([$qty]);
                    $this->db->prepare("INSERT INTO kardex (tipo_movimiento, tipo_item, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones) VALUES ('salida', 'bolsas_plasticas', ?, 'kg', NOW(), ?, 'Venta item secundario')")->execute([$qty, "VENTA-$idVenta"]);
                } else {
                    $this->db->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE tipo_item = 'producto_terminado' AND id_calidad_napa = ?")->execute([$qty, $idCal]);
                    $this->db->prepare("INSERT INTO kardex (tipo_movimiento, tipo_item, id_calidad_napa, cantidad, unidad_medida, fecha_movimiento, documento_referencia, observaciones) VALUES ('salida', 'producto_terminado', ?, ?, 'unidades', NOW(), ?, 'Venta item secundario')")->execute([$idCal, $qty, "VENTA-$idVenta"]);
                }
            }
            
            // Crear registro de entrega asociado
            $codigoGuia = 'GR-' . date('Ymd') . '-' . str_pad($idVenta, 5, '0', STR_PAD_LEFT);
            
             try {
                $checkCols = $this->db->query("SHOW COLUMNS FROM entregas");
                $columns = $checkCols->fetchAll(PDO::FETCH_COLUMN);
                $hasEstimada = in_array('fecha_entrega_estimada', $columns);
                $hasEstado = in_array('estado_entrega', $columns);
                $requiresDireccion = in_array('direccion_entrega', $columns);
                
                if ($hasEstimada && $hasEstado) {
                    $direccion = $requiresDireccion ? $direccionEntrega : null;
                    if ($requiresDireccion) {
                        $stmtEntrega = $this->db->prepare("INSERT INTO entregas (id_venta, id_chofer, codigo_guia, fecha_entrega_estimada, direccion_entrega, estado_entrega, usuario_creacion) VALUES (?, ?, ?, ?, ?, 'pendiente', ?)");
                        $stmtEntrega->execute([$idVenta, $idChofer, $codigoGuia, $fechaEntrega, $direccion, getCurrentUserId()]);
                    } else {
                        $stmtEntrega = $this->db->prepare("INSERT INTO entregas (id_venta, id_chofer, codigo_guia, fecha_entrega_estimada, estado_entrega, usuario_creacion) VALUES (?, ?, ?, ?, 'pendiente', ?)");
                        $stmtEntrega->execute([$idVenta, $idChofer, $codigoGuia, $fechaEntrega, getCurrentUserId()]);
                    }
                } else {
                    $direccion = $requiresDireccion ? $direccionEntrega : null;
                    if ($requiresDireccion) {
                        $stmtEntrega = $this->db->prepare("INSERT INTO entregas (id_venta, id_chofer, codigo_guia, fecha_entrega, direccion_entrega, usuario_creacion) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmtEntrega->execute([$idVenta, $idChofer, $codigoGuia, $fechaEntrega, $direccion, getCurrentUserId()]);
                    } else {
                        $stmtEntrega = $this->db->prepare("INSERT INTO entregas (id_venta, id_chofer, codigo_guia, fecha_entrega, usuario_creacion) VALUES (?, ?, ?, ?, ?)");
                        $stmtEntrega->execute([$idVenta, $idChofer, $codigoGuia, $fechaEntrega, getCurrentUserId()]);
                    }
                }
            } catch (PDOException $e) {
                throw $e; 
            }
            
            // Registrar en auditoría
            registrarAuditoria('ventas', $idVenta, AUDITORIA_INSERT, "Venta registrada: {$totalCantidad} items. Total: " . formatCurrency($totalPrecio));
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Venta registrada exitosamente.",
                'id_venta' => $idVenta
            ];
            
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error al registrar venta: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar la venta: ' . $e->getMessage()];
        }
    }
    
    /**
     * Calcular costo referencial estimado para una venta
     */
    private function calcularCostoReferencia($idCalidad = null) {
        // Manejo especial para bolsas (Insumo)
        if ($idCalidad == 999999) {
            try {
                $stmt = $this->db->query("SELECT AVG(precio_por_kg) FROM compras_bolsas ORDER BY id_compra_bolsa DESC LIMIT 5");
                $precio = (float)$stmt->fetchColumn();
                return $precio > 0 ? round($precio, 2) : 0.00;
            } catch (Exception $e) {
                return 0.00;
            }
        }

        try {
            // 1. Costo promedio de fibra por Bolsa
            // Costo por Kg de fibra (últimos 10 lotes)
            $stmt = $this->db->query("SELECT AVG(precio_por_kg) FROM (SELECT precio_por_kg FROM lotes_fibra WHERE precio_por_kg > 0 ORDER BY id_lote DESC LIMIT 10) as sub");
            $precioFibraKg = (float)$stmt->fetchColumn();
            
            // Rendimiento promedio (Kg por Bolsa) de lotes agotados
            // peso_neto / cantidad_producida_real
            $stmt = $this->db->query("SELECT SUM(peso_neto) / SUM(cantidad_producida_real) as kg_por_bolsa 
                                     FROM lotes_fibra 
                                     WHERE estado = 'agotado' AND cantidad_producida_real > 0 
                                     ORDER BY id_lote DESC LIMIT 20");
            $kgPorBolsa = (float)$stmt->fetchColumn();
            
            if ($kgPorBolsa <= 0) $kgPorBolsa = 1.5; // Fallback estimado
            if ($precioFibraKg <= 0) $precioFibraKg = 10.0; // Fallback
            
            $costoFibraPorBolsa = $precioFibraKg * $kgPorBolsa;
            
            // 2. Costo bolsa plástica
            $stmt = $this->db->query("SELECT AVG(precio_por_kg) FROM compras_bolsas ORDER BY id_compra_bolsa DESC LIMIT 5");
            $precioBolsaKg = (float)$stmt->fetchColumn();
            $costoBolsa = $precioBolsaKg * 0.02; // 0.02 kg por bolsa (factor estándar)
            
            // 3. Mano de obra
            $stmt = $this->db->query("SELECT AVG(tarifa_por_bolsa) FROM usuarios WHERE rol IN ('operador', 'trabajador') AND estado = 'activo'");
            $costoManoObra = (float)$stmt->fetchColumn();
            
            return round($costoFibraPorBolsa + $costoBolsa + $costoManoObra, 2);
        } catch (Exception $e) {
            error_log("Error calculando costo referencia: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Generar código de guía de remisión
     */
    private function generarCodigoGuia() {
        // Stored procedure expects OUT param; keep compatibility but return fallback
        try {
            $stmt = $this->db->query("CALL sp_generar_codigo_guia()");
            $resultado = $stmt->fetch();
            // stored proc implementation may vary; try common keys
            if ($resultado) {
                if (isset($resultado['p_codigo_guia'])) return $resultado['p_codigo_guia'];
                if (isset($resultado['codigo'])) return $resultado['codigo'];
            }
        } catch (Exception $e) {
            // ignore and fallback
        }
        return 'GUIA-' . date('Y-m-d-His');
    }
    
    /**
     * Obtener todas las ventas
     */
    private function getVentas($desde = null, $hasta = null) {
        $sql = "SELECT 
                v.id_venta,
                v.fecha_venta,
                e.codigo_guia AS codigo_guia_remision,
                c.nombre as cliente,
                v.cantidad_vendida AS cantidad,
                v.precio_unitario,
                v.precio_total,
                v.estado_pago,
                u.nombre_completo as vendedor
             FROM ventas v
             LEFT JOIN entregas e ON e.id_venta = v.id_venta
             INNER JOIN clientes c ON v.id_cliente = c.id_cliente
             INNER JOIN usuarios u ON v.usuario_creacion = u.id_usuario";

        $params = [];
        if (!empty($desde) && !empty($hasta)) {
            // Aseguramos formato YYYY-MM-DD al comparar
            $sql .= " WHERE DATE(v.fecha_venta) BETWEEN ? AND ?";
            $params[] = $desde;
            $params[] = $hasta;
        } elseif (!empty($desde)) {
            $sql .= " WHERE DATE(v.fecha_venta) >= ?";
            $params[] = $desde;
        } elseif (!empty($hasta)) {
            $sql .= " WHERE DATE(v.fecha_venta) <= ?";
            $params[] = $hasta;
        }

        $sql .= " ORDER BY v.fecha_venta DESC, v.id_venta DESC";

        if (empty($params)) {
            $stmt = $this->db->query($sql);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

        return $stmt->fetchAll();
    }
    
    /**
     * Obtener venta por ID
     */
    private function getVentaById($idVenta) {
        // Determinar nombre de columna de calidad para el SELECT
        $colCalidad = 'id_calidad_napa';
        try {
            $checkCol = $this->db->query("SHOW COLUMNS FROM ventas LIKE 'id_calidad_producto'");
            if ($checkCol->rowCount() > 0) {
                $colCalidad = 'id_calidad_producto as id_calidad_napa'; // Alias para uniformizar
            }
        } catch (Exception $e) {}

        $stmt = $this->db->prepare(
            "SELECT 
                v.id_venta,
                v.fecha_venta,
                v.cantidad_vendida AS cantidad,
                v.precio_unitario,
                v.precio_total,
                v.costo_unitario_referencia,
                v.costo_unitario_referencia as costo_unitario,
                (v.cantidad_vendida * COALESCE(v.costo_unitario_referencia, 0)) as costo_total,
                v.fecha_creacion as fecha_registro,
                v.observaciones,
                v.estado_pago,
                v.$colCalidad,
                c.nombre as cliente,
                c.ruc as cliente_ruc,
                c.direccion as cliente_direccion,
                c.telefono as cliente_telefono,
                u.nombre_completo as vendedor,
                e.codigo_guia AS codigo_guia_remision
             FROM ventas v
             LEFT JOIN entregas e ON e.id_venta = v.id_venta
             INNER JOIN clientes c ON v.id_cliente = c.id_cliente
             INNER JOIN usuarios u ON v.usuario_creacion = u.id_usuario
             WHERE v.id_venta = ?"
        );
        
        $stmt->execute([$idVenta]);
        return $stmt->fetch();
    }
    
    /**
     * Extraer detalles de venta desde observaciones (JSON)
     */
    private function extraerDetallesVenta($venta) {
        $obs = $venta['observaciones'];
        $parts = explode('[DETALLE_SISTEMA]', $obs);
        
        if (count($parts) > 1) {
            $json = trim($parts[1]);
            $data = json_decode($json, true);
            if (is_array($data)) return $data;
        }
        
        // Fallback para ventas antiguas
        return [[
            'nombre_producto' => 'Bolsas de Napa',
            'codigo_producto' => '',
            'cantidad' => $venta['cantidad'],
            'precio' => $venta['precio_unitario'],
            'subtotal' => $venta['precio_total']
        ]];
    }
    
    /**
     * Obtener clientes activos
     */
    private function getClientes() {
        $stmt = $this->db->query(
            "SELECT id_cliente, nombre, ruc, telefono
             FROM clientes
             WHERE estado = 'activo'
             ORDER BY nombre"
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener stock disponible por calidad
     */
    private function getStockPorCalidad($idCalidad) {
        if ($idCalidad == 999999) { // Bolsas Plásticas
            $stmt = $this->db->query("SELECT cantidad FROM inventario WHERE tipo_item = 'bolsas_plasticas' LIMIT 1");
            $row = $stmt->fetch();
            return $row ? (float)$row['cantidad'] : 0;
        }

        $stmt = $this->db->prepare(
            "SELECT cantidad
             FROM inventario
             WHERE tipo_item = ? AND id_calidad_napa = ?
             LIMIT 1"
        );
        
        $stmt->execute([INVENTARIO_PRODUCTO_TERMINADO, $idCalidad]);
        $row = $stmt->fetch();
        return $row ? (float)$row['cantidad'] : 0;
    }
    
    /**
     * Obtener choferes activos
     */
    private function getChoferes() {
        $stmt = $this->db->query(
            "SELECT id_chofer, nombre_completo, vehiculo
             FROM choferes
             WHERE estado = 'activo'
             ORDER BY nombre_completo"
        );
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener calidades de producto (napa) con stock
     */
    private function getCalidadesConStock() {
        $resultados = [];
        try {
            // Intentar usar vista si existe
            $stmt = $this->db->query(
                "SELECT * FROM v_stock_ventas ORDER BY codigo"
            );
            $resultados = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Vista no existe - consultar directamente
            try {
                // Verificar si existe tabla calidades_napa
                $checkTable = $this->db->query("SHOW TABLES LIKE 'calidades_napa'");
                if ($checkTable->rowCount() > 0) {
                    $stmt = $this->db->query(
                        "SELECT 
                            cn.id_calidad_napa as id_calidad,
                            cn.codigo,
                            cn.nombre as calidad_napa,
                            cn.precio_base_sugerido as precio,
                            COALESCE(
                                (SELECT SUM(cantidad) 
                                 FROM inventario 
                                 WHERE tipo_item = 'producto_terminado' 
                                 AND (id_calidad_napa = cn.id_calidad_napa OR id_calidad_napa IS NULL)
                                ), 0
                            ) as stock_disponible,
                            CASE 
                                WHEN COALESCE(
                                    (SELECT SUM(cantidad) FROM inventario 
                                     WHERE tipo_item = 'producto_terminado' 
                                     AND (id_calidad_napa = cn.id_calidad_napa OR id_calidad_napa IS NULL)
                                    ), 0
                                ) > 50 THEN 'DISPONIBLE'
                                WHEN COALESCE(
                                    (SELECT SUM(cantidad) FROM inventario 
                                     WHERE tipo_item = 'producto_terminado' 
                                     AND (id_calidad_napa = cn.id_calidad_napa OR id_calidad_napa IS NULL)
                                    ), 0
                                ) > 0 THEN 'BAJO STOCK'
                                ELSE 'SIN STOCK'
                            END as estado_stock
                         FROM calidades_napa cn
                         WHERE cn.estado = 'activo'
                         HAVING stock_disponible > 0
                         ORDER BY cn.codigo"
                    );
                    $resultados = $stmt->fetchAll();
                } else {
                    // No hay tabla de calidades - retornar stock general
                    $stmt = $this->db->query(
                        "SELECT 
                            1 as id_calidad,
                            'GEN' as codigo,
                            'General' as calidad_napa,
                            0 as precio,
                            COALESCE(cantidad, 0) as stock_disponible,
                            'DISPONIBLE' as estado_stock
                         FROM inventario 
                         WHERE tipo_item = 'producto_terminado'
                         LIMIT 1"
                    );
                    $res = $stmt->fetchAll();
                    $resultados = $res ? $res : [];
                }
            } catch (PDOException $e2) {
                error_log("Error al obtener calidades con stock: " . $e2->getMessage());
                $resultados = [];
            }
        }

        // Agregar Bolsas Plásticas (Insumo)
        $stockBolsas = $this->getStockPorCalidad(999999);
        if ($stockBolsas > 0) {
            $resultados[] = [
                'id_calidad' => 999999,
                'codigo' => 'INS-BOLSA',
                'calidad_napa' => 'Bolsas Plásticas (Kg)',
                'precio' => 0.00, // Precio abierto
                'stock_disponible' => $stockBolsas,
                'estado_stock' => 'DISPONIBLE'
            ];
        }

        return $resultados;
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
