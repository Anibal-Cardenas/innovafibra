<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-line"></i> <?= h($title) ?></h2>
        <button onclick="window.print()" class="btn btn-secondary no-print">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
    
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reportes/produccion" class="row g-3">
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
                    <h6>Total Producciones</h6>
                    <h3><?= formatInteger($resumen['total_producciones']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Producido</h6>
                    <h3><?= formatInteger($resumen['total_producido']) ?></h3>
                    <small>bolsas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Eficiencia Promedio</h6>
                    <h3><?= formatDecimal($resumen['eficiencia_promedio']) ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Aprobadas</h6>
                    <h3><?= formatInteger($resumen['aprobadas']) ?></h3>
                    <small>de <?= formatInteger($resumen['total_producciones']) ?></small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Producción por Día</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($produccion_diaria)): ?>
                    <p class="text-muted">No hay datos</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th class="text-end">Producido</th>
                                    <th class="text-center">Eficiencia</th>
                                    <th class="text-center">Con Merma</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produccion_diaria as $dia): ?>
                                <tr>
                                    <td><?= formatDate($dia['fecha_produccion']) ?></td>
                                    <td class="text-end"><?= formatInteger($dia['total_producido']) ?></td>
                                    <td class="text-center"><?= formatDecimal($dia['eficiencia_promedio']) ?>%</td>
                                    <td class="text-center">
                                        <?php if ($dia['con_merma'] > 0): ?>
                                        <span class="badge bg-danger"><?= $dia['con_merma'] ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Producción por Operario</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($produccion_operarios)): ?>
                    <p class="text-muted">No hay datos</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Operario</th>
                                    <th class="text-end">Producido</th>
                                    <th class="text-center">Eficiencia</th>
                                    <th class="text-end">A Pagar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produccion_operarios as $op): ?>
                                <tr>
                                    <td><?= h($op['nombre_completo']) ?></td>
                                    <td class="text-end"><?= formatInteger($op['total_producido']) ?></td>
                                    <td class="text-center"><?= formatDecimal($op['eficiencia_promedio']) ?>%</td>
                                    <td class="text-end"><?= formatCurrency($op['total_pagar']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
