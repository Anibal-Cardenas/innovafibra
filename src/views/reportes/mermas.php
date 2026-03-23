<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-exclamation-triangle"></i> <?= h($title) ?></h2>
        <button onclick="window.print()" class="btn btn-secondary no-print">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
    
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reportes/mermas" class="row g-3">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Desde:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= h($fecha_inicio) ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Hasta:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= h($fecha_fin) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Lotes</h6>
                    <h3><?= formatInteger($resumen['total_lotes']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Eficiencia Promedio</h6>
                    <h3><?= formatDecimal($resumen['eficiencia_promedio']) ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Merma Total</h6>
                    <h3><?= formatInteger($resumen['merma_total']) ?></h3>
                    <small>bolsas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Lotes c/ Merma Excesiva</h6>
                    <h3><?= formatInteger($resumen['lotes_merma_excesiva']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (empty($lotes)): ?>
    <div class="alert alert-info">
        No hay datos de mermas en el período seleccionado.
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Detalle de Lotes con Merma</h5>
            <small>Período: <?= formatDate($fecha_inicio) ?> - <?= formatDate($fecha_fin) ?></small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Fecha</th>
                            <th class="text-end">Peso</th>
                            <th class="text-end">Estimado</th>
                            <th class="text-end">Producido</th>
                            <th class="text-end">Merma</th>
                            <th class="text-center">Eficiencia</th>
                            <th class="text-center">Producciones</th>
                            <th class="text-center">Con Merma</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lotes as $lote): ?>
                        <tr class="<?= $lote['eficiencia'] < 85 ? 'table-danger' : ($lote['eficiencia'] < 95 ? 'table-warning' : '') ?>">
                            <td><strong><?= h($lote['codigo_lote']) ?></strong></td>
                            <td><?= formatDate($lote['fecha_compra']) ?></td>
                            <td class="text-end"><?= formatDecimal($lote['peso_neto']) ?> kg</td>
                            <td class="text-end"><?= formatInteger($lote['cantidad_estimada_bolsas']) ?></td>
                            <td class="text-end"><?= formatInteger($lote['cantidad_producida_real']) ?></td>
                            <td class="text-end">
                                <strong class="text-danger"><?= formatInteger($lote['merma']) ?></strong>
                            </td>
                            <td class="text-center">
                                <?php
                                $clase = $lote['eficiencia'] >= 95 ? 'eficiencia-alta' : 
                                        ($lote['eficiencia'] >= 85 ? 'eficiencia-media' : 'eficiencia-baja');
                                ?>
                                <span class="<?= $clase ?>"><?= formatDecimal($lote['eficiencia']) ?>%</span>
                            </td>
                            <td class="text-center"><?= $lote['num_producciones'] ?></td>
                            <td class="text-center">
                                <?php if ($lote['producciones_con_merma'] > 0): ?>
                                <span class="badge bg-danger"><?= $lote['producciones_con_merma'] ?></span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $estadoClase = $lote['estado'] === 'merma_excesiva' ? 'bg-danger' : 'bg-secondary';
                                ?>
                                <span class="badge <?= $estadoClase ?>">
                                    <?= h(ucfirst(str_replace('_', ' ', $lote['estado']))) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
