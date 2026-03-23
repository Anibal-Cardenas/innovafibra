<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-id-card"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/choferes/nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Chofer
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>DNI</th>
                            <th>Licencia</th>
                            <th>Teléfono</th>
                            <th class="text-end">Sueldo</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($choferes)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No hay choferes registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($choferes as $chofer): ?>
                            <tr>
                                <td><strong><?= h($chofer['nombre_completo']) ?></strong></td>
                                <td><?= h($chofer['dni'] ?: '-') ?></td>
                                <td><?= h($chofer['licencia'] ?: '-') ?></td>
                                <td><?= h($chofer['telefono'] ?: '-') ?></td>
                                <td class="text-end"><?= formatCurrency($chofer['sueldo']) ?></td>
                                <td><?= h($chofer['vehiculo'] ?: '-') ?></td>
                                <td>
                                    <?php if ($chofer['estado'] === 'activo'): ?>
                                    <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/choferes/editar?id=<?= $chofer['id_chofer'] ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
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