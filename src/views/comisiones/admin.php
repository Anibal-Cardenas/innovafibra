<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-money-bill-wave"></i> Administrar Comisiones</h2>
            <p class="text-muted">Gestionar el cálculo y pago de comisiones de operadores</p>
        </div>
    </div>

    <?php if (isset($tabla_no_existe) && $tabla_no_existe): ?>
    <!-- Mensaje de instalación requerida -->
    <div class="alert alert-warning border-warning" style="border-left: 5px solid #ffc107;">
        <div class="row align-items-center">
            <div class="col-md-1 text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
            </div>
            <div class="col-md-11">
                <h4 class="alert-heading"><i class="fas fa-database"></i> Sistema de Comisiones No Instalado</h4>
                <p class="mb-3">Las tablas del sistema de comisiones aún no han sido creadas. Necesitas ejecutar la migración SQL para activar esta funcionalidad.</p>
                
                <hr>
                
                <h5><i class="fas fa-tools"></i> Pasos para Activar el Sistema:</h5>
                <ol class="mb-3">
                    <li><strong>Abre phpMyAdmin:</strong> <a href="http://localhost/phpmyadmin" target="_blank" class="alert-link">http://localhost/phpmyadmin</a></li>
                    <li><strong>Selecciona la base de datos:</strong> <code>sistema_napa</code></li>
                    <li><strong>Ve a la pestaña:</strong> "SQL"</li>
                    <li><strong>Abre el archivo:</strong> <code>c:\xampp\htdocs\Napa\database\migrations\implementar_sistema_roles.sql</code></li>
                    <li><strong>Copia todo el contenido</strong> y pégalo en el editor SQL</li>
                    <li><strong>Click en "Continuar"</strong> y espera a que termine</li>
                </ol>
                
                <div class="bg-white p-3 rounded border">
                    <h6 class="text-dark"><i class="fas fa-terminal"></i> O desde Terminal PowerShell:</h6>
                    <pre class="mb-0 text-dark"><code>cd c:\xampp\htdocs\Napa
