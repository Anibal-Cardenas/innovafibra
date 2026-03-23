<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="<?= BASE_URL ?>/comisiones" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h2><i class="fas fa-file-invoice-dollar"></i> Detalle de Comisión #<?= $comision['id_comision'] ?></h2>
        </div>
    </div>

    <!-- Información de la Comisión -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Información General</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Operador:</th>
                            <td><?= h($comision['nombre_completo']) ?></td>
                        </tr>
                        <tr>
                            <th>Usuario:</th>
                            <td><?= h($comision['username']) ?></td>
                        </tr>
                        <?php if ($comision['dni']): ?>
                        <tr>
                            <th>DNI:</th>
                            <td><?= h($comision['dni']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Periodo:</th>
                            <td>
                                <?= date('d/m/Y', strtotime($comision['fecha_inicio'])) ?> - 
                                <?= date('d/m/Y', strtotime($comision['fecha_fin'])) ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Total Bolsas:</th>
                            <td class="fw-bold"><?= number_format($comision['total_bolsas_producidas']) ?> bolsas</td>
                        </tr>
                        <tr>
                            <th>Tarifa Aplicada:</th>
                            <td>S/ <?= number_format($comision['tarifa_aplicada'], 2) ?> por bolsa</td>
                        </tr>
                        <tr>
                            <th>Comisión Base:</th>
                            <td>S/ <?= number_format($comision['monto_comision'], 2) ?></td>
                        </tr>
                        <?php if ($comision['monto_bonificacion'] > 0): ?>
                        <tr>
                            <th>Bonificación:</th>
                            <td class="text-success">+ S/ <?= number_format($comision['monto_bonificacion'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($comision['monto_descuento'] > 0): ?>
                        <tr>
                            <th>Descuentos:</th>
                            <td class="text-danger">- S/ <?= number_format($comision['monto_descuento'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="table-light">
                            <th>TOTAL A PAGAR:</th>
                            <td class="h5 text-success fw-bold">S/ <?= number_format($comision['monto_total'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-<?= $comision['estado'] == 'pagado' ? 'success' : 
                                              ($comision['estado'] == 'anulado' ? 'danger' : 'warning') ?>">
                        <strong>Estado:</strong> <?= ucfirst($comision['estado']) ?>
                        
                        <?php if ($comision['estado'] == 'pagado'): ?>
                            <br><strong>Fecha de Pago:</strong> <?= date('d/m/Y', strtotime($comision['fecha_pago'])) ?>
                            <br><strong>Método:</strong> <?= ucfirst($comision['metodo_pago']) ?>
                            <?php if ($comision['numero_operacion']): ?>
                                <br><strong>Nº Operación:</strong> <?= h($comision['numero_operacion']) ?>
                            <?php endif; ?>
                            <?php if ($comision['pagado_por']): ?>
                                <br><strong>Pagado por:</strong> <?= h($comision['pagado_por']) ?>
                            <?php endif; ?>
                        <?php elseif ($comision['estado'] == 'calculado'): ?>
                            <br><strong>Calculado el:</strong> <?= date('d/m/Y H:i', strtotime($comision['fecha_calculo'])) ?>
                            <?php if ($comision['calculado_por']): ?>
                                <br><strong>Calculado por:</strong> <?= h($comision['calculado_por']) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($comision['observaciones']): ?>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <strong>Observaciones:</strong><br>
                        <?= nl2br(h($comision['observaciones'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detalle de Producciones -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Producciones Incluidas en esta Comisión</h5>
        </div>
        <div class="card-body">
            <?php if (empty($detalle)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No hay producciones asociadas a esta comisión.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>ID Producción</th>
                                <th>Fecha</th>
                                <th class="text-center">Cantidad Bolsas</th>
                                <th class="text-center">Tarifa</th>
                                <th class="text-end">Subtotal</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalBolsas = 0;
                            $totalMonto = 0;
                            foreach ($detalle as $item): 
                                $totalBolsas += $item['cantidad_bolsas'];
                                $totalMonto += $item['subtotal'];
                            ?>
                            <tr>
                                <td>#<?= $item['id_produccion'] ?></td>
                                <td><?= date('d/m/Y', strtotime($item['fecha_produccion'])) ?></td>
                                <td class="text-center"><?= number_format($item['cantidad_bolsas']) ?></td>
                                <td class="text-center">S/ <?= number_format($item['tarifa_por_bolsa'], 2) ?></td>
                                <td class="text-end">S/ <?= number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <?php if ($item['observaciones']): ?>
                                        <small class="text-muted"><?= h($item['observaciones']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">TOTALES:</td>
                                <td class="text-center"><?= number_format($totalBolsas) ?></td>
                                <td></td>
                                <td class="text-end">S/ <?= number_format($totalMonto, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Acciones -->
    <?php if (isAdmin() && $comision['estado'] != 'pagado' && $comision['estado'] != 'anulado'): ?>
    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-success btn-lg w-100" onclick="registrarPago()">
                        <i class="fas fa-dollar-sign"></i> Registrar Pago
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-danger btn-lg w-100" onclick="anularComision()">
                        <i class="fas fa-times"></i> Anular Comisión
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function registrarPago() {
    window.location.href = '<?= BASE_URL ?>/comisiones#pago-<?= $comision['id_comision'] ?>';
}

function anularComision() {
    const motivo = prompt('Ingrese el motivo de la anulación:');
    
    if (motivo && motivo.trim() !== '') {
        if (confirm('¿Está seguro de anular esta comisión?')) {
            $.ajax({
                url: '<?= BASE_URL ?>/comisiones/anular',
                method: 'POST',
                data: { 
                    id_comision: <?= $comision['id_comision'] ?>, 
                    motivo: motivo 
                },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    if (response.success) {
                        window.location.href = '<?= BASE_URL ?>/comisiones';
                    }
                }
            });
        }
    }
}
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
