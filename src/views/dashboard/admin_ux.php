<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid py-4">
    
    <!-- SECCIÓN 1: SALUD FINANCIERA (LO MÁS IMPORTANTE) -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-muted mb-0"><i class="fas fa-chart-line me-2"></i>Resumen Financiero</h5>
            
            <form class="d-flex" method="GET" action="<?= BASE_URL ?>/dashboard">
                <select name="mes" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <?php
                    $meses = [
                        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
                        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
                        '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                    ];
                    foreach ($meses as $k => $v) {
                        $selected = ($k == $mes_seleccionado) ? 'selected' : '';
                        echo "<option value=\"$k\" $selected>$v</option>";
                    }
                    ?>
                </select>
                <select name="anio" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php
                    $anio_actual = date('Y');
                    for ($i = $anio_actual; $i >= $anio_actual - 2; $i--) {
                        $selected = ($i == $anio_seleccionado) ? 'selected' : '';
                        echo "<option value=\"$i\" $selected>$i</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
        
        <!-- Ingresos -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-5">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Ingresos Totales</h6>
                            <h2 class="mb-0 text-success fw-bold">S/ <?php echo number_format($financiero['ingresos'], 2); ?></h2>
                        </div>
                        <div class="icon-circle bg-success bg-opacity-10 text-success">
                            <i class="fas fa-hand-holding-usd fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Ingresos brutos del mes</small>
                </div>
            </div>
        </div>

        <!-- Gastos -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-5">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Gastos Operativos</h6>
                            <h2 class="mb-0 text-danger fw-bold">S/ <?php echo number_format($financiero['gastos'], 2); ?></h2>
                        </div>
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Compras + Mano de Obra estimada</small>
                </div>
            </div>
        </div>

        <!-- Utilidad -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-5" style="background: linear-gradient(to right, #ffffff, #f8f9fa);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Utilidad Estimada</h6>
                            <h2 class="mb-0 text-primary fw-bold">S/ <?php echo number_format($financiero['utilidad'], 2); ?></h2>
                        </div>
                    </div>
                    <small class="text-muted">Dinero disponible aproximado</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: OPERACIONES Y ALERTAS -->
    <div class="row">
        <!-- Columna Izquierda: Estado del Taller -->
        <div class="col-lg-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-industry me-2"></i>Estado del Taller</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded bg-light">
                                <h3 class="fw-bold text-dark"><?php echo number_format($produccion_hoy); ?></h3>
                                <span class="text-muted">Bolsas Producidas Hoy</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded bg-light">
                                <h3 class="fw-bold text-warning"><?php echo $alertas['validaciones']; ?></h3>
                                <span class="text-muted">Pendientes de Validar</span>
                                <?php if($alertas['validaciones'] > 0): ?>
                                    <a href="<?php echo BASE_URL; ?>/produccion/validar" class="btn btn-sm btn-warning mt-2 w-100">Ir a Validar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded bg-light">
                                <h3 class="fw-bold text-info">
                                    <?php 
                                    // Buscar stock de napa
                                    $stock = 0;
                                    foreach($inventario_critico as $item) if($item['tipo_item'] == 'producto_terminado') $stock += $item['cantidad'];
                                    echo number_format($stock);
                                    ?>
                                </h3>
                                <span class="text-muted">Stock Napa Lista</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>