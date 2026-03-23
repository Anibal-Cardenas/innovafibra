<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/proveedores/nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Proveedor
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaProveedores" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>RUC</th>
                            <th>Tipo</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $prov): ?>
                        <tr>
                            <td><strong><?= h($prov['nombre']) ?></strong></td>
                            <td><?= h($prov['ruc'] ?: '-') ?></td>
                            <td>
                                <?php
                                $badgeClass = $prov['tipo_proveedor'] === 'fibra' ? 'bg-primary' : 
                                            ($prov['tipo_proveedor'] === 'bolsas' ? 'bg-info' : 'bg-secondary');
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= h(ucfirst($prov['tipo_proveedor'])) ?>
                                </span>
                            </td>
                            <td><?= h($prov['contacto_principal'] ?: '-') ?></td>
                            <td><?= h($prov['telefono'] ?: '-') ?></td>
                            <td>
                                <?php if ($prov['estado'] === 'activo'): ?>
                                <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/proveedores/editar?id=<?= $prov['id_proveedor'] ?>" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaProveedores').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[0, 'asc']]
    });
});
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
