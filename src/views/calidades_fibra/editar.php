<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-award"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/calidades-fibra" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/calidades-fibra/editar?id=<?= $calidad['id_calidad_fibra'] ?>">
                <?= csrfField() ?>
                
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Calidad *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= h($old['nombre'] ?? $calidad['nombre']) ?>" 
                           placeholder="Ej: Fibra Virgen Premium, Fibra Cristalizada, etc."
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= h($old['descripcion'] ?? $calidad['descripcion']) ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="color" class="form-label">Color del Badge</label>
                            <select class="form-select" id="color" name="color">
                                <?php $currentColor = $old['color'] ?? $calidad['color']; ?>
                                <option value="success" <?= $currentColor === 'success' ? 'selected' : '' ?>>Verde (Success)</option>
                                <option value="info" <?= $currentColor === 'info' ? 'selected' : '' ?>>Azul (Info)</option>
                                <option value="warning" <?= $currentColor === 'warning' ? 'selected' : '' ?>>Amarillo (Warning)</option>
                                <option value="primary" <?= $currentColor === 'primary' ? 'selected' : '' ?>>Azul Oscuro (Primary)</option>
                                <option value="secondary" <?= $currentColor === 'secondary' ? 'selected' : '' ?>>Gris (Secondary)</option>
                                <option value="danger" <?= $currentColor === 'danger' ? 'selected' : '' ?>>Rojo (Danger)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <?php $currentEstado = $old['estado'] ?? $calidad['estado']; ?>
                                <option value="activo" <?= $currentEstado === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $currentEstado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <small class="form-text text-muted">
                                Solo las calidades activas aparecen en los formularios
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Calidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
