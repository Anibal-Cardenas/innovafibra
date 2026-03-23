<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus-circle"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/produccion" class="btn btn-secondary">
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
            <form method="POST" action="<?= BASE_URL ?>/produccion/nueva">
                <?= csrfField() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_produccion" class="form-label">Fecha de Producción *</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_produccion" 
                                   name="fecha_produccion" 
                                   value="<?= isset($old['fecha_produccion']) ? h($old['fecha_produccion']) : date('Y-m-d') ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_cubo" class="form-label">Fardo de Fibra *</label>
                            <select class="form-select" id="id_cubo" name="id_cubo" required>
                                <option value="">Seleccione un fardo...</option>
                                <?php foreach ($cubos as $cubo): ?>
                                <option value="<?= $cubo['id_cubo'] ?>"
                                        data-estimado="<?= $cubo['cantidad_estimada_bolsas'] ?>"
                                        data-producido="<?= $cubo['cantidad_producida_real'] ?>"
                                        data-pendiente="<?= $cubo['pendiente'] ?>"
                                        data-peso="<?= $cubo['peso_neto'] ?>"
                                        data-calidad-fibra="<?= h($cubo['calidad_fibra'] ?? '') ?>"
                                        data-calidad-napa="<?= h($cubo['calidad_napa_producira'] ?? '') ?>"
                                        data-codigo-napa="<?= h($cubo['codigo_napa'] ?? '') ?>"
                                        <?= (isset($old['id_cubo']) && $old['id_cubo'] == $cubo['id_cubo']) ? 'selected' : '' ?>>
                                    <?= h($cubo['descripcion']) ?> 
                                    (<?= h($cubo['calidad_fibra'] ?? 'S/C') ?>) - 
                                    Estimado: <?= $cubo['cantidad_estimada_bolsas'] ?> bolsas
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small id="info_cubo" class="form-text text-muted"></small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info" id="info_calidad" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Calidad a producir:</strong> <span id="calidad_napa_display"></span>
                            <br>
                            <small>Esta calidad se asignará automáticamente según el tipo de fibra del cubo seleccionado.</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-users"></i> Detalle de Producción por Operador</strong>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label">Operador</label>
                                        <select class="form-select" id="select_operador">
                                            <option value="">Seleccione un operador...</option>
                                            <?php foreach ($operadores as $op): ?>
                                                <option value="<?= $op['id_usuario'] ?>"><?= h($op['nombre_completo']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cantidad (Bolsas)</label>
                                        <input type="number" class="form-control" id="input_cantidad_op" min="1" placeholder="0">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-success w-100" id="btn_add_operador">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="tabla_operadores">
                                        <thead>
                                            <tr>
                                                <th>Operador</th>
                                                <th width="150" class="text-center">Cantidad</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic rows -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-secondary">
                                                <td class="text-end"><strong>TOTAL PRODUCIDO:</strong></td>
                                                <td class="text-center"><strong id="total_producido_display">0</strong></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div id="hidden_inputs_container"></div>
                                <input type="hidden" name="cantidad_producida" id="cantidad_producida_total" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Consumo Estimado de Bolsas Plásticas (Total)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="peso_bolsas_consumido" 
                                       name="peso_bolsas_consumido"
                                       step="0.01"
                                       min="0"
                                       value="<?= isset($old['peso_bolsas_consumido']) ? h($old['peso_bolsas_consumido']) : '' ?>"
                                       placeholder="0.00">
                                <span class="input-group-text">kg</span>
                            </div>
                            <small class="text-muted">Factor ref: <?= $factor_conversion ?> kg/bolsa</small>
                            <input type="hidden" id="factor_conversion" value="<?= $factor_conversion ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" 
                              id="observaciones" 
                              name="observaciones" 
                              rows="3"><?= isset($old['observaciones']) ? h($old['observaciones']) : '' ?></textarea>
                    <small class="form-text text-muted">
                        Obligatorio si hay merma excesiva (eficiencia menor al 95%)
                    </small>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> La producción quedará pendiente de validación por un supervisor.
                    Si la cantidad producida es menor al 95% del estimado, se generará una alerta de merma.
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= BASE_URL ?>/produccion" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Producción
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar Select2
    $('#id_cubo').select2({
        theme: 'bootstrap-5', // Si tienes un tema compatible, si no, usa el default
        width: '100%',
        placeholder: 'Seleccione un fardo...',
        allowClear: true
    });

    const selectCubo = $('#id_cubo');
    const inputPeso = $('#peso_bolsas_consumido');
    const infoCubo = $('#info_cubo');
    const factorConversion = <?= $factor_conversion ?>;

    // --- LOGICA MULTI-OPERADOR ---
    const selectOperador = $('#select_operador');
    const inputCantidadOp = $('#input_cantidad_op');
    const btnAddOperador = $('#btn_add_operador');
    const tablaOperadoresBody = $('#tabla_operadores tbody');
    const containerInputs = $('#hidden_inputs_container');
    const totalDisplay = $('#total_producido_display');
    const inputTotal = $('#cantidad_producida_total');
    
    let operadoresList = [];

    function renderTabla() {
        tablaOperadoresBody.empty();
        containerInputs.empty();
        let total = 0;

        operadoresList.forEach((item, index) => {
            total += parseInt(item.cantidad);
            
            const row = `
                <tr>
                    <td>${item.nombre}</td>
                    <td class="text-center">${item.cantidad}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tablaOperadoresBody.append(row);

            // Inputs hidden
            containerInputs.append(`<input type="hidden" name="operadores[]" value="${item.id}">`);
            containerInputs.append(`<input type="hidden" name="cantidades[]" value="${item.cantidad}">`);
        });

        totalDisplay.text(total);
        inputTotal.val(total);

        // Actualizar consumo de bolsas
        const consumo = total * factorConversion;
        if (!inputPeso.data('manual')) {
            inputPeso.val(consumo.toFixed(2));
        }
    }

    btnAddOperador.click(function() {
        const idOp = selectOperador.val();
        const nombreOp = selectOperador.find(':selected').text();
        const cant = parseInt(inputCantidadOp.val());

        if (!idOp) {
            alert('Seleccione un operador');
            return;
        }
        if (!cant || cant <= 0) {
            alert('Ingrese una cantidad válida');
            return;
        }

        operadoresList.push({
            id: idOp,
            nombre: nombreOp,
            cantidad: cant
        });

        renderTabla();
        
        // Reset inputs
        selectOperador.val('').trigger('change');
        inputCantidadOp.val('');
        inputCantidadOp.focus();
    });

    $(document).on('click', '.btn-remove', function() {
        const idx = $(this).data('index');
        operadoresList.splice(idx, 1);
        renderTabla();
    });

    // Enter en input cantidad agrega
    inputCantidadOp.keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            btnAddOperador.click();
        }
    });

    // Inicializar Select2 para operador
    $('#select_operador').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Seleccione un operador...'
    });
    
    // --- FIN LOGICA MULTI-OPERADOR ---
    
    // Mostrar info del cubo seleccionado
    selectCubo.on('change', function() {
        // Con Select2/jQuery obtenemos la opción seleccionada así:
        const option = $(this).find(':selected');
        const val = $(this).val();

        if (val) {
            const estimado = option.data('estimado');
            const producido = option.data('producido');
            const pendiente = option.data('pendiente');
            const peso = option.data('peso');
            const calidadFibra = option.data('calidadFibra') || 'No especificada';
            const calidadNapa = option.data('calidadNapa') || 'No especificada';
            const codigoNapa = option.data('codigoNapa') || '';
            
            infoCubo.html(`<strong>Fardo de ${peso} kg</strong> - Estimado: ${estimado} bolsas | Ya producido: ${producido} | Pendiente: ${pendiente} bolsas`);
            infoCubo.addClass('text-primary');
            
            // Mostrar calidad que se producirá
            const infoCalidad = $('#info_calidad');
            const displayCalidad = $('#calidad_napa_display');
            
            if (calidadNapa && calidadNapa !== 'No especificada') {
                displayCalidad.html(`<span class="badge bg-success">${codigoNapa}</span> ${calidadNapa}`);
                infoCalidad.show();
            } else {
                infoCalidad.hide();
            }
        } else {
            infoCubo.html('');
            $('#info_calidad').hide();
        }
    });
    
    inputPeso.on('input', function() {
        $(this).data('manual', true);
    });
    
    // Trigger events on load if values exist (re-population)
    if (selectCubo.val()) {
        selectCubo.trigger('change');
    }
});
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>