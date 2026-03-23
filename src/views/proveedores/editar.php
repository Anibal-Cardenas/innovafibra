<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/proveedores" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/proveedores/editar?id=<?= $proveedor['id_proveedor'] ?>">
                <?= csrfField() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= h($old['nombre'] ?? $proveedor['nombre']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="ruc" class="form-label">RUC</label>
                            <input type="text" class="form-control" id="ruc" name="ruc" 
                                   value="<?= h($old['ruc'] ?? $proveedor['ruc']) ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tipo_proveedor" class="form-label">Tipo *</label>
                            <select class="form-select" id="tipo_proveedor" name="tipo_proveedor" required>
                                <option value="">Seleccione...</option>
                                <option value="fibra" <?= ($old['tipo_proveedor'] ?? $proveedor['tipo_proveedor']) === 'fibra' ? 'selected' : '' ?>>Fibra</option>
                                <option value="bolsas" <?= ($old['tipo_proveedor'] ?? $proveedor['tipo_proveedor']) === 'bolsas' ? 'selected' : '' ?>>Bolsas Plásticas</option>
                                <option value="otros" <?= ($old['tipo_proveedor'] ?? $proveedor['tipo_proveedor']) === 'otros' ? 'selected' : '' ?>>Otros</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contacto_principal" class="form-label">Contacto Principal</label>
                            <input type="text" class="form-control" id="contacto_principal" name="contacto_principal" 
                                   value="<?= h($old['contacto_principal'] ?? $proveedor['contacto_principal']) ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="<?= h($old['telefono'] ?? $proveedor['telefono']) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" 
                           value="<?= h($old['direccion'] ?? $proveedor['direccion']) ?>">
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= h($old['email'] ?? $proveedor['email']) ?>">
                </div>
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado *</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="activo" <?= ($old['estado'] ?? $proveedor['estado']) === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= ($old['estado'] ?? $proveedor['estado']) === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
