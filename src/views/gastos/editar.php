<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Editar Gasto</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/gastos/editar/<?= $gasto['id_gasto'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha del Gasto *</label>
                                <input type="date" name="fecha_gasto" class="form-control" 
                                       value="<?= isset($old['fecha_gasto']) ? $old['fecha_gasto'] : $gasto['fecha_gasto'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría *</label>
                                <select name="categoria" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat ?>" <?= (isset($old['categoria']) && $old['categoria'] == $cat) || (!isset($old) && $gasto['categoria'] == $cat) ? 'selected' : '' ?>>
                                            <?= $cat ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="descripcion" class="form-control" 
                                   value="<?= isset($old['descripcion']) ? h($old['descripcion']) : h($gasto['descripcion']) ?>"
                                   placeholder="Detalle del gasto (opcional)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Monto (S/) *</label>
                            <input type="number" name="monto" class="form-control" step="0.01" min="0" 
                                   value="<?= isset($old['monto']) ? $old['monto'] : $gasto['monto'] ?>" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/gastos" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Actualizar Gasto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>