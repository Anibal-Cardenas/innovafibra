<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Registrar Nuevo Ingreso</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/otros_ingresos/nuevo">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha del Ingreso *</label>
                                <input type="date" name="fecha_ingreso" class="form-control" 
                                       value="<?= isset($old['fecha_ingreso']) ? $old['fecha_ingreso'] : date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría *</label>
                                <select name="categoria" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat ?>" <?= (isset($old['categoria']) && $old['categoria'] == $cat) ? 'selected' : '' ?>>
                                            <?= $cat ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="descripcion" class="form-control" 
                                   value="<?= isset($old['descripcion']) ? h($old['descripcion']) : '' ?>"
                                   placeholder="Detalle del ingreso (opcional)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Monto (S/) *</label>
                            <input type="number" name="monto" class="form-control" step="0.01" min="0" 
                                   value="<?= isset($old['monto']) ? $old['monto'] : '' ?>" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/otros_ingresos" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Ingreso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>