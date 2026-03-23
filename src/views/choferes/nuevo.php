<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-id-card"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/choferes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/choferes/nuevo">
                <?= csrfField() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                                   value="<?= h($old['nombre_completo'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dni" name="dni" 
                                   value="<?= h($old['dni'] ?? '') ?>" maxlength="20">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="licencia" class="form-label">Licencia</label>
                            <input type="text" class="form-control" id="licencia" name="licencia" 
                                   value="<?= h($old['licencia'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="<?= h($old['telefono'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="sueldo" class="form-label">Sueldo Mensual (S/)</label>
                            <input type="number" class="form-control" id="sueldo" name="sueldo" 
                                   step="0.01" min="0"
                                   value="<?= h($old['sueldo'] ?? '0.00') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="vehiculo" class="form-label">Vehículo (Placa/Descripción)</label>
                            <input type="text" class="form-control" id="vehiculo" name="vehiculo" 
                                   value="<?= h($old['vehiculo'] ?? '') ?>" 
                                   placeholder="Ej: ABC-123 / Camión Nissan">
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Chofer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>