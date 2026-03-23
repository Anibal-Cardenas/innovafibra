<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-boxes"></i> <?= h($title) ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/compras/nueva-bolsas" class="btn btn-success me-2">
                <i class="fas fa-plus"></i> Nueva Compra de Bolsas
            </a>
            <a href="<?= BASE_URL ?>/compras/nueva-fibra" class="btn btn-primary">
                <i class="fas fa-plus"></i> Comprar Fibra
            </a>
        </div>
    </div>

    <?php if (empty($compras)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No hay compras de bolsas registradas aún.
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
                            <th>Proveedor</th>
                            <th class="text-end">Peso (kg)</th>
                            <th class="text-end">Precio Total</th>
                            <th class="text-end">Precio/kg</th>
                            <th>Tipo</th>
                            <th>Usuario</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $c): ?>
                        <tr>
                            <td><strong><?= h($c['id_compra_bolsa']) ?></strong></td>
                            <td><?= formatDate($c['fecha_compra']) ?></td>
                            <td><?= h($c['proveedor']) ?></td>
                            <td class="text-end"><?= formatDecimal($c['peso_kg']) ?></td>
                            <td class="text-end"><?= formatCurrency($c['precio_total']) ?></td>
                            <td class="text-end"><?= formatCurrency($c['precio_por_kg']) ?></td>
                            <td><?= h($c['tipo_bolsa']) ?></td>
                            <td><?= h($c['usuario']) ?></td>
                            <td><?= h($c['observaciones']) ?></td>
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
