<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-money-bill-wave"></i> Mis Comisiones</h2>
            <p class="text-muted">Consulta tu producción y comisiones generadas</p>
        </div>
    </div>

    <?php if (isset($tabla_no_existe) && $tabla_no_existe): ?>
    <!-- Mensaje de instalación requerida -->
    <div class="alert alert-info border-info" style="border-left: 5px solid #0dcaf0;">
        <div class="row align-items-center">
            <div class="col-md-1 text-center">
                <i class="fas fa-info-circle fa-3x text-info"></i>
            </div>
            <div class="col-md-11">
                <h4 class="alert-heading"><i class="fas fa-clock"></i> Sistema de Comisiones en Configuración</h4>
                <p class="mb-3">El sistema de comisiones aún no está activado. Tu administrador necesita completar la instalación para que puedas consultar tus comisiones.</p>
                
                <div class="bg-white p-3 rounded border">
                    <h6 class="text-dark"><i class="fas fa-user-shield"></i> Mensaje para el Administrador:</h6>
                    <p class="mb-2 text-dark">Para activar el sistema de comisiones, ejecuta la migración SQL desde phpMyAdmin:</p>
                    <ol class="mb-0 text-dark">
                        <li>Abre: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></li>
                        <li>Base de datos: <code>sistema_napa</code> → Pestaña "SQL"</li>
                        <li>Ejecuta: <code>database\migrations\implementar_sistema_roles.sql</code></li>
                    </ol>
                </div>
                
                <hr>
                
                <h5><i class="fas fa-gift"></i> Cuando se Active, Podrás:</h5>
                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li><i class="fas fa-check text-success"></i> Ver tu producción diaria</li>
                            <li><i class="fas fa-check text-success"></i> Consultar comisiones estimadas</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul>
                            <li><i class="fas fa-check text-success"></i> Ver historial de pagos</li>
                            <li><i class="fas fa-check text-success"></i> Detalles de cada comisión</li>
                        </ul>
                    </div>
                </div>
                
                <p class="mb-0 mt-3">
                    <i class="fas fa-hourglass-half"></i> <strong>Mientras tanto:</strong> 
                    Puedes seguir registrando tu producción normalmente. Las comisiones se calcularán una vez que el sistema esté activado.
                </p>
            </div>
        </div>
    </div>
    
    <?php else: ?>

    <!-- Filtros de Periodo -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/comisiones/mis-comisiones" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Mes</label>
                    <select name="mes" class="form-select">
                        <?php foreach ($meses as $num => $nombre): ?>
                        <option value="<?= $num ?>" <?= $mes == $num ? 'selected' : '' ?>><?= $nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Año</label>
                    <select name="anio" class="form-select">
                        <?php for ($a = date('Y'); $a >= date('Y') - 2; $a--): ?>
                        <option value="<?= $a ?>" <?= $anio == $a ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen del Mes -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-gradient-primary text-white h-100 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title text-white-50 text-uppercase small fw-bold">Bolsas Producidas (Mes)</h6>
                    <h2 class="display-6 fw-bold mb-0"><?= number_format($total_bolsas_mes) ?></h2>
                    <small class="text-white-50">bolsas aprobadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-white h-100 shadow-sm border-0">
                <div class="card-body text-center position-relative">
                    <span class="badge bg-light text-dark position-absolute top-0 end-0 m-3">Tarifa Actual</span>
                    <h6 class="card-title text-muted text-uppercase small fw-bold mt-2">Pago por Bolsa</h6>
                    <h2 class="display-6 fw-bold text-dark mb-0">S/ <?= number_format($tarifa_actual, 2) ?></h2>
                    <small class="text-muted">soles</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php if ($total_a_cobrar_producciones > 0): ?>
                <div class="card bg-gradient-warning text-dark h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="card-title text-black-50 text-uppercase small fw-bold">Falta Cobrar</h6>
                        <h2 class="display-6 fw-bold mb-0">S/ <?= number_format($total_a_cobrar_producciones, 2) ?></h2>
                        <small class="text-black-50"><i class="fas fa-clock me-1"></i> Pendiente de proceso</small>
                    </div>
                </div>
            <?php else: ?>
                <div class="card bg-gradient-success text-white h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="card-title text-white-50 text-uppercase small fw-bold">Estado de Pagos</h6>
                        <h2 class="display-6 fw-bold mb-0"><i class="fas fa-check-circle"></i> Al día</h2>
                        <small class="text-white-50">No tienes cobros pendientes</small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .bg-gradient-primary { background: linear-gradient(45deg, #0d6efd, #0a58ca); }
        .bg-gradient-success { background: linear-gradient(45deg, #198754, #157347); }
        .bg-gradient-warning { background: linear-gradient(45deg, #ffc107, #ffca2c); }
    </style>

    <!-- Producción Diaria del Mes -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Producción Diaria - <?= $meses[$mes] ?> <?= $anio ?></h5>
        </div>
        <div class="card-body">
            <?php if (empty($produccion_mes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay producciones registradas en este periodo.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th class="text-center">Total Prod.</th>
                                <th class="text-center">Aprobadas</th>
                                <th class="text-center">Rechazadas</th>
                                <th class="text-center">Pendientes</th>
                                <th class="text-center">Tarifa</th>
                                <th class="text-end">Comisión</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produccion_mes as $dia): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($dia['fecha'])) ?></td>
                                <td class="text-center"><?= $dia['total_producciones'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $dia['bolsas_aprobadas'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($dia['bolsas_rechazadas'] > 0): ?>
                                        <span class="badge bg-danger"><?= $dia['bolsas_rechazadas'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($dia['bolsas_pendientes'] > 0): ?>
                                        <span class="badge bg-warning"><?= $dia['bolsas_pendientes'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">S/ <?= number_format($dia['tarifa_por_bolsa'], 2) ?></td>
                                <td class="text-end fw-bold">S/ <?= number_format($dia['comision_estimada'], 2) ?></td>
                                <td class="text-center">
                                    <?php if ($dia['incluida_en_comision'] == 'SI'): ?>
                                        <?php if (isset($dia['estado_comision']) && $dia['estado_comision'] === 'pagado'): ?>
                                            <span class="badge bg-success" title="Pagado el <?= date('d/m', strtotime($dia['fecha_pago'])) ?>">
                                                <i class="fas fa-check-circle"></i> Pagado
                                            </span>
                                        <?php elseif (isset($dia['estado_comision']) && $dia['estado_comision'] === 'anulado'): ?>
                                            <span class="badge bg-danger">Anulado</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark" title="En proceso de pago">
                                                <i class="fas fa-cog"></i> Procesado
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sin procesar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">TOTAL A COBRAR (Pendiente):</td>
                                <td class="text-center"><?= $total_bolsas_mes ?></td>
                                <td colspan="4"></td>
                                <td class="text-end text-success">S/ <?= number_format($total_pendiente, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historial de Comisiones Liquidadas -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Historial de Comisiones</h5>
        </div>
        <div class="card-body">
            <?php if (empty($mis_comisiones)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aún no tienes comisiones calculadas.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Periodo</th>
                                <th class="text-center">Bolsas</th>
                                <th class="text-center">Tarifa</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Fecha Pago</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mis_comisiones as $com): ?>
                            <tr>
                                <td>#<?= $com['id_comision'] ?></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($com['fecha_inicio'])) ?> - 
                                    <?= date('d/m/Y', strtotime($com['fecha_fin'])) ?>
                                </td>
                                <td class="text-center"><?= number_format($com['total_bolsas_producidas']) ?></td>
                                <td class="text-center">S/ <?= number_format($com['tarifa_aplicada'], 2) ?></td>
                                <td class="text-end fw-bold">S/ <?= number_format($com['monto_total'], 2) ?></td>
                                <td class="text-center">
                                    <?php
                                    $statusConfig = [
                                        'pendiente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pendiente'],
                                        'calculado' => ['class' => 'info', 'icon' => 'cog', 'text' => 'Procesado'],
                                        'pagado'    => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Pagado'],
                                        'anulado'   => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Anulado']
                                    ];
                                    $config = $statusConfig[$com['estado']] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => $com['estado']];
                                    ?>
                                    <span class="badge bg-<?= $config['class'] ?> p-2">
                                        <i class="fas fa-<?= $config['icon'] ?> me-1"></i> <?= $config['text'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?= $com['fecha_pago'] ? date('d/m/Y', strtotime($com['fecha_pago'])) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/comisiones/detalle/<?= $com['id_comision'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php endif; // Fin de verificación tabla_no_existe ?>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
