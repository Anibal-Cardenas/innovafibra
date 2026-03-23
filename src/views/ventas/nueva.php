<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container pb-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-shopping-cart text-primary"></i> Nueva Venta</h2>
            <p class="text-muted mb-0">Registrar salida de productos y generar documentación</p>
        </div>
        <a href="<?= BASE_URL ?>/ventas" class="btn btn-outline-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-danger border-start border-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
            <div><?= h($error) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="<?= BASE_URL ?>/ventas/nueva" id="formVenta" autocomplete="off">
        <?= csrfField() ?>
        
        <div class="row g-4">
            <!-- Columna Izquierda: Datos del Formulario -->
            <div class="col-lg-8">
                
                <!-- SECCIÓN 1: CLIENTE -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-2 border-bottom">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-user-circle me-2"></i>Información del Cliente</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="fecha_venta" class="form-label fw-bold text-secondary small text-uppercase">Fecha de Emisión</label>
                                <input type="date" class="form-control" id="fecha_venta" name="fecha_venta" 
                                       value="<?= isset($old['fecha_venta']) ? h($old['fecha_venta']) : date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="col-md-8">
                                <label for="id_cliente" class="form-label fw-bold text-secondary small text-uppercase">Cliente</label>
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <select class="form-select" id="id_cliente" name="id_cliente">
                                            <option value="">-- Público General (Sin datos) --</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['id_cliente'] ?>" <?= (isset($old['id_cliente']) && $old['id_cliente'] == $cliente['id_cliente']) ? 'selected' : '' ?>>
                                                <?= h($cliente['nombre']) ?><?= !empty($cliente['ruc']) ? ' - RUC: ' . h($cliente['ruc']) : '' ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button class="btn btn-outline-primary" type="button" id="btnToggleNewClient" title="Nuevo Cliente">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario Cliente Rápido (Oculto por defecto) -->
                        <div id="nuevo_cliente_container" class="mt-3 p-4 bg-light rounded-3 border border-primary border-opacity-25 position-relative" style="display: none;">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" id="btnCloseNewClient" aria-label="Cerrar"></button>
                            
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-magic me-2"></i>Registro Rápido de Cliente</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted fw-bold">Nombre / Razón Social *</label>
                                    <input type="text" class="form-control" id="nuevo_cliente_nombre" name="nuevo_cliente_nombre" placeholder="Ej: Distribuidora SAC" maxlength="200">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted fw-bold">RUC / DNI</label>
                                    <input type="text" class="form-control" name="nuevo_cliente_ruc" placeholder="Ej: 20123456789" maxlength="20">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted fw-bold">Teléfono</label>
                                    <input type="text" class="form-control" name="nuevo_cliente_telefono" placeholder="Ej: 999888777" maxlength="20">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small text-muted fw-bold">Dirección (Para Guía)</label>
                                    <input type="text" class="form-control" name="nuevo_cliente_direccion" placeholder="Dirección completa de entrega" maxlength="255">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: PRODUCTO -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-2 border-bottom">
                        <h5 class="mb-0 text-success fw-bold"><i class="fas fa-box-open me-2"></i>Detalle del Producto</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tablaProductos">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Producto / Calidad</th>
                                        <th style="width: 20%;">Cantidad</th>
                                        <th style="width: 20%;">P. Unitario</th>
                                        <th style="width: 15%;">Subtotal</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="contenedorProductos">
                                    <!-- Las filas se agregarán aquí dinámicamente -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="p-3">
                                            <button type="button" class="btn btn-outline-success w-100 border-dashed" id="btnAgregarProducto">
                                                <i class="fas fa-plus-circle me-2"></i> Agregar Producto
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: LOGÍSTICA -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-2 border-bottom">
                        <h5 class="mb-0 text-info fw-bold"><i class="fas fa-truck me-2"></i>Logística de Entrega</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="id_chofer" class="form-label fw-bold text-secondary small text-uppercase">Chofer Asignado *</label>
                                <select class="form-select" id="id_chofer" name="id_chofer" required>
                                    <option value="">Seleccione chofer...</option>
                                    <?php foreach ($choferes as $chofer): ?>
                                    <option value="<?= $chofer['id_chofer'] ?>" <?= (isset($old['id_chofer']) && $old['id_chofer'] == $chofer['id_chofer']) ? 'selected' : '' ?>>
                                        <?= h($chofer['nombre_completo']) ?> (<?= h($chofer['vehiculo'] ?: 'Sin vehículo') ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="fecha_entrega_estimada" class="form-label fw-bold text-secondary small text-uppercase">Fecha Entrega Estimada *</label>
                                <input type="date" class="form-control" id="fecha_entrega_estimada" name="fecha_entrega_estimada" 
                                       value="<?= isset($old['fecha_entrega_estimada']) ? h($old['fecha_entrega_estimada']) : date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="observaciones" class="form-label fw-bold text-secondary small text-uppercase">Observaciones / Notas</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="2" placeholder="Instrucciones especiales para el chofer o detalles de la venta..."><?= isset($old['observaciones']) ? h($old['observaciones']) : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: Resumen Sticky -->
            <div class="col-lg-4">
                <div class="card shadow border-0 sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-primary text-white py-2 text-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-calculator me-2"></i>RESUMEN DE VENTA</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="text-center mb-3">
                            <small class="text-muted text-uppercase fw-bold">Total a Pagar</small>
                            <h2 class="display-6 fw-bold text-primary mb-0" id="precioTotal">S/ 0.00</h2>
                        </div>

                        <!-- Panel de Rentabilidad (Se muestra al calcular) -->
                        <div class="bg-light rounded-3 p-3 mb-4 border" id="wrapCostos" style="display: none;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Costo Unitario Ref:</span>
                                <span class="fw-bold text-dark" id="costoUnitario">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Costo Total:</span>
                                <span class="fw-bold text-dark" id="costoTotal">S/ 0.00</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 shadow-sm fw-bold">
                                <i class="fas fa-check-circle me-2"></i> CONFIRMAR VENTA
                            </button>
                            <a href="<?= BASE_URL ?>/ventas" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    
    // Inicializar Select2 para clientes
    $('#id_cliente').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar cliente o Público General',
        allowClear: true
    });

    // --- LÓGICA DE CLIENTE NUEVO (UX MEJORADA) ---
    
    // Mostrar formulario de nuevo cliente
    $('#btnToggleNewClient').on('click', function() {
        $('#nuevo_cliente_container').slideDown();
        $('#id_cliente').val(null).trigger('change'); // Limpiar selección
        $('#id_cliente').prop('disabled', true); // Deshabilitar select
        $('#nuevo_cliente_nombre').focus();
        $(this).addClass('active');
    });

    // Cerrar formulario de nuevo cliente
    $('#btnCloseNewClient').on('click', function() {
        $('#nuevo_cliente_container').slideUp();
        $('#nuevo_cliente_container input').val(''); // Limpiar inputs
        $('#id_cliente').prop('disabled', false); // Habilitar select
        $('#btnToggleNewClient').removeClass('active');
    });

    // Si selecciona un cliente del select, asegurar que el form nuevo esté oculto
    $('#id_cliente').on('change', function() {
        if($(this).val()) {
            $('#nuevo_cliente_container').slideUp();
            $('#nuevo_cliente_container input').val('');
        }
    });

    // --- LÓGICA DE PRODUCTO Y PRECIOS ---

    // Template de opciones de producto (se genera una vez)
    const opcionesProducto = `
        <option value="">Seleccione producto...</option>
        <?php if (empty($calidades_producto)): ?>
            <option value="" disabled>No hay stock disponible</option>
        <?php else: ?>
            <?php foreach ($calidades_producto as $cal): 
                $id_calidad = isset($cal['id_calidad_producto']) ? $cal['id_calidad_producto'] : (isset($cal['id_calidad_napa']) ? $cal['id_calidad_napa'] : (isset($cal['id_calidad']) ? $cal['id_calidad'] : ''));
                $precio_base = isset($cal['precio_base_sugerido']) ? $cal['precio_base_sugerido'] : (isset($cal['precio']) ? $cal['precio'] : 0);
                $codigo = isset($cal['codigo']) ? $cal['codigo'] : '';
                $nombre = isset($cal['nombre']) ? $cal['nombre'] : (isset($cal['calidad_napa']) ? $cal['calidad_napa'] : '');
                $stock = isset($cal['stock_disponible']) ? (float)$cal['stock_disponible'] : 0;
            ?>
            <option value="<?= h($id_calidad) ?>" 
                    data-precio="<?= h($precio_base) ?>"
                    data-stock="<?= h($stock) ?>"
                    data-nombre="<?= h($nombre) ?>"
                    data-codigo="<?= h($codigo) ?>">
                <?= h($nombre) ?> (<?= h($codigo) ?>) - Stock: <?= number_format($stock, 2) ?>
            </option>
            <?php endforeach; ?>
        <?php endif; ?>
    `;

    function agregarFilaProducto() {
        const rowId = Date.now();
        const row = `
            <tr class="producto-row" id="row_${rowId}">
                <td>
                    <select class="form-select form-select-sm select-producto" name="productos[${rowId}][id_calidad]" required>
                        ${opcionesProducto}
                    </select>
                    <input type="hidden" name="productos[${rowId}][nombre_producto]" class="input-nombre-producto">
                    <input type="hidden" name="productos[${rowId}][codigo_producto]" class="input-codigo-producto">
                    <div class="small text-muted mt-1 info-stock"></div>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm input-cantidad" name="productos[${rowId}][cantidad]" min="0.01" step="0.01" placeholder="0.00" required disabled>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">S/</span>
                        <input type="number" class="form-control input-precio" name="productos[${rowId}][precio]" step="0.01" min="0.01" placeholder="0.00" required disabled>
                    </div>
                </td>
                <td class="text-end align-middle fw-bold text-primary subtotal-display">S/ 0.00</td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#contenedorProductos').append(row);
    }

    // Agregar primera fila por defecto
    agregarFilaProducto();

    // Evento agregar fila
    $('#btnAgregarProducto').on('click', agregarFilaProducto);

    // Evento eliminar fila
    $(document).on('click', '.btn-eliminar-fila', function() {
        if ($('.producto-row').length > 1) {
            $(this).closest('tr').remove();
            calcularTotales();
        } else {
            alert('Debe haber al menos un producto.');
        }
    });

    // Evento cambio de producto
    $(document).on('change', '.select-producto', function() {
        const $row = $(this).closest('tr');
        const $selected = $(this).find(':selected');
        const precioSugerido = $selected.data('precio');
        const stock = parseFloat($selected.data('stock')) || 0;
        const nombre = $selected.data('nombre');
        const codigo = $selected.data('codigo');
        const idCalidad = $(this).val(); // Obtener ID seleccionado
        
        // Guardar datos ocultos para el backend
        $row.find('.input-nombre-producto').val(nombre);
        $row.find('.input-codigo-producto').val(codigo);
        
        // Configurar input cantidad según tipo de producto
        const $inputCant = $row.find('.input-cantidad');
        $inputCant.data('max-stock', stock);
        
        if (idCalidad == '999999') {
            // Es Bolsa (Insumo) -> Permite decimales
            $inputCant.attr('step', '0.01');
            $inputCant.attr('min', '0.01');
            $inputCant.attr('placeholder', '0.00');
        } else {
            // Es Napa (Producto) -> Solo enteros
            $inputCant.attr('step', '1');
            $inputCant.attr('min', '1');
            $inputCant.attr('placeholder', '0');
            // Si ya tenía un valor decimal, redondearlo o limpiar
            if ($inputCant.val() && $inputCant.val() % 1 !== 0) {
                $inputCant.val(Math.floor($inputCant.val()));
            }
        }
        
        // Actualizar UI de stock
        const $infoStock = $row.find('.info-stock');
        if (stock > 0) {
            $infoStock.html(`<span class="text-success"><i class="fas fa-check-circle"></i> Stock: ${stock.toFixed(2)}</span>`);
            $inputCant.prop('disabled', false).attr('max', stock).attr('placeholder', idCalidad == '999999' ? 'Máx: ' + stock.toFixed(2) : 'Máx: ' + Math.floor(stock));
            $row.find('.input-precio').prop('disabled', false);
        } else {
            $infoStock.html(`<span class="text-danger"><i class="fas fa-times-circle"></i> Sin Stock</span>`);
            $inputCant.prop('disabled', true).val('');
            $row.find('.input-precio').prop('disabled', true);
        }
        
        calcularTotales();
    });
    
    // Evento cambio de cantidad o precio
    $(document).on('input change', '.input-cantidad, .input-precio', calcularTotales);
    
    function calcularTotales() {
        let totalVenta = 0;
        
        $('.producto-row').each(function() {
            const cantidad = parseFloat($(this).find('.input-cantidad').val()) || 0;
            const precio = parseFloat($(this).find('.input-precio').val()) || 0;
            const subtotal = cantidad * precio;
            
            $(this).find('.subtotal-display').text(formatCurrency(subtotal));
            totalVenta += subtotal;
        });
        
        $('#precioTotal').text(formatCurrency(totalVenta));
        // Ocultar panel de costos detallado por ahora
        $('#wrapCostos').slideUp();
    }
    
    // Validación final antes de enviar
    $('#formVenta').on('submit', function(e) {
        const idCliente = $('#id_cliente').val();
        const nuevoCliente = $('#nuevo_cliente_nombre').val().trim();
        
        // Validar stocks por fila
        let stockError = false;
        $('.producto-row').each(function() {
            const cantidad = parseFloat($(this).find('.input-cantidad').val()) || 0;
            const maxStock = parseFloat($(this).find('.input-cantidad').data('max-stock')) || 0;
            if (cantidad > maxStock) {
                stockError = true;
                $(this).find('.input-cantidad').addClass('is-invalid').focus();
            }
        });
        
        if (stockError) {
            e.preventDefault();
            alert('⚠️ Una o más cantidades superan el stock disponible.');
            return false;
        }
        
        return true;
    });
});

function formatCurrency(amount) {
    return 'S/ ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
