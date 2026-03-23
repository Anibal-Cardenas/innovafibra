<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-boxes"></i> <?= h($title) ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/compras/nueva-bolsas" class="btn btn-success me-2">
                <i class="fas fa-plus"></i> Comprar Bolsas
            </a>
            <a href="<?= BASE_URL ?>/compras/nueva-fibra" class="btn btn-primary">
                <i class="fas fa-plus"></i> Comprar Fibra
            </a>
        </div>
    </div>
    
    <?php if (empty($lotes)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No hay lotes registrados aún.
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover data-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha Compra</th>
                            <th>Proveedor</th>
                            <th>Calidad</th>
                            <th class="text-center">Fardos</th>
                            <th class="text-end">Peso Neto</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Estimado</th>
                            <th class="text-end">Producido</th>
                            <th class="text-center">Eficiencia</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lotes as $lote): ?>
                        <tr>
                            <td>
                                <strong><?= h($lote['codigo_lote']) ?></strong>
                            </td>
                            <td><?= formatDate($lote['fecha_compra']) ?></td>
                            <td><?= h($lote['proveedor']) ?></td>
                            <td>
                                <?php if (isset($lote['calidad'])): ?>
                                <span class="badge bg-<?= h($lote['calidad_color'] ?? 'secondary') ?>">
                                    <?= h($lote['calidad']) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">
                                    <?= isset($lote['numero_cubos']) ? $lote['numero_cubos'] : '1' ?>
                                </span>
                            </td>
                            <td class="text-end"><?= formatDecimal($lote['peso_neto']) ?> kg</td>
                            <td class="text-end"><?= formatCurrency($lote['precio_total']) ?></td>
                            <td class="text-end"><?= number_format($lote['cantidad_estimada_bolsas']) ?></td>
                            <td class="text-end"><?= number_format($lote['cantidad_producida_real']) ?></td>
                            <td class="text-center">
                                <?php
                                $eficiencia = 0;
                                if ($lote['cantidad_estimada_bolsas'] > 0) {
                                    $eficiencia = ($lote['cantidad_producida_real'] / $lote['cantidad_estimada_bolsas']) * 100;
                                }
                                $clase = $eficiencia >= 95 ? 'eficiencia-alta' : 
                                        ($eficiencia >= 85 ? 'eficiencia-media' : 'eficiencia-baja');
                                ?>
                                <span class="<?= $clase ?>">
                                    <?= formatDecimal($eficiencia) ?>%
                                </span>
                            </td>
                            <td class="text-center">
                                <?php
                                $badgeClass = '';
                                $estadoTexto = '';
                                switch ($lote['estado']) {
                                    case 'disponible':
                                        $badgeClass = 'bg-success';
                                        $estadoTexto = 'Disponible';
                                        break;
                                    case 'en_proceso':
                                        $badgeClass = 'bg-info';
                                        $estadoTexto = 'En Proceso';
                                        break;
                                    case 'agotado':
                                        $badgeClass = 'bg-secondary';
                                        $estadoTexto = 'Agotado';
                                        break;
                                    case 'merma_excesiva':
                                        $badgeClass = 'bg-danger';
                                        $estadoTexto = 'Merma Excesiva';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $estadoTexto ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/compras/detalle-lote/<?= $lote['id_lote'] ?>" class="btn btn-sm btn-info text-white" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
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
