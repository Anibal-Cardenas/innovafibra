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
            <h2><i class="fas fa-money-bill-wave"></i> Gastos Operativos</h2>
            <p class="text-muted">Registro y control de gastos del taller</p>
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
                    <form action="<?= BASE_URL ?>/gastos/importar-sueldos" method="POST" class="d-inline-block me-2" onsubmit="return confirm('¿Está seguro de importar los sueldos de los choferes para este mes?');">
                        <input type="hidden" name="mes" value="<?= $mes_seleccionado ?>">
                        <input type="hidden" name="anio" value="<?= $anio_seleccionado ?>">
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-file-import"></i> Importar Sueldos
                        </button>
                    </form>
                    <a href="<?= BASE_URL ?>/gastos/nuevo" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Registrar Gasto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Gastos (<?= isset($meses[(int)$mes_seleccionado]) ? $meses[(int)$mes_seleccionado] : 'Mes Desconocido' ?>)</h5>
                    <h2 class="mb-0">S/ <?= number_format($total_gastos, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Gastos -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($gastos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay gastos registrados para este periodo.
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
                            <?php foreach ($gastos as $gasto): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($gasto['fecha_gasto'])) ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= h($gasto['categoria']) ?></span>
                                </td>
                                <td><?= h($gasto['descripcion']) ?></td>
                                <td class="text-end fw-bold">S/ <?= number_format($gasto['monto'], 2) ?></td>
                                <td><small class="text-muted">ID: <?= $gasto['usuario_creacion'] ?></small></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/gastos/editar/<?= $gasto['id_gasto'] ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/gastos/eliminar/<?= $gasto['id_gasto'] ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este gasto?');">
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