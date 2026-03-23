<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-box"></i> <?= h($title) ?>
        </h1>
        <a href="<?= BASE_URL ?>/compras/lotes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <!-- Información General del Lote -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <h5 class="card-title text-primary fw-bold mb-3">Información General</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Código:</span>
                            <span><?= h($lote['codigo_lote']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Proveedor:</span>
                            <span><?= h($lote['proveedor']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Fecha Compra:</span>
                            <span><?= formatDate($lote['fecha_compra']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Calidad:</span>
                            <span class="badge bg-<?= h($lote['calidad_color'] ?? 'secondary') ?>">
                                <?= h($lote['calidad']) ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Estado:</span>
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
                            <span class="badge <?= $badgeClass ?>"><?= $estadoTexto ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">N° Guía:</span>
                            <span><?= h($lote['numero_guia'] ?? '-') ?></span>
                        </li>
                        <li class="list-group-item">
                            <span class="fw-bold d-block mb-1">Observaciones:</span>
                            <small class="text-muted"><?= h($lote['observaciones'] ?? 'Ninguna') ?></small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Métricas y Costos -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <h5 class="card-title text-success fw-bold mb-3">Métricas y Costos</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Fardos (Cubos):</span>
                            <span><?= h($lote['numero_cubos']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Peso Neto Total:</span>
                            <span><?= formatDecimal($lote['peso_neto']) ?> kg</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Precio Total:</span>
                            <span><?= formatCurrency($lote['precio_total']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Precio por Kg:</span>
                            <span><?= formatCurrency($lote['precio_por_kg']) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Producción y Rendimiento -->
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <h5 class="card-title text-info fw-bold mb-3">Producción y Rendimiento</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Estimado (Bolsas):</span>
                            <span><?= number_format($lote['cantidad_estimada_bolsas']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Producido Real:</span>
                            <span><?= number_format($lote['cantidad_producida_real']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Eficiencia:</span>
                            <?php
                                $eficiencia = 0;
                                if ($lote['cantidad_estimada_bolsas'] > 0) {
                                    $eficiencia = ($lote['cantidad_producida_real'] / $lote['cantidad_estimada_bolsas']) * 100;
                                }
                                $clase = $eficiencia >= 95 ? 'text-success' : ($eficiencia >= 85 ? 'text-warning' : 'text-danger');
                            ?>
                            <span class="fw-bold <?= $clase ?>"><?= formatDecimal($eficiencia) ?>%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Rendimiento Est.:</span>
                            <span><?= formatDecimal($lote['rendimiento_estimado']) ?> bols/kg</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Rendimiento Real:</span>
                            <?php
                                $rendimientoReal = ($lote['peso_neto'] > 0) ? ($lote['cantidad_producida_real'] / $lote['peso_neto']) : 0;
                            ?>
                            <span><?= formatDecimal($rendimientoReal) ?> bols/kg</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Fardos (Cubos) -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cubes"></i> Detalle de Fardos (Cubos)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTableCubos" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center"># Cubo</th>
                            <th class="text-end">Peso Neto (kg)</th>
                            <th class="text-end">Estimado (Bolsas)</th>
                            <th class="text-end">Producido (Bolsas)</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cubos)): ?>
                            <?php foreach ($cubos as $cubo): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $cubo['numero_cubo'] ?></td>
                                <td class="text-end"><?= formatDecimal($cubo['peso_neto']) ?></td>
                                <td class="text-end"><?= number_format($cubo['cantidad_estimada_bolsas']) ?></td>
                                <td class="text-end"><?= number_format($cubo['cantidad_producida_real']) ?></td>
                                <td class="text-center">
                                    <?php
                                        $estadoCuboClass = '';
                                        $estadoCuboTexto = '';
                                        switch ($cubo['estado']) {
                                            case 'disponible':
                                                $estadoCuboClass = 'bg-success';
                                                $estadoCuboTexto = 'Disponible';
                                                break;
                                            case 'procesado':
                                                $estadoCuboClass = 'bg-secondary';
                                                $estadoCuboTexto = 'Procesado';
                                                break;
                                            default:
                                                $estadoCuboClass = 'bg-light text-dark border';
                                                $estadoCuboTexto = ucfirst($cubo['estado']);
                                        }
                                    ?>
                                    <span class="badge <?= $estadoCuboClass ?>"><?= $estadoCuboTexto ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No se encontraron detalles de fardos para este lote.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-active fw-bold">
                            <td class="text-center">TOTALES</td>
                            <td class="text-end"><?= formatDecimal($lote['peso_neto']) ?></td>
                            <td class="text-end"><?= number_format($lote['cantidad_estimada_bolsas']) ?></td>
                            <td class="text-end"><?= number_format($lote['cantidad_producida_real']) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
