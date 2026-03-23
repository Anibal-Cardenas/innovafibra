<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-users"></i> Gestión de Usuarios</h2>
            <p class="text-muted">Administrar usuarios del sistema y sus roles</p>
        </div>
    </div>

    <!-- Filtros y Acciones -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-10">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Filtrar por Rol</label>
                            <select name="rol" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos los roles</option>
                                <option value="administrador" <?= $filtro_rol === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                                <option value="operador" <?= $filtro_rol === 'operador' ? 'selected' : '' ?>>Operador</option>
                                <option value="vendedor" <?= $filtro_rol === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                                <option value="supervisor" <?= $filtro_rol === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filtrar por Estado</label>
                            <select name="estado" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                <option value="activo" <?= $filtro_estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $filtro_estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <a href="<?= BASE_URL ?>/usuarios" class="btn btn-secondary w-100">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </a>
                        </div>
                    </form>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="<?= BASE_URL ?>/usuarios/nuevo" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay usuarios registrados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>DNI</th>
                                <th>Rol</th>
                                <th class="text-center">Tarifa/Bolsa</th>
                                <th class="text-center">Estado</th>
                                <th>Fecha Ingreso</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user): ?>
                            <tr>
                                <td><?= $user['id_usuario'] ?></td>
                                <td>
                                    <strong><?= h($user['username']) ?></strong>
                                    <?php if ($user['id_usuario'] == getCurrentUserId()): ?>
                                        <span class="badge bg-info">Tú</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($user['nombre_completo']) ?></td>
                                <td><?= h($user['dni'] ?: '-') ?></td>
                                <td>
                                    <?php
                                    $roleColors = [
                                        'administrador' => 'danger',
                                        'operador' => 'primary',
                                        'vendedor' => 'success',
                                        'supervisor' => 'warning',
                                        'trabajador' => 'secondary'
                                    ];
                                    $color = $roleColors[$user['rol']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= ucfirst($user['rol']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (in_array($user['rol'], ['operador', 'trabajador'])): ?>
                                        <strong>S/ <?= number_format($user['tarifa_por_bolsa'], 2) ?></strong>
                                        <button type="button" class="btn btn-sm btn-link p-0 ms-1 btn-editar-tarifa" 
                                                data-id="<?= $user['id_usuario'] ?>"
                                                data-nombre="<?= h($user['nombre_completo']) ?>"
                                                data-tarifa="<?= $user['tarifa_por_bolsa'] ?>"
                                                title="Cambiar tarifa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input toggle-estado" type="checkbox" 
                                               data-id="<?= $user['id_usuario'] ?>"
                                               <?= $user['estado'] === 'activo' ? 'checked' : '' ?>
                                               <?= $user['id_usuario'] == getCurrentUserId() ? 'disabled' : '' ?>>
                                    </div>
                                </td>
                                <td><?= $user['fecha_ingreso'] ? date('d/m/Y', strtotime($user['fecha_ingreso'])) : '-' ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/usuarios/editar/<?= $user['id_usuario'] ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-reset-password" 
                                                data-id="<?= $user['id_usuario'] ?>"
                                                data-nombre="<?= h($user['nombre_completo']) ?>"
                                                title="Resetear contraseña">
                                            <i class="fas fa-key"></i>
                                        </button>
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

<!-- Modal: Cambiar Tarifa -->
<div class="modal fade" id="modalTarifa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-dollar-sign"></i> Cambiar Tarifa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTarifa">
                <div class="modal-body">
                    <input type="hidden" name="id_usuario" id="tarifaIdUsuario">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Operador:</label>
                        <div id="tarifaNombre"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tarifa Actual:</label>
                        <div class="h5 text-muted" id="tarifaActual"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Tarifa (S/) *</label>
                        <input type="number" name="tarifa_nueva" class="form-control" 
                               step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo del Cambio</label>
                        <textarea name="motivo" class="form-control" rows="2" 
                                  placeholder="Opcional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Resetear Contraseña -->
<div class="modal fade" id="modalPassword" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-key"></i> Resetear Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPassword">
                <div class="modal-body">
                    <input type="hidden" name="id_usuario" id="passwordIdUsuario">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Usuario:</label>
                        <div id="passwordNombre"></div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Esta acción cambiará la contraseña del usuario.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña *</label>
                        <input type="password" name="nueva_password" class="form-control" 
                               minlength="6" required>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check"></i> Resetear Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle estado usuario
    $('.toggle-estado').on('change', function() {
        const checkbox = $(this);
        const id = checkbox.data('id');
        const nuevoEstado = checkbox.is(':checked') ? 'activo' : 'inactivo';
        
        $.ajax({
            url: '<?= BASE_URL ?>/usuarios/cambiar-estado',
            method: 'POST',
            data: { id_usuario: id, estado: nuevoEstado },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                } else {
                    alert(response.message);
                    checkbox.prop('checked', !checkbox.is(':checked'));
                }
            },
            error: function() {
                alert('Error al cambiar el estado');
                checkbox.prop('checked', !checkbox.is(':checked'));
            }
        });
    });
    
    // Abrir modal para cambiar tarifa
    $('.btn-editar-tarifa').on('click', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const tarifa = $(this).data('tarifa');
        
        $('#tarifaIdUsuario').val(id);
        $('#tarifaNombre').text(nombre);
        $('#tarifaActual').text('S/ ' + parseFloat(tarifa).toFixed(2));
        $('input[name="tarifa_nueva"]').val(tarifa);
        
        new bootstrap.Modal($('#modalTarifa')).show();
    });
    
    // Guardar nueva tarifa
    $('#formTarifa').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= BASE_URL ?>/usuarios/actualizar-tarifa',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
    
    // Abrir modal para resetear contraseña
    $('.btn-reset-password').on('click', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#passwordIdUsuario').val(id);
        $('#passwordNombre').text(nombre);
        $('input[name="nueva_password"]').val('');
        
        new bootstrap.Modal($('#modalPassword')).show();
    });
    
    // Resetear contraseña
    $('#formPassword').on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('¿Está seguro de resetear la contraseña de este usuario?')) {
            return;
        }
        
        $.ajax({
            url: '<?= BASE_URL ?>/usuarios/resetear-password',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    bootstrap.Modal.getInstance($('#modalPassword')).hide();
                }
            }
        });
    });
});

function showToast(type, message) {
    // Función simple de notificación
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const toast = $('<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">' +
                    message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('body').append(toast);
    setTimeout(() => toast.alert('close'), 3000);
}
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
