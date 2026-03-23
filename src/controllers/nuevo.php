<?php require_once VIEWS_PATH . '/layout/header.php'; ?>
<!-- Vista de Nuevo Gasto -->

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice-dollar"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/gastos" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/gastos/nuevo">
                <?= csrfField() ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_gasto" class="form-label">Fecha *</label>
                        <input type="date" class="form-control" id="fecha_gasto" name="fecha_gasto" 
                               value="<?= h($old['fecha_gasto'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="categoria" class="form-label">Categoría *</label>
                        <select class="form-select" id="categoria" name="categoria" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat ?>" <?= (isset($old['categoria']) && $old['categoria'] == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <input type="text" class="form-control" id="descripcion" name="descripcion" 
                           value="<?= h($old['descripcion'] ?? '') ?>" placeholder="Detalle del gasto">
                </div>
                
                <div class="mb-3">
                    <label for="monto" class="form-label">Monto (S/) *</label>
                    <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" 
                           value="<?= h($old['monto'] ?? '') ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar Gasto</button>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>