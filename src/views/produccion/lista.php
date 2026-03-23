<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-industry"></i> <?= h($title) ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/produccion/validar" class="btn btn-warning me-2">
                <i class="fas fa-check-circle"></i> Validar Producción
            </a>
            <a href="<?= BASE_URL ?>/produccion/nueva" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Producción
            </a>
        </div>
    </div>
    
    <?php if (empty($producciones)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No hay producciones registradas aún.
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Lote</th>
                            <th>Peso fardo (kg)</th>
                            <th>Operario</th>
                            <th class="text-end">Producido</th>
                            <th class="text-center">Eficiencia</th>
                            <th class="text-center">Merma</th>
                            <th class="text-center">Estado</th>
                            <th>Supervisor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($producciones as $prod): ?>
                        <tr>
                            <td><?= $prod['id_produccion'] ?></td>
                            <td><?= formatDate($prod['fecha_produccion']) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= h($prod['codigo_lote']) ?></span>
                            </td>
                            <td class="text-end">
                                <?= isset($prod['peso_fardo']) && $prod['peso_fardo'] > 0 ? number_format($prod['peso_fardo'], 2) : '-' ?>
                            </td>
                            <td><?= h($prod['operario']) ?></td>
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
                                    <i class="fas fa-exclamation-triangle"></i> SÍ
                                </span>
                                <?php else: ?>
                                <span class="badge bg-success">NO</span>
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
                            <td>
                                <?= $prod['supervisor'] ? h($prod['supervisor']) : '<span class="text-muted">-</span>' ?>
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
