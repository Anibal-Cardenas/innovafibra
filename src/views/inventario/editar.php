<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Ajuste Manual de Inventario</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i> 
                        Estás editando: <strong>
                            <?= $item['tipo_item'] == 'producto_terminado' ? 'Napa ' . h($item['nombre_calidad']) : h(str_replace('_', ' ', $item['tipo_item'])) ?>
                        </strong>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/inventario/editar/<?= $item['id_inventario'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Stock Actual (<?= h($item['unidad_medida']) ?>)</label>
                                <input type="number" name="cantidad" class="form-control form-control-lg fw-bold" 
                                       step="0.01" min="0" 
                                       value="<?= isset($old['cantidad']) ? $old['cantidad'] : $item['cantidad'] ?>" required>
                                <small class="text-muted">Modificar solo si hay discrepancia física.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Mínimo (Alerta)</label>
                                <input type="number" name="stock_minimo" class="form-control" 
                                       step="0.01" min="0" 
                                       value="<?= isset($old['stock_minimo']) ? $old['stock_minimo'] : $item['stock_minimo'] ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo del Ajuste / Observación</label>
                            <textarea name="observacion" class="form-control" rows="3" 
                                      placeholder="Obligatorio si cambia la cantidad (Ej: Merma no reportada, Conteo físico anual)"><?= isset($old['observacion']) ? h($old['observacion']) : '' ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/inventario" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Guardar Ajuste
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>