c:\xampp\mysql\bin\mysql.exe -u root sistema_napa < database\migrations\implementar_sistema_roles.sql</code></pre>
                </div>
                
                <hr>
                
                <p class="mb-0">
                    <i class="fas fa-book"></i> <strong>Documentación completa:</strong> 
                    <a href="<?= BASE_URL ?>/../../SOLUCION_ERROR_COMISIONES.md" class="alert-link">SOLUCION_ERROR_COMISIONES.md</a>
                    <br>
                    <small class="text-muted">⏱️ Tiempo estimado: 2-3 minutos</small>
                </p>
            </div>
        </div>
    </div>
    
    <div class="card border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> ¿Qué incluye el Sistema de Comisiones?</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-check-circle text-success"></i> Funcionalidades para Administrador:</h6>
                    <ul>
                        <li>Calcular comisiones por periodo</li>
                        <li>Registrar pagos de comisiones</li>
                        <li>Ver historial de pagos</li>
                        <li>Anular comisiones</li>
                        <li>Gestionar tarifas de operadores</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-check-circle text-success"></i> Funcionalidades para Operador:</h6>
                    <ul>
                        <li>Ver producción diaria del mes</li>
                        <li>Consultar comisiones estimadas</li>
                        <li>Ver historial de comisiones pagadas</li>
                        <li>Detalle de cada comisión</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>

    <!-- Producción Pendiente de Procesar (Tiempo Real) -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-tasks"></i> Producción Pendiente de Procesar</h5>
            <span class="badge bg-white text-primary">Actualizado: En tiempo real</span>
        </div>
        <div class="card-body">
            <?php if (empty($produccion_pendiente)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> No hay producción pendiente de procesar. Todo está al día.
                </div>
            <?php else: ?>
                <div class="alert alert-info py-2 mb-3">
                    <small><i class="fas fa-info-circle"></i> Aquí se muestra la producción aprobada que aún no ha sido convertida en una comisión fija.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Operador</th>
                                <th>Rango Fechas</th>
                                <th class="text-center">Prod. Pendientes</th>
                                <th class="text-center">Total Bolsas</th>
                                <th class="text-end">Monto Estimado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produccion_pendiente as $pend): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= h($pend['nombre_completo']) ?></div>
                                    <small class="text-muted">Tarifa: S/ <?= number_format($pend['tarifa_por_bolsa'], 2) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= date('d/m/Y', strtotime($pend['fecha_inicio'])) ?>
                                        <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                        <?= date('d/m/Y', strtotime($pend['fecha_fin'])) ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $pend['total_producciones'] ?></td>
                                <td class="text-center fw-bold"><?= number_format($pend['total_bolsas']) ?></td>
                                <td class="text-end text-success fw-bold">S/ <?= number_format($pend['monto_estimado'], 2) ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary btn-sm btn-generar-corte"
                                            data-id="<?= $pend['id_usuario'] ?>"
                                            data-inicio="<?= $pend['fecha_inicio'] ?>"
                                            data-fin="<?= $pend['fecha_fin'] ?>"
                                            title="Generar Corte y Calcular Comisión">
                                        <i class="fas fa-calculator"></i> Generar Corte
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Calcular Nueva Comisión (Manual) -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white" data-bs-toggle="collapse" data-bs-target="#collapseCalculo" style="cursor: pointer;">
            <h5 class="mb-0"><i class="fas fa-calculator"></i> Cálculo Manual de Comisión <small class="float-end"><i class="fas fa-chevron-down"></i></small></h5>
        </div>
        <div id="collapseCalculo" class="collapse">
            <div class="card-body">
                <form id="formCalcularComision">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Operador *</label>
                            <select name="id_operario" id="calc_id_operario" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($operadores as $operador): ?>
                                <option value="<?= $operador['id_usuario'] ?>">
                                    <?= h($operador['nombre_completo']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio *</label>
                            <input type="date" name="fecha_inicio" id="calc_fecha_inicio" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin *</label>
                            <input type="date" name="fecha_fin" id="calc_fecha_fin" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Calcular
                            </button>
                        </div>
                    </div>
                </form>
                <div id="resultadoCalculo" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Comisiones Pendientes de Pago (Agrupadas) -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-clock"></i> Comisiones Pendientes de Pago</h5>
        </div>
        <div class="card-body">
            <?php if (empty($comisiones_agrupadas)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay comisiones pendientes de pago.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Operador</th>
                                <th class="text-center">Total Bolsas</th>
                                <th class="text-end">Deuda Total</th>
                                <th class="text-center">Antigüedad</th>
                                <th class="text-center">Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comisiones_agrupadas as $idOp => $grupo): ?>
                            <!-- Fila Resumen -->
                            <tr class="table-warning border-bottom border-dark" style="cursor: pointer;" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-op-<?= $idOp ?>" 
                                aria-expanded="false">
                                <td>
                                    <strong><?= h($grupo['nombre']) ?></strong>
                                    <br><small class="text-muted"><?= count($grupo['items']) ?> corte(s) pendiente(s)</small>
                                </td>
                                <td class="text-center fw-bold fs-5"><?= number_format($grupo['total_bolsas']) ?></td>
                                <td class="text-end fw-bold fs-5 text-dark">S/ <?= number_format($grupo['total_monto'], 2) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $grupo['max_dias_pendiente'] > 15 ? 'danger' : 'warning' ?> text-dark">
                                        Max <?= $grupo['max_dias_pendiente'] ?> días
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-dark">
                                        <i class="fas fa-chevron-down"></i> Ver Cortes
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Fila Detalle (Acordeón) -->
                            <tr>
                                <td colspan="5" class="p-0 border-0">
                                    <div class="collapse bg-light" id="collapse-op-<?= $idOp ?>">
                                        <div class="p-3">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead>
                                                    <tr class="text-muted small">
                                                        <th>ID Corte</th>
                                                        <th>Periodo</th>
                                                        <th class="text-center">Bolsas</th>
                                                        <th class="text-end">Monto</th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($grupo['items'] as $com): ?>
                                                    <tr>
                                                        <td>#<?= $com['id_comision'] ?></td>
                                                        <td>
                                                            <?= date('d/m/y', strtotime($com['fecha_inicio'])) ?> - 
                                                            <?= date('d/m/y', strtotime($com['fecha_fin'])) ?>
                                                        </td>
                                                        <td class="text-center"><?= number_format($com['total_bolsas_producidas']) ?></td>
                                                        <td class="text-end">S/ <?= number_format($com['monto_total'], 2) ?></td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="<?= BASE_URL ?>/comisiones/detalle/<?= $com['id_comision'] ?>" 
                                                                   class="btn btn-outline-info" title="Ver Detalle">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-success btn-pagar" 
                                                                        data-id="<?= $com['id_comision'] ?>"
                                                                        data-operador="<?= h($com['operario']) ?>"
                                                                        data-monto="<?= $com['monto_total'] ?>"
                                                                        title="Pagar este corte">
                                                                    <i class="fas fa-dollar-sign"></i> Pagar
                                                                </button>
                                                                <button type="button" class="btn btn-outline-danger btn-anular" 
                                                                        data-id="<?= $com['id_comision'] ?>"
                                                                        title="Anular (Borrar)">
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
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historial de Pagos Recientes -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-history"></i> Últimos Pagos Realizados</h5>
        </div>
        <div class="card-body">
            <?php if (empty($historial_pagos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay pagos registrados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Operador</th>
                                <th>Periodo</th>
                                <th class="text-end">Monto</th>
                                <th>Fecha Pago</th>
                                <th>Método</th>
                                <th>Pagado Por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial_pagos as $pago): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/comisiones/detalle/<?= $pago['id_comision'] ?>">
                                        #<?= $pago['id_comision'] ?>
                                    </a>
                                </td>
                                <td><?= h($pago['operario']) ?></td>
                                <td>
                                    <small>
                                        <?= date('d/m', strtotime($pago['fecha_inicio'])) ?> - 
                                        <?= date('d/m/Y', strtotime($pago['fecha_fin'])) ?>
                                    </small>
                                </td>
                                <td class="text-end">S/ <?= number_format($pago['monto_total'], 2) ?></td>
                                <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst($pago['metodo_pago']) ?></span></td>
                                <td><small><?= h($pago['pagado_por']) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php endif; // Fin de verificación tabla_no_existe ?>
</div>

<!-- Modal: Registrar Pago -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-dollar-sign"></i> Registrar Pago de Comisión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPago">
                <div class="modal-body">
                    <input type="hidden" name="id_comision" id="pagoIdComision">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Operador:</label>
                        <div id="pagoOperador"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Monto a Pagar:</label>
                        <div class="h4 text-success" id="pagoMonto"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Pago *</label>
                        <input type="date" name="fecha_pago" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Método de Pago *</label>
                        <select name="metodo_pago" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nº de Operación</label>
                        <input type="text" name="numero_operacion" class="form-control" 
                               placeholder="Opcional: número de referencia">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Generar corte desde tabla de pendientes
    $('.btn-generar-corte').on('click', function() {
        const btn = $(this);
        const id = btn.data('id');
        const inicio = btn.data('inicio');
        const fin = btn.data('fin');
        
        // Rellenar formulario oculto
        $('#calc_id_operario').val(id);
        $('#calc_fecha_inicio').val(inicio);
        $('#calc_fecha_fin').val(fin);
        
        // Mostrar confirmación visual o enviar directo
        if(confirm('¿Generar corte de comisión para este periodo?')) {
            // Abrir el acordeón si está cerrado (opcional, visual)
            $('#collapseCalculo').collapse('show');
            
            // Disparar envío del formulario
            $('#formCalcularComision').submit();
        }
    });

    // Calcular comisión
    $('#formCalcularComision').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= BASE_URL ?>/comisiones/calcular',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#resultadoCalculo').html(
                        '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + 
                        response.message + '</div>'
                    );
                    setTimeout(() => location.reload(), 1500);
                } else {
                    $('#resultadoCalculo').html(
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + 
                        response.message + '</div>'
                    );
                }
            },
            error: function() {
                $('#resultadoCalculo').html(
                    '<div class="alert alert-danger">Error al procesar la solicitud</div>'
                );
            }
        });
    });
    
    // Abrir modal de pago
    $('.btn-pagar').on('click', function() {
        const id = $(this).data('id');
        const operador = $(this).data('operador');
        const monto = $(this).data('monto');
        
        $('#pagoIdComision').val(id);
        $('#pagoOperador').text(operador);
        $('#pagoMonto').text('S/ ' + parseFloat(monto).toFixed(2));
        
        new bootstrap.Modal($('#modalPago')).show();
    });
    
    // Registrar pago
    $('#formPago').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= BASE_URL ?>/comisiones/registrar-pago',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
    
    // Anular comisión
    $('.btn-anular').on('click', function() {
        const id = $(this).data('id');
        const motivo = prompt('Ingrese el motivo de la anulación:');
        
        if (motivo && motivo.trim() !== '') {
            if (confirm('¿Está seguro de anular esta comisión?')) {
                $.ajax({
                    url: '<?= BASE_URL ?>/comisiones/anular',
                    method: 'POST',
                    data: { id_comision: id, motivo: motivo },
                    dataType: 'json',
                    success: function(response) {
                        alert(response.message);
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            }
        }
    });
});
</script>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
