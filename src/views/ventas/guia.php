<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Salida - <?= h($venta['codigo_guia_remision']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #333; margin: 0; padding: 20px; background: #f9f9f9; }
        .page-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* Header */
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .company-info h1 { margin: 0 0 5px 0; color: #2c3e50; font-size: 24px; text-transform: uppercase; }
        .company-info p { margin: 2px 0; color: #7f8c8d; }
        .doc-info { text-align: right; border: 2px solid #2c3e50; padding: 15px; border-radius: 4px; min-width: 200px; }
        .doc-ruc { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        .doc-title { font-size: 18px; font-weight: bold; background: #2c3e50; color: #fff; padding: 5px; margin: 5px -15px; text-align: center; }
        .doc-number { font-size: 16px; font-weight: bold; color: #e74c3c; margin-top: 5px; }
        
        /* Info Sections */
        .section-title { background: #f8f9fa; padding: 8px 15px; font-weight: bold; color: #2c3e50; border-left: 4px solid #2c3e50; margin-bottom: 15px; margin-top: 20px; text-transform: uppercase; font-size: 12px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-row { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #555; width: 100px; display: inline-block; }
        
        /* Table */
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #2c3e50; color: #fff; font-weight: 600; text-align: left; padding: 10px; text-transform: uppercase; font-size: 12px; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Footer & Signatures */
        .footer { margin-top: 60px; display: flex; justify-content: space-around; }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-weight: bold; font-size: 12px; }
        
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
            </div>
            <div class="doc-info">
                <div class="doc-ruc">RUC: <?= h(getConfigValue('ruc_empresa', '00000000000')) ?></div>
                <div class="doc-title">ORDEN DE SALIDA</div>
                <div class="doc-number"><?= h($venta['codigo_guia_remision']) ?></div>
            </div>
        </div>
        
        <!-- Info -->
        <div class="info-grid">
            <div>
                <div class="section-title">Punto de Partida</div>
                <div class="info-row"><?= h(getConfigValue('direccion_empresa', 'Dirección del Taller')) ?></div>
                <div class="info-row"><span class="info-label">Fecha Emisión:</span> <?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></div>
            </div>
            <div>
                <div class="section-title">Punto de Llegada</div>
                <div class="info-row"><?= h($venta['cliente_direccion'] ?: 'Dirección del Cliente') ?></div>
                <div class="info-row"><span class="info-label">Fecha Traslado:</span> <?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></div>
            </div>
        </div>
        
        <div class="section-title">Datos del Destinatario</div>
        <div class="info-grid">
            <div>
                <div class="info-row"><span class="info-label">Razón Social:</span> <?= h($venta['cliente']) ?></div>
                <div class="info-row"><span class="info-label">RUC/DNI:</span> <?= h($venta['cliente_ruc']) ?></div>
            </div>
            <div>
                <div class="info-row"><span class="info-label">Teléfono:</span> <?= h($venta['cliente_telefono']) ?></div>
            </div>
        </div>
        
        <!-- Items -->
        <table>
            <thead>
                <tr>
                    <th class="text-center" width="10%">Cant.</th>
                    <th class="text-center" width="10%">Unidad</th>
                    <th width="60%">Descripción</th>
                    <th class="text-right" width="20%">Peso Total Ref.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $det): ?>
                <tr>
                    <td class="text-center"><?= number_format($det['cantidad']) ?></td>
                    <td class="text-center">UND</td>
                    <td><?= h($det['nombre_producto']) ?></td>
                    <td class="text-right">-</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php 
        $obsLimpia = isset($venta['observaciones']) ? trim(explode('[DETALLE_SISTEMA]', $venta['observaciones'])[0]) : '';
        if ($obsLimpia): 
        ?>
        <div style="margin-bottom: 20px; font-style: italic; color: #555;">
            <strong>Observaciones:</strong> <?= nl2br(h($obsLimpia)) ?>
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <div class="signature-box">
                <div class="signature-line">CONFORME REMITENTE</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">CONFORME DESTINATARIO</div>
            </div>
        </div>
    </div>
</body>
</html>
