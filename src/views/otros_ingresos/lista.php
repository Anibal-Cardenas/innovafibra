<?php require_once VIEWS_PATH . '/layout/header.php'; ?>
<?php 
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-hand-holding-usd text-success"></i> Otros Ingresos</h2>
            <p class="text-muted">Registro de ingresos extraordinarios que suman a la utilidad</p>
        </div>
    </div>

    <!-- Filtros y Acciones -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Mes</label>
                            <select name="mes" class="form-select">
                                <?php foreach ($meses as $num => $nombre): ?>
                                    <option value="<?= $num ?>" <?= (int)$num == (int)$mes_seleccionado ? 'selected' : '' ?>>
                                        <?= $nombre ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Año</label>
                            <select name="anio" class="form-select">
                                <?php 
                                $currentYear = date('Y');
                                for ($y = $currentYear; $y >= $currentYear - 2; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y == $anio_seleccionado ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                             <label class="form-label">&nbsp;</label>
                             <button type="submit" class="btn btn-secondary w-100">
                                <i class="fas fa-filter"></i> Filtrar
                             </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <a href="<?= BASE_URL ?>/otros_ingresos/nuevo" class="btn btn-success">
                        <i class="fas fa-plus"></i> Registrar Ingreso
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Ingresos (<?= isset($meses[(int)$mes_seleccionado]) ? $meses[(int)$mes_seleccionado] : 'Mes Desconocido' ?>)</h5>
                    <h2 class="mb-0">S/ <?= number_format($total_ingresos, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Ingresos -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($ingresos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay otros ingresos registrados para este periodo.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th class="text-end">Monto</th>
                                <th>Registrado Por</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ingresos as $ingreso): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($ingreso['fecha_ingreso'])) ?></td>
                                <td>
                                    <span class="badge bg-success"><?= h($ingreso['categoria']) ?></span>
                                </td>
                                <td><?= h($ingreso['descripcion']) ?></td>
                                <td class="text-end fw-bold text-success">+ S/ <?= number_format($ingreso['monto'], 2) ?></td>
                                <td><small class="text-muted">ID: <?= $ingreso['usuario_creacion'] ?></small></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/otros_ingresos/editar/<?= $ingreso['id_ingreso'] ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/otros_ingresos/eliminar/<?= $ingreso['id_ingreso'] ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este ingreso?');">
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
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

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>