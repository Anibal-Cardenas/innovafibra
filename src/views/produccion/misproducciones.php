<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-check"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/produccion/nueva" class="btn btn-primary">
            <i class="fas fa-plus"></i> Registrar Producción
        </a>
    </div>
    
    <?php if (empty($producciones)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No tiene producciones registradas aún.
    </div>
    <?php else: ?>
    
    <!-- Resumen -->
    <div class="row mb-4">
        <?php
        $totalBolsas = 0;
        $totalPagar = 0;
        $aprobadas = 0;
        $pendientes = 0;
        $rechazadas = 0;
        
        foreach ($producciones as $p) {
            $totalBolsas += $p['cantidad_producida'];
            $totalPagar += $p['monto_pagar'];
            if ($p['estado_validacion'] === 'aprobado') $aprobadas++;
            elseif ($p['estado_validacion'] === 'pendiente') $pendientes++;
            elseif ($p['estado_validacion'] === 'rechazado') $rechazadas++;
        }
        ?>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Producido</h5>
                    <h2><?= number_format($totalBolsas) ?></h2>
                    <small>bolsas</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Aprobadas</h5>
                    <h2><?= $aprobadas ?></h2>
                    <small>producciones</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5>Pendientes</h5>
                    <h2><?= $pendientes ?></h2>
                    <small>producciones</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total a Cobrar</h5>
                    <h2><?= formatCurrency($totalPagar) ?></h2>
                    <small>solo aprobadas</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Lote</th>
                            <th>Peso fardo (kg)</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-center">Eficiencia</th>
                            <th class="text-center">Merma</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Tarifa</th>
                            <th class="text-end">A Pagar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($producciones as $prod): ?>
                        <tr class="<?= $prod['flag_merma_excesiva'] ? 'table-warning' : '' ?>">
                            <td><?= formatDate($prod['fecha_produccion']) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= h($prod['codigo_lote']) ?></span>
                            </td>
                            <td class="text-end">
                                <?= isset($prod['peso_fardo']) && $prod['peso_fardo'] > 0 ? number_format($prod['peso_fardo'], 2) : '-' ?>
                            </td>
                            <td class="text-end"><?= number_format($prod['cantidad_producida']) ?></td>
                            <td class="text-center">
                                <?php
                                $eficiencia = $prod['eficiencia_porcentual'];
                                $clase = $eficiencia >= 95 ? 'eficiencia-alta' : 
                                        ($eficiencia >= 85 ? 'eficiencia-media' : 'eficiencia-baja');
                                ?>
                                <span class="<?= $clase ?>"><?= formatDecimal($eficiencia) ?>%</span>
                            </td>
                            <td class="text-center">
                                <?php if ($prod['flag_merma_excesiva']): ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-success">OK</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $badgeClass = '';
                                $estadoTexto = '';
                                switch ($prod['estado_validacion']) {
                                    case 'pendiente':
                                        $badgeClass = 'bg-warning text-dark';
                                        $estadoTexto = 'Pendiente';
                                        break;
                                    case 'aprobado':
                                        $badgeClass = 'bg-success';
                                        $estadoTexto = 'Aprobado';
                                        break;
                                    case 'rechazado':
                                        $badgeClass = 'bg-danger';
                                        $estadoTexto = 'Rechazado';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $estadoTexto ?>
                                </span>
                            </td>
                            <td class="text-end"><?= formatCurrency($prod['tarifa_por_bolsa']) ?></td>
                            <td class="text-end">
                                <strong><?= formatCurrency($prod['monto_pagar']) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <td colspan="7" class="text-end"><strong>TOTAL A COBRAR:</strong></td>
                            <td class="text-end"><strong><?= formatCurrency($totalPagar) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
