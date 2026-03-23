<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-money-bill-wave"></i> <?= h($title) ?></h2>
        <button onclick="window.print()" class="btn btn-secondary no-print">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
    
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reportes/nomina" class="row g-3">
                <div class="col-md-6">
                    <label for="mes" class="form-label">Mes:</label>
                    <input type="month" class="form-control" id="mes" name="mes" value="<?= h($mes) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Trabajadores</h6>
                    <h3><?= number_format($resumen['num_trabajadores']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Nómina</h6>
                    <h3><?= formatCurrency($resumen['total_nomina']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Tarifa Promedio</h6>
                    <h3><?= formatCurrency($resumen['tarifa_promedio']) ?></h3>
                    <small>por bolsa</small>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (empty($nomina)): ?>
    <div class="alert alert-info">
        No hay datos de nómina para el mes seleccionado.
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Nómina - <?= date('F Y', strtotime($mes . '-01')) ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Trabajador</th>
                            <th class="text-center">Producciones</th>
                            <th class="text-end">Aprobadas</th>
                            <th class="text-end">Rechazadas</th>
                            <th class="text-end">Tarifa</th>
                            <th class="text-end">Total a Pagar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalGeneral = 0;
                        foreach ($nomina as $trab): 
                            $totalGeneral += $trab['total_pagar'];
                        ?>
                        <tr>
                            <td><strong><?= h($trab['nombre_completo']) ?></strong></td>
                            <td class="text-center"><?= number_format($trab['num_producciones']) ?></td>
                            <td class="text-end">
                                <span class="badge bg-success"><?= number_format($trab['bolsas_aprobadas']) ?></span>
                            </td>
                            <td class="text-end">
                                <?php if ($trab['bolsas_rechazadas'] > 0): ?>
                                <span class="badge bg-danger"><?= number_format($trab['bolsas_rechazadas']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= formatCurrency($trab['tarifa_por_bolsa']) ?></td>
                            <td class="text-end">
                                <strong><?= formatCurrency($trab['total_pagar']) ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <td colspan="5" class="text-end"><strong>TOTAL GENERAL:</strong></td>
                            <td class="text-end"><strong><?= formatCurrency($totalGeneral) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle"></i>
        <strong>Nota:</strong> Solo se incluyen las producciones aprobadas. Las producciones rechazadas no generan pago.
    </div>
    
    <?php endif; ?>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
