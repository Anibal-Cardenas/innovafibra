<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-balance-scale text-primary me-2"></i>Reporte de Rentabilidad</h2>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print me-2"></i>Imprimir</button>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Este reporte muestra el flujo de caja estimado del mes actual. Para un análisis exacto, asegúrese de haber registrado todas las compras y ventas.
    </div>

    <div class="row">
        <!-- Columna de Ingresos -->
        <div class="col-md-6">
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-arrow-up me-2"></i>INGRESOS (Ventas)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td>Ventas Cobradas (Efectivo/Banco)</td>
                            <td class="text-end fw-bold text-success">S/ <?php echo number_format($ingresos_cobrados ?? 0, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Ventas a Crédito (Por cobrar)</td>
                            <td class="text-end text-muted">S/ <?php echo number_format($ingresos_credito ?? 0, 2); ?></td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold">TOTAL VENTAS</td>
                            <td class="text-end fw-bold fs-5">S/ <?php echo number_format(($ingresos_cobrados ?? 0) + ($ingresos_credito ?? 0), 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Columna de Egresos -->
        <div class="col-md-6">
            <div class="card border-danger mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-arrow-down me-2"></i>EGRESOS (Costos)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td>Compras de Fibra</td>
                            <td class="text-end text-danger">S/ <?php echo number_format($gastos_fibra ?? 0, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Compras de Bolsas</td>
                            <td class="text-end text-danger">S/ <?php echo number_format($gastos_bolsas ?? 0, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Mano de Obra (Producción)</td>
                            <td class="text-end text-danger">S/ <?php echo number_format($gastos_mano_obra ?? 0, 2); ?></td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold">TOTAL COSTOS</td>
                            <td class="text-end fw-bold fs-5 text-danger">S/ <?php echo number_format(($gastos_fibra ?? 0) + ($gastos_bolsas ?? 0) + ($gastos_mano_obra ?? 0), 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultado Final -->
    <div class="card shadow-sm">
        <div class="card-body text-center py-4">
            <h5 class="text-muted text-uppercase">Utilidad Operativa Estimada</h5>
            <?php 
                $total_ingresos = ($ingresos_cobrados ?? 0) + ($ingresos_credito ?? 0);
                $total_egresos = ($gastos_fibra ?? 0) + ($gastos_bolsas ?? 0) + ($gastos_mano_obra ?? 0);
                $utilidad = $total_ingresos - $total_egresos;
                $color = $utilidad >= 0 ? 'text-primary' : 'text-danger';
            ?>
            <h1 class="display-4 fw-bold <?php echo $color; ?>">
                S/ <?php echo number_format($utilidad, 2); ?>
            </h1>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>