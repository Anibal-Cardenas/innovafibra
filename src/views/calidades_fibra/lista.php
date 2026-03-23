<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-award"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/calidades-fibra/nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Calidad
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Calidades de Fibra:</strong> Define las diferentes calidades de fibra que puedes comprar (Virgen, Cristalizada, Reciclada, etc.). 
        La calidad de la fibra determina la calidad final de la napa producida.
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaCalidades" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($calidades as $cal): ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?= h($cal['color'] ?: 'secondary') ?>">
                                    <?= h($cal['nombre']) ?>
                                </span>
                            </td>
                            <td><?= h($cal['descripcion'] ?: '-') ?></td>
                            <td>
                                <?php if ($cal['estado'] === 'activo'): ?>
                                <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/calidades-fibra/editar?id=<?= $cal['id_calidad_fibra'] ?>" 
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
    $('#tablaCalidades').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[2, 'desc']] // Ordenar por factor de precio descendente
    });
});
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
