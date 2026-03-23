<?php require_once VIEWS_PATH . '/layout/header.php'; ?>
<!-- Vista de Lista de Gastos -->

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice-dollar"></i> <?= h($title) ?></h2>
        <div>
            <form action="<?= BASE_URL ?>/gastos/importarSueldos" method="POST" class="d-inline" onsubmit="return confirm('¿Importar sueldos de choferes activos para este mes?');">
                <input type="hidden" name="mes" value="<?= $mes_seleccionado ?>">
                <input type="hidden" name="anio" value="<?= $anio_seleccionado ?>">
                <button type="submit" class="btn btn-info text-white me-2">
                    <i class="fas fa-users"></i> Cargar Sueldos Choferes
                </button>
            </form>
            <a href="<?= BASE_URL ?>/gastos/nuevo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Gasto
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="<?= BASE_URL ?>/gastos">
                <div class="col-auto">
                    <label class="form-label">Mes</label>
                    <select name="mes" class="form-select">
                        <?php for($m=1; $m<=12; $m++): $mStr = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                            <option value="<?= $mStr ?>" <?= $mStr == $mes_seleccionado ? 'selected' : '' ?>><?= getNombreMes($m) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Año</label>
                    <select name="anio" class="form-select">
                        <?php for($y=date('Y'); $y>=2024; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $anio_seleccionado ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                </div>
                <div class="col text-end">
                    <h4 class="mb-0 text-danger">Total: <?= formatCurrency($total_gastos) ?></h4>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($gastos)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No hay gastos registrados en este periodo.</td></tr>
                        <?php else: ?>
                            <?php foreach ($gastos as $gasto): ?>
                            <tr>
                                <td><?= formatDate($gasto['fecha_gasto']) ?></td>
                                <td><span class="badge bg-secondary"><?= h($gasto['categoria']) ?></span></td>
                                <td><?= h($gasto['descripcion']) ?></td>
                                <td class="text-end fw-bold"><?= formatCurrency($gasto['monto']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>