<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="<?= BASE_URL ?>/usuarios" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h2>
                <i class="fas fa-user-<?= $usuario ? 'edit' : 'plus' ?>"></i> 
                <?= $usuario ? 'Editar Usuario' : 'Nuevo Usuario' ?>
            </h2>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= h($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" autocomplete="off">
                <?= csrfField() ?>
                
                <div class="row">
                    <!-- Información de Acceso -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Información de Acceso</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario *</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?= h($usuario['username'] ?? ($old['username'] ?? '')) ?>" 
                                   required maxlength="50">
                            <small class="text-muted">Usuario para iniciar sesión</small>
                        </div>

                        <?php if (!$usuario): ?>
                            <!-- Solo para nuevos usuarios -->
                            <div class="mb-3">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" name="password" class="form-control" 
                                       required minlength="6" autocomplete="new-password">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirmar Contraseña *</label>
                                <input type="password" name="password_confirm" class="form-control" 
                                       required minlength="6" autocomplete="new-password">
                            </div>
                        <?php else: ?>
                            <!-- Para editar usuario -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cambiar_password" 
                                           name="cambiar_password" value="1">
                                    <label class="form-check-label" for="cambiar_password">
                                        Cambiar contraseña
                                    </label>
                                </div>
                            </div>

                            <div id="password-fields" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Nueva Contraseña</label>
                                    <input type="password" name="password" class="form-control" 
                                           minlength="6" autocomplete="new-password">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password_confirm" class="form-control" 
                                           minlength="6" autocomplete="new-password">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Rol *</label>
                            <select name="rol" class="form-select" id="selectRol" required>
                                <option value="">Seleccione un rol...</option>
                                <option value="administrador" <?= ($usuario['rol'] ?? ($old['rol'] ?? '')) === 'administrador' ? 'selected' : '' ?>>
                                    Administrador - Acceso completo
                                </option>
                                <option value="operador" <?= ($usuario['rol'] ?? ($old['rol'] ?? '')) === 'operador' ? 'selected' : '' ?>>
                                    Operador - Producción y comisiones
                                </option>
                                <option value="vendedor" <?= ($usuario['rol'] ?? ($old['rol'] ?? '')) === 'vendedor' ? 'selected' : '' ?>>
                                    Vendedor - Solo ventas
                                </option>
                                <option value="supervisor" <?= ($usuario['rol'] ?? ($old['rol'] ?? '')) === 'supervisor' ? 'selected' : '' ?>>
                                    Supervisor - Validación (legacy)
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="activo" <?= ($usuario['estado'] ?? ($old['estado'] ?? 'activo')) === 'activo' ? 'selected' : '' ?>>
                                    Activo
                                </option>
                                <option value="inactivo" <?= ($usuario['estado'] ?? ($old['estado'] ?? '')) === 'inactivo' ? 'selected' : '' ?>>
                                    Inactivo
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Información Personal</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" name="nombre_completo" class="form-control" 
                                   value="<?= h($usuario['nombre_completo'] ?? ($old['nombre_completo'] ?? '')) ?>" 
                                   required maxlength="150">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">DNI</label>
                            <input type="text" name="dni" class="form-control" 
                                   value="<?= h($usuario['dni'] ?? ($old['dni'] ?? '')) ?>" 
                                   maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= h($usuario['email'] ?? ($old['email'] ?? '')) ?>" 
                                   maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fecha de Ingreso</label>
                            <input type="date" name="fecha_ingreso" class="form-control" 
                                   value="<?= $usuario['fecha_ingreso'] ?? ($old['fecha_ingreso'] ?? '') ?>">
                        </div>

                        <!-- Tarifa por Bolsa (solo para operadores) -->
                        <div class="mb-3" id="tarifaField" style="display: none;">
                            <label class="form-label">Tarifa por Bolsa (S/)</label>
                            <input type="number" name="tarifa_por_bolsa" class="form-control" 
                                   value="<?= $usuario['tarifa_por_bolsa'] ?? ($old['tarifa_por_bolsa'] ?? '0.00') ?>" 
                                   step="0.01" min="0">
                            <small class="text-muted">Comisión que recibe por cada bolsa producida</small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> 
                            <?= $usuario ? 'Actualizar Usuario' : 'Crear Usuario' ?>
                        </button>
                        <a href="<?= BASE_URL ?>/usuarios" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mostrar/ocultar campos de contraseña en edición
    $('#cambiar_password').on('change', function() {
        if ($(this).is(':checked')) {
            $('#password-fields').show();
            $('#password-fields input').prop('required', true);
        } else {
            $('#password-fields').hide();
            $('#password-fields input').prop('required', false);
        }
    });
    
    // Mostrar/ocultar campo de tarifa según rol
    function toggleTarifaField() {
        const rol = $('#selectRol').val();
        if (rol === 'operador' || rol === 'trabajador') {
            $('#tarifaField').show();
        } else {
            $('#tarifaField').hide();
        }
    }
    
    $('#selectRol').on('change', toggleTarifaField);
    
    // Ejecutar al cargar la página
    toggleTarifaField();
});
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
