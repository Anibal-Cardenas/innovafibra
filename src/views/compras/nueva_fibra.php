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
            <form id="form_nueva_compra_fibra" method="POST" action="<?= BASE_URL ?>/compras/nueva-fibra">
                <?= csrfField() ?>
                
                <div class="row">
                    <div class="col-md-4">
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
                    
                    <div class="col-md-4">
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
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="id_calidad_fibra" class="form-label">Calidad de Fibra *</label>
                            <select class="form-select" id="id_calidad_fibra" name="id_calidad_fibra" required>
                                <option value="">Seleccione calidad...</option>
                                <?php foreach ($calidades_fibra as $cal): ?>
                                <option value="<?= $cal['id_calidad_fibra'] ?>" 
                                        <?= (isset($old['id_calidad_fibra']) && $old['id_calidad_fibra'] == $cal['id_calidad_fibra']) ? 'selected' : '' ?>>
                                    <span class="badge bg-<?= h($cal['color']) ?>">
                                        <?= h($cal['nombre']) ?>
                                    </span>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> La calidad de la fibra determina la calidad de la napa
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="numero_cubos" class="form-label">Número de Fardos *</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="numero_cubos" 
                                   name="numero_cubos" 
                                   min="1"
                                   value="<?= isset($old['numero_cubos']) ? h($old['numero_cubos']) : '1' ?>"
                                   required>
                            <small class="form-text text-muted">
                                Ingrese la cantidad de fardos
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Contenedor dinámico para pesos de cubos -->
                <div id="cubos-container" class="mb-3">
                    <h5 class="mb-3"><i class="fas fa-cube"></i> Peso Individual de Cada Fardo</h5>
                    <!-- Los campos se generarán dinámicamente aquí -->
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Peso Neto Total (kg)</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="peso_neto_total_display" 
                                   readonly
                                   value="0.00">
                            <small class="form-text text-muted">
                                Calculado automáticamente
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="precio_total" class="form-label">Precio Total (S/) *</label>
                            <input type="number" 
                                   class="form-control currency-input" 
                                   id="precio_total_fibra" 
                                   name="precio_total" 
                                   step="0.01"
                                   min="0.01"
                                   value="<?= isset($old['precio_total']) ? h($old['precio_total']) : '' ?>"
                                   required>
                            <small id="precio_por_kg_display" class="form-text text-muted">
                                Precio por kg: --
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cantidad_estimada" class="form-label">Cantidad Estimada de Bolsas</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="cantidad_estimada" 
                                   name="cantidad_estimada" 
                                   min="1"
                                   value="<?= isset($old['cantidad_estimada']) ? h($old['cantidad_estimada']) : $cantidad_estimada_default ?>">
                            <small class="form-text text-muted">
                                Default: <?= $cantidad_estimada_default ?> bolsas por fardo
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="numero_guia" class="form-label">Número de Guía</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="numero_guia" 
                                   name="numero_guia" 
                                   value="<?= isset($old['numero_guia']) ? h($old['numero_guia']) : '' ?>"
                                   placeholder="Opcional">
                        </div>
                    </div>
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
                    <strong>Nota:</strong> El sistema generará automáticamente un código de lote (LOTE-YYYY-MM-NNNN) 
                    y actualizará el inventario de fibra.
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
    // Generar campos de cubos dinámicamente
    function generarCamposCubos(numCubos) {
        const container = $('#cubos-container');
        container.find('.cubo-row').remove(); // Limpiar existentes
        
        for (let i = 1; i <= numCubos; i++) {
            const row = `
                <div class="row cubo-row mb-2">
                    <div class="col-md-2">
                        <label class="form-label pt-2"><strong>Fardo ${i}</strong></label>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Neto</span>
                            <input type="number" 
                                   class="form-control peso-neto-cubo" 
                                   name="pesos_neto[]" 
                                   step="0.01"
                                   min="0.01"
                                   data-cubo="${i}"
                                   required
                                   placeholder="Peso neto">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>
                    <div class="col-md-5">
                         <div class="input-group input-group-sm">
                            <span class="input-group-text">Estimado</span>
                            <input type="number" class="form-control form-control-sm estimadas-input" name="cantidad_estimada_cubos[]" id="estimadas_input_${i}" min="0" value="">
                            <span class="input-group-text">bolsas</span>
                        </div>
                    </div>
                </div>
            `;
            container.append(row);
        }
        
        // Agregar listeners para recalcular totales (input y change)
        $('.peso-neto-cubo').on('input change', calcularTotales);
        // también cuando el usuario modifica estimadas por cubo
        $('.estimadas-input').on('input change', calcularTotales);
        // Calcular totales iniciales
        calcularTotales();
    }
    
    // Calcular totales automáticamente
    function calcularTotales() {
        let totalNeto = 0;
        let totalBolsasEstimadas = 0;

        // Leer factor de configuración. El sistema puede guardar el factor
        // como "kg por bolsa" (p.ej. 0.02) o como "bolsas por kg" (p.ej. 50).
        const configFactorRaw = parseFloat($('#factor_conversion_hidden').val());
        const factorCuboRaw = parseFloat($('#factor_conversion_cubo_hidden').val());
        const fallbackFactor = <?= (float)DEFAULT_FACTOR_CONVERSION_CUBO ?>;

        // Determinar cómo interpretar el factor y obtener 'bolsas por kg' o el modo 'kg por bolsa'
        // Prioridad: usar `factor_conversion_cubo` (debe ser bolsas/kg). Si existe, usarlo directamente.
        // Si no existe, usar `factor_conversion` (puede ser kg/bolsa (<1) o bolsas/kg (>=1)).
        let usedFactor = fallbackFactor; // bolsas/kg por defecto
        let kgPerBag = null;
        let mode = 'bags_per_kg';

        if (!isNaN(factorCuboRaw) && factorCuboRaw > 0) {
            usedFactor = factorCuboRaw;
            mode = 'bags_per_kg';
        } else if (!isNaN(configFactorRaw) && configFactorRaw > 0) {
            if (configFactorRaw < 1) {
                // Se guardó como kg por bolsa
                kgPerBag = configFactorRaw;
                mode = 'kg_per_bag';
            } else {
                usedFactor = configFactorRaw;
                mode = 'bags_per_kg';
            }
        }

        if (typeof console !== 'undefined' && console.debug) console.debug('calcularTotales: mode=', mode, 'configFactorRaw=', configFactorRaw, 'factorCuboRaw=', factorCuboRaw, 'usedFactor(bolsas/kg)=', usedFactor, 'kgPerBag=', kgPerBag);

        $('.peso-neto-cubo').each(function() {
            const valor = Number($(this).val()) || 0;
            totalNeto += valor;
            const idx = $(this).data('cubo');
            const inputEstimadas = $('#estimadas_input_' + idx);

            // Siempre recalcular estimadas a partir del peso neto cuando se edite
            if (valor > 0) {
                let bolsas = 0;
                if (mode === 'kg_per_bag' && kgPerBag > 0) {
                    bolsas = Math.round(valor / kgPerBag);
                } else if (mode === 'bags_per_kg' && usedFactor > 0) {
                    bolsas = Math.round(valor * usedFactor);
                }

                if (inputEstimadas.length) {
                    inputEstimadas.val(bolsas);
                }
                totalBolsasEstimadas += bolsas;
            } else {
                // Si no hay peso, limpiar input estimadas
                if (inputEstimadas.length) {
                    inputEstimadas.val('');
                }
            }
        });

        $('#peso_neto_total_display').val(totalNeto.toFixed(2));

        // Actualizar cantidad estimada global con la suma de estimadas por cubo (siempre actualizar)
        $('#cantidad_estimada').val(totalBolsasEstimadas);

        // Calcular precio por kg
        const precioTotal = parseFloat($('#precio_total_fibra').val()) || 0;
        if (totalNeto > 0) {
            const precioPorKg = precioTotal / totalNeto;
            $('#precio_por_kg_display').text('Precio por kg: S/ ' + precioPorKg.toFixed(2));
        } else {
            $('#precio_por_kg_display').text('Precio por kg: --');
        }
    }
    
    // Evento cambio de número de cubos (usar input para responder al tecleo)
    $('#numero_cubos').on('input change', function() {
        const numCubos = parseInt($(this).val()) || 1;
        if (numCubos < 1) {
            $(this).val(1);
            return;
        }
        generarCamposCubos(numCubos);
    });
    
    // Cambio en precio total
    $('#precio_total_fibra').on('input', calcularTotales);

    // Antes de enviar el formulario, asegurarnos de que los campos de cubos
    // estén generados según el valor actual de `numero_cubos`.
    $('#form_nueva_compra_fibra').on('submit', function(e) {
        const expected = parseInt($('#numero_cubos').val()) || 1;
        const current = $('#cubos-container').find('.cubo-row').length || 0;
        if (current !== expected) {
            // Generar/ajustar campos de cubos inmediatamente
            generarCamposCubos(expected);
            // Dejar que el envío continúe (los nuevos campos estarán vacíos si el usuario no los llenó)
            // El servidor validará y mostrará mensajes claros si falta información.
        }
    });
    
    // Inicializar con 1 cubo
    // Si hay datos antiguos (error de validación), usar el número de cubos anterior
    const oldNumCubos = <?= isset($old['numero_cubos']) ? (int)$old['numero_cubos'] : 1 ?>;
    generarCamposCubos(oldNumCubos);
    
    // Si hay datos antiguos de pesos, poblarlos
    <?php if (isset($old['pesos_neto'])): ?>
    const oldPesosNeto = <?= json_encode($old['pesos_neto']) ?>;
    const oldEstimadas = <?= isset($old['cantidad_estimada_cubos']) ? json_encode($old['cantidad_estimada_cubos']) : '[]' ?>;
    
    // Función auxiliar para obtener valor seguro de array u objeto
    function getOldValue(collection, index) {
        if (Array.isArray(collection)) {
            return collection[index];
        } else if (typeof collection === 'object' && collection !== null) {
            return collection[index] || collection[String(index)];
        }
        return null;
    }

    $('.peso-neto-cubo').each(function(index) {
        const val = getOldValue(oldPesosNeto, index);
        if (val !== null && val !== undefined) $(this).val(val);
    });
    $('.estimadas-input').each(function(index) {
        const val = getOldValue(oldEstimadas, index);
        if (val !== null && val !== undefined) $(this).val(val);
    });
    // Recalcular totales después de restaurar valores
    calcularTotales();
    <?php endif; ?>
});

</script>

<!-- Hidden factor values: global bolsas factor and per-cubo factor -->
<input type="hidden" id="factor_conversion_hidden" value="<?= isset($factor_conversion) ? h($factor_conversion) : DEFAULT_FACTOR_CONVERSION ?>">
<input type="hidden" id="factor_conversion_cubo_hidden" value="<?= isset($factor_conversion_cubo) ? h($factor_conversion_cubo) : DEFAULT_FACTOR_CONVERSION_CUBO ?>">

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
