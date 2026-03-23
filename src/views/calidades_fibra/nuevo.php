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
            <form method="POST" action="<?= BASE_URL ?>/calidades-fibra/nuevo">
                <?= csrfField() ?>
                
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Calidad *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= h($old['nombre'] ?? '') ?>" 
                           placeholder="Ej: Fibra Virgen Premium, Fibra Cristalizada, etc."
                           required>
                    <small class="form-text text-muted">
                        Ejemplos: Fibra Virgen, Fibra Cristalizada, Fibra Reciclada Premium, Fibra Estándar
                    </small>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= h($old['descripcion'] ?? '') ?></textarea>
                    <small class="form-text text-muted">
                        Describe las características de esta calidad de fibra
                    </small>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="color" class="form-label">Color del Badge</label>
                            <select class="form-select" id="color" name="color">
                                <option value="success" <?= isset($old['color']) && $old['color'] === 'success' ? 'selected' : '' ?>>Verde (Success)</option>
                                <option value="info" <?= isset($old['color']) && $old['color'] === 'info' ? 'selected' : 'selected' ?>>Azul (Info)</option>
                                <option value="warning" <?= isset($old['color']) && $old['color'] === 'warning' ? 'selected' : '' ?>>Amarillo (Warning)</option>
                                <option value="primary" <?= isset($old['color']) && $old['color'] === 'primary' ? 'selected' : '' ?>>Azul Oscuro (Primary)</option>
                                <option value="secondary" <?= isset($old['color']) && $old['color'] === 'secondary' ? 'selected' : '' ?>>Gris (Secondary)</option>
                                <option value="danger" <?= isset($old['color']) && $old['color'] === 'danger' ? 'selected' : '' ?>>Rojo (Danger)</option>
                            </select>
                            <small class="form-text text-muted">
                                Color visual para identificar esta calidad en la interfaz
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Calidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
