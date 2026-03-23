<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-check-circle"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
    
    <?php if (empty($producciones_pendientes)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check"></i> No hay producciones pendientes de validación
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0">
                <i class="fas fa-clipboard-check"></i> 
                Producciones Pendientes (<?= count($producciones_pendientes) ?>)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Lote</th>
                            <th>Operario</th>
                            <th class="text-end">Estimado</th>
                            <th class="text-end">Producido</th>
                            <th class="text-center">Eficiencia</th>
                            <th class="text-center">Alerta</th>
                            <th>Observaciones</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($producciones_pendientes as $prod): ?>
                        <tr>
                            <td><?= formatDate($prod['fecha_produccion']) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= h($prod['codigo_lote']) ?></span>
                            </td>
                            <td><?= h($prod['operario']) ?></td>
                            <td class="text-end"><?= number_format($prod['cantidad_estimada_bolsas']) ?></td>
                            <td class="text-end"><?= number_format($prod['cantidad_producida']) ?></td>
                            <td class="text-center">
                                <?php
                                $eficiencia = $prod['eficiencia_porcentual'];
                                $clase = $eficiencia >= 95 ? 'eficiencia-alta' : 
                                        ($eficiencia >= 85 ? 'eficiencia-media' : 'eficiencia-baja');
                                ?>
                                <span class="<?= $clase ?>"><?= formatDecimal($eficiencia) ?>%</span>
                            </td>
                            <td class="text-center">
                                <?php if ($prod['flag_merma_excesiva']): ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i> MERMA
                                </span>
                                <?php else: ?>
                                <span class="badge bg-success">OK</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($prod['observaciones']): ?>
                                <small><?= h($prod['observaciones']) ?></small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" 
                                            class="btn btn-success btn-validar"
                                            data-id="<?= $prod['id_produccion'] ?>"
                                            data-decision="aprobado">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-danger btn-validar"
                                            data-id="<?= $prod['id_produccion'] ?>"
                                            data-decision="rechazado">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<!-- Modal para observaciones -->
<div class="modal fade" id="modalObservaciones" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Motivo del Rechazo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" 
                          id="observaciones_rechazo" 
                          rows="4" 
                          placeholder="Ingrese el motivo del rechazo..."
                          required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarRechazo">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let idProduccionActual = null;
    let decisionActual = null;
    
    // Botones de validación
    $('.btn-validar').on('click', function() {
        idProduccionActual = $(this).data('id');
        decisionActual = $(this).data('decision');
        
        if (decisionActual === 'aprobado') {
            if (confirm('¿Está seguro de APROBAR esta producción?')) {
                procesarValidacion(idProduccionActual, decisionActual, '');
            }
        } else {
            // Mostrar modal para observaciones
            $('#modalObservaciones').modal('show');
        }
    });
    
    // Confirmar rechazo
    $('#btnConfirmarRechazo').on('click', function() {
        const observaciones = $('#observaciones_rechazo').val().trim();
        
        if (observaciones === '') {
            alert('Debe ingresar el motivo del rechazo');
            return;
        }
        
        procesarValidacion(idProduccionActual, decisionActual, observaciones);
        $('#modalObservaciones').modal('hide');
    });
    
    // Función para procesar la validación
    function procesarValidacion(id, decision, observaciones) {
        showLoading();
        
        $.ajax({
            url: '<?= BASE_URL ?>/produccion/procesarValidacion',
            method: 'POST',
            data: {
                id_produccion: id,
                decision: decision,
                observaciones: observaciones,
                csrf_token: '<?= generateCsrfToken() ?>'
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                let msg = 'Error al procesar la validación';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }
});
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
