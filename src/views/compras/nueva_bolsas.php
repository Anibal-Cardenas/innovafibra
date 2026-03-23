<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus-circle"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/compras/lotes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?= h($error) ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            
            <div class="alert alert-info mb-4">
                <i class="fas fa-box"></i>
                <strong>Stock Actual de Bolsas Plásticas:</strong> 
                <?php 
                $stmt = $db->query("SELECT SUM(peso_kg) as total FROM compras_bolsas");
                $stockData = $stmt->fetch();
                $stockBolsas = $stockData['total'] ?? 0;
                echo number_format($stockBolsas, 2);
                ?> kg
                <span class="ms-3">
                    <i class="fas fa-shopping-bag"></i>
                    Aprox. <?= number_format($stockBolsas / $factor_conversion, 0) ?> bolsas
                </span>
            </div>
            
            <form method="POST" action="<?= BASE_URL ?>/compras/nueva-bolsas">
                <?= csrfField() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_compra" class="form-label">Fecha de Compra *</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_compra" 
                                   name="fecha_compra" 
                                   value="<?= isset($old['fecha_compra']) ? h($old['fecha_compra']) : date('Y-m-d') ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_proveedor" class="form-label">Proveedor *</label>
                            <select class="form-select" id="id_proveedor" name="id_proveedor" required>
                                <option value="">Seleccione un proveedor...</option>
                                <?php foreach ($proveedores as $prov): ?>
                                <option value="<?= $prov['id_proveedor'] ?>" 
                                        <?= (isset($old['id_proveedor']) && $old['id_proveedor'] == $prov['id_proveedor']) ? 'selected' : '' ?>>
                                    <?= h($prov['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="peso_kg" class="form-label">Peso (kg) *</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="peso_kg" 
                                   name="peso_kg" 
                                   step="0.01"
                                   min="0.01"
                                   value="<?= isset($old['peso_kg']) ? h($old['peso_kg']) : '' ?>"
                                   required>
                            <small class="form-text text-muted">
                                Factor: <?= $factor_conversion ?> kg/bolsa
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="precio_total" class="form-label">Precio Total (S/) *</label>
                            <input type="number" 
                                   class="form-control currency-input" 
                                   id="precio_total" 
                                   name="precio_total" 
                                   step="0.01"
                                   min="0.01"
                                   value="<?= isset($old['precio_total']) ? h($old['precio_total']) : '' ?>"
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-secondary">
                            <h5>Conversión Automática</h5>
                            <p class="mb-0">
                                <strong>Peso ingresado:</strong> <span id="peso_display">0.00</span> kg<br>
                                <strong>Equivale a:</strong> <span id="unidades_display">0</span> bolsas plásticas<br>
                                <strong>Precio por kg:</strong> S/ <span id="precio_kg_display">0.00</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="numero_guia" class="form-label">Número de Guía</label>
                    <input type="text" 
                           class="form-control" 
                           id="numero_guia" 
                           name="numero_guia" 
                           value="<?= isset($old['numero_guia']) ? h($old['numero_guia']) : '' ?>"
                           placeholder="Opcional">
                </div>
                
                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" 
                              id="observaciones" 
                              name="observaciones" 
                              rows="3"><?= isset($old['observaciones']) ? h($old['observaciones']) : '' ?></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> El inventario de bolsas plásticas se actualizará automáticamente en kilogramos.
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= BASE_URL ?>/compras/lotes" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const factor = <?= $factor_conversion ?>;
    
    function actualizarConversion() {
        const peso = parseFloat($('#peso_kg').val()) || 0;
        const precioTotal = parseFloat($('#precio_total').val()) || 0;
        const unidades = Math.floor(peso / factor);
        const precioKg = peso > 0 ? precioTotal / peso : 0;
        
        $('#peso_display').text(peso.toFixed(2));
        $('#unidades_display').text(unidades.toLocaleString());
        $('#precio_kg_display').text(precioKg.toFixed(2));
    }
    
    $('#peso_kg, #precio_total').on('input', actualizarConversion);
    actualizarConversion();
});
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
