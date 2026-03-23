<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-eye"></i> <?= h($title) ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/ventas/guia?id=<?= $venta['id_venta'] ?>" 
               class="btn btn-secondary me-2"
               target="_blank">
                <i class="fas fa-file-pdf"></i> Orden de Salida
            </a>
            <a href="<?= BASE_URL ?>/ventas/comprobante?id=<?= $venta['id_venta'] ?>" 
               class="btn btn-success me-2"
               target="_blank">
                <i class="fas fa-print"></i> Imprimir Ticket
            </a>
            <a href="<?= BASE_URL ?>/ventas" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información de la Venta #<?= $venta['id_venta'] ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Fecha de Venta:</strong><br>
                            <?= formatDate($venta['fecha_venta']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Orden de Salida:</strong><br>
                            <span class="badge bg-info"><?= h($venta['codigo_guia_remision']) ?></span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Datos del Cliente</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nombre/Razón Social:</strong><br>
                            <?= h($venta['cliente']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>RUC:</strong><br>
                            <?= h($venta['cliente_ruc']) ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Dirección:</strong><br>
                            <?= h($venta['cliente_direccion']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Teléfono:</strong><br>
                            <?= h($venta['cliente_telefono']) ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Detalle de Productos</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">P. Unitario</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $det): ?>
                            <tr>
                                <td><?= h($det['nombre_producto']) ?> <span class="badge bg-secondary"><?= h($det['codigo_producto']) ?></span></td>
                                <td class="text-end"><?= number_format($det['cantidad']) ?></td>
                                <td class="text-end"><?= formatCurrency($det['precio']) ?></td>
                                <td class="text-end">
                                    <strong><?= formatCurrency($det['subtotal']) ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($venta['observaciones']): 
                        // Limpiar el JSON oculto para mostrar solo el texto del usuario
                        $obsLimpia = explode('[DETALLE_SISTEMA]', $venta['observaciones'])[0];
                    ?>
                    <?php if (trim($obsLimpia)): ?>
                    <div class="alert alert-info">
                        <strong>Observaciones:</strong><br>
                        <?= nl2br(h($obsLimpia)) ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Análisis de Rentabilidad</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Precio de Venta:</small>
                        <h4 class="text-primary"><?= formatCurrency($venta['precio_total']) ?></h4>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Costo Unitario:</small><br>
                        <strong><?= formatCurrency($venta['costo_unitario']) ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Costo Total:</small><br>
                        <strong><?= formatCurrency($venta['costo_total']) ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Vendedor:</small><br>
                        <strong><?= h($venta['vendedor']) ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Fecha de Registro:</small><br>
                        <?= formatDateTime($venta['fecha_registro']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
