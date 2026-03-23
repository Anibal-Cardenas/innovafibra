<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta #<?= str_pad($venta['id_venta'], 6, '0', STR_PAD_LEFT) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #333; margin: 0; padding: 20px; background: #f9f9f9; }
        .page-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* Header */
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .company-info h1 { margin: 0 0 5px 0; color: #2c3e50; font-size: 24px; text-transform: uppercase; }
        .company-info p { margin: 2px 0; color: #7f8c8d; }
        .invoice-info { text-align: right; }
        .invoice-title { font-size: 20px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .invoice-number { font-size: 16px; color: #e74c3c; font-weight: bold; }
        
        /* Client & Meta Info */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .client-box, .meta-box { width: 48%; }
        .box-title { font-size: 12px; text-transform: uppercase; color: #95a5a6; font-weight: bold; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 3px; }
        .info-row { margin-bottom: 5px; }
        .info-label { font-weight: 600; color: #555; }
        
        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f8f9fa; color: #2c3e50; font-weight: 600; text-align: left; padding: 12px 10px; border-bottom: 2px solid #ddd; text-transform: uppercase; font-size: 12px; }
        td { padding: 12px 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; border-top: 2px solid #2c3e50; border-bottom: none; font-size: 16px; color: #2c3e50; }
        
        /* Footer */
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #95a5a6; border-top: 1px solid #eee; padding-top: 20px; }
        
        /* Buttons */
        .actions { text-align: center; margin-bottom: 20px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; margin: 0 5px; }
        .btn-print { background: #2c3e50; color: #fff; }
        .btn-close { background: #95a5a6; color: #fff; }
        .btn:hover { opacity: 0.9; }

        @media print {
            body { background: #fff; padding: 0; }
            .page-container { box-shadow: none; padding: 0; max-width: 100%; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> Imprimir</button>
        <button onclick="window.close()" class="btn btn-close"><i class="fas fa-times"></i> Cerrar</button>
    </div>

    <div class="page-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1><?= h(getConfigValue('nombre_empresa', APP_NAME)) ?></h1>
                <p><i class="fas fa-map-marker-alt"></i> <?= h(getConfigValue('direccion_empresa', 'Dirección del Taller')) ?></p>
                <p><i class="fas fa-phone"></i> <?= h(getConfigValue('telefono_empresa', '999 999 999')) ?></p>
                <p><i class="fas fa-envelope"></i> <?= h(getConfigValue('email_empresa', 'contacto@napa.com')) ?></p>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">TICKET DE VENTA</div>
                <div class="invoice-number">Nº <?= str_pad($venta['id_venta'], 6, '0', STR_PAD_LEFT) ?></div>
                <p style="margin-top: 5px; color: #7f8c8d;">RUC: <?= h(getConfigValue('ruc_empresa', '00000000000')) ?></p>
            </div>
        </div>

        <!-- Info Sections -->
        <div class="info-section">
            <div class="client-box">
                <div class="box-title">Cliente</div>
                <div class="info-row"><strong><?= h($venta['cliente']) ?></strong></div>
                <?php if($venta['cliente_ruc']): ?>
                    <div class="info-row"><span class="info-label">RUC/DNI:</span> <?= h($venta['cliente_ruc']) ?></div>
                <?php endif; ?>
                <?php if($venta['cliente_direccion']): ?>
                    <div class="info-row"><span class="info-label">Dirección:</span> <?= h($venta['cliente_direccion']) ?></div>
                <?php endif; ?>
                <?php if($venta['cliente_telefono']): ?>
                    <div class="info-row"><span class="info-label">Teléfono:</span> <?= h($venta['cliente_telefono']) ?></div>
                <?php endif; ?>
            </div>
            <div class="meta-box">
                <div class="box-title">Detalles</div>
                <div class="info-row"><span class="info-label">Fecha de Emisión:</span> <?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></div>
                <div class="info-row"><span class="info-label">Orden de Salida:</span> <?= h($venta['codigo_guia_remision']) ?></div>
                <div class="info-row"><span class="info-label">Vendedor:</span> <?= h($venta['vendedor']) ?></div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th class="text-center" width="10%">Cant.</th>
                    <th width="10%">Unidad</th>
                    <th width="50%">Descripción</th>
                    <th class="text-right" width="15%">P. Unit.</th>
                    <th class="text-right" width="15%">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $det): ?>
                <tr>
                    <td class="text-center"><?= number_format($det['cantidad']) ?></td>
                    <td>UND</td>
                    <td>
                        <strong><?= h($det['nombre_producto']) ?></strong>
                    </td>
                    <td class="text-right"><?= formatCurrency($det['precio']) ?></td>
                    <td class="text-right"><?= formatCurrency($det['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
                <!-- Espacio de relleno si es necesario -->
                <tr>
                    <td colspan="5" style="border-bottom: none; padding: 20px;"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="border-bottom: none;"></td>
                    <td class="text-right">TOTAL:</td>
                    <td class="text-right"><?= formatCurrency($venta['precio_total']) ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Observaciones -->
        <?php 
        $obsLimpia = isset($venta['observaciones']) ? trim(explode('[DETALLE_SISTEMA]', $venta['observaciones'])[0]) : '';
        if ($obsLimpia): 
        ?>
        <div style="margin-bottom: 30px; background: #f8f9fa; padding: 15px; border-radius: 4px;">
            <div class="box-title" style="border-bottom: none; margin-bottom: 5px;">Observaciones:</div>
            <div style="font-style: italic; color: #555;"><?= nl2br(h($obsLimpia)) ?></div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>Gracias por su preferencia</p>
            <p>Este documento es un comprobante interno del sistema.</p>
        </div>
    </div>
</body>
</html>