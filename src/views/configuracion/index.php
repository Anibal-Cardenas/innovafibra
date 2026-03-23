<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-cog"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (isset($params) && count($params) > 0): ?>
    
    <?php
    // Organizar parámetros por grupos lógicos para mejor UX
    $grupos = [
        'empresa' => [
            'titulo' => 'Datos de la Empresa',
            'icono' => 'fa-building',
            'descripcion' => 'Información que aparecerá en los documentos impresos (Guías, Comprobantes)',
            'params' => []
        ],
        'produccion' => [
            'titulo' => 'Producción e Inventario',
            'icono' => 'fa-industry',
            'descripcion' => 'Factores de conversión y límites de stock',
            'params' => []
        ],
        'sistema' => [
            'titulo' => 'Sistema y Ventas',
            'icono' => 'fa-sliders-h',
            'descripcion' => 'Configuraciones generales del comportamiento del sistema',
            'params' => []
        ],
        'otros' => [
            'titulo' => 'Otros Parámetros',
            'icono' => 'fa-list',
            'descripcion' => 'Configuraciones adicionales',
            'params' => []
        ]
    ];

    // Mapa de asignación de parámetros a grupos
    $asignacion = [
        'nombre_empresa' => 'empresa',
        'ruc_empresa' => 'empresa',
        'direccion_empresa' => 'empresa',
        'telefono_empresa' => 'empresa',
        'email_empresa' => 'empresa',
        
        'factor_conversion_bolsas' => 'produccion',
        'factor_conversion_cubo' => 'produccion',
        'cantidad_estimada_default' => 'produccion',
        'tolerancia_merma' => 'produccion',
        'stock_minimo_bolsas' => 'produccion',
        'stock_minimo_fibra' => 'produccion',
        
        'margen_minimo_venta' => 'sistema',
        'timeout_sesion' => 'sistema'
    ];

    // Parámetros a ocultar (limpieza de configuración)
    $ocultos = ['stock_minimo_fibra', 'timeout_sesion', 'margen_minimo_venta'];

    foreach ($params as $p) {
        if (in_array($p['parametro'], $ocultos)) continue;
        $grupoKey = $asignacion[$p['parametro']] ?? 'otros';
        $grupos[$grupoKey]['params'][] = $p;
    }
    ?>

    <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
        <?= csrfField() ?>
        
        <div class="row">
            <?php foreach ($grupos as $key => $grupo): ?>
                <?php if (empty($grupo['params'])) continue; ?>
                
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                                    <i class="fas <?= $grupo['icono'] ?> fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark"><?= $grupo['titulo'] ?></h5>
                                    <small class="text-muted"><?= $grupo['descripcion'] ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;" class="ps-4">Parámetro</th>
                                            <th style="width: 35%;">Valor</th>
                                            <th style="width: 35%;">Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grupo['params'] as $p): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-secondary">
                                                <?= h(ucwords(str_replace('_', ' ', $p['parametro']))) ?>
                                            </td>
                                            <td>
                                                <?php if ($p['tipo_dato'] === 'decimal'): ?>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" name="valor[<?= h($p['parametro']) ?>]" class="form-control" value="<?= h($p['valor']) ?>">
                                                    </div>
                                                <?php elseif ($p['tipo_dato'] === 'integer' || $p['tipo_dato'] === 'int'): ?>
                                                    <input type="number" step="1" name="valor[<?= h($p['parametro']) ?>]" class="form-control form-control-sm" value="<?= h($p['valor']) ?>">
                                                <?php elseif ($p['tipo_dato'] === 'boolean'): ?>
                                                    <select name="valor[<?= h($p['parametro']) ?>]" class="form-select form-select-sm">
                                                        <option value="1" <?= $p['valor'] == '1' ? 'selected' : '' ?>>Sí</option>
                                                        <option value="0" <?= $p['valor'] == '0' ? 'selected' : '' ?>>No</option>
                                                    </select>
                                                <?php else: ?>
                                                    <input type="text" name="valor[<?= h($p['parametro']) ?>]" class="form-control form-control-sm" value="<?= h($p['valor']) ?>">
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?= h($p['descripcion']) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="card mb-5 border-primary shadow-sm">
            <div class="card-body bg-light">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <label class="form-label fw-bold text-primary"><i class="fas fa-comment-alt me-2"></i>Motivo del cambio (opcional)</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ej: Actualización de dirección fiscal, ajuste de precios...">
                    </div>
                    <div class="col-md-4 text-end mt-3 mt-md-0">
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>
    <?php else: ?>
    <div class="alert alert-info">No hay parámetros de configuración disponibles.</div>
    <?php endif; ?>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
