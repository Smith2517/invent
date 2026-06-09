<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta Patrimonial - <?= htmlspecialchars($asset['custom_code']) ?></title>
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #333;
            padding: 20px;
        }
        .label-container {
            width: 80mm;
            height: 50mm;
            background-color: #fff;
            border: 1px dashed #bbb;
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
        }
        .label-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            padding-right: 8px;
        }
        .label-header {
            font-size: 8px;
            font-weight: 700;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .label-code {
            font-size: 16px;
            font-weight: 700;
            color: #000;
            margin: 2px 0;
            line-height: 1;
        }
        .label-info {
            font-size: 7.5px;
            color: #555;
            line-height: 1.3;
        }
        .label-info-item {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .label-footer {
            font-size: 6px;
            color: #999;
            text-transform: uppercase;
        }
        .label-qr-container {
            width: 32mm;
            height: 32mm;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 4px;
            background-color: #fff;
        }
        .label-qr-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Estilos específicos para impresión térmica (ajusta el tamaño a la etiqueta exacta) */
        @media print {
            body {
                background-color: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                min-height: auto !important;
            }
            .label-container {
                width: 80mm;
                height: 50mm;
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 8px 12px !important;
                page-break-inside: avoid;
                page-break-after: always;
            }
            @page {
                size: 80mm 50mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="label-container">
        <!-- Detalles del Bien -->
        <div class="label-details">
            <div>
                <div class="label-header">Control Patrimonial</div>
                <div class="label-code"><?= htmlspecialchars($asset['custom_code']) ?></div>
            </div>
            
            <div class="label-info">
                <div class="label-info-item"><strong>Tipo:</strong> <?= htmlspecialchars($asset['type']) ?></div>
                <div class="label-info-item"><strong>Marca:</strong> <?= htmlspecialchars($asset['brand'] ?? 'S/M') ?></div>
                <div class="label-info-item"><strong>Modelo:</strong> <?= htmlspecialchars($asset['model'] ?? 'S/M') ?></div>
                <div class="label-info-item"><strong>Serie:</strong> <?= htmlspecialchars($asset['serial_number'] ?? 'S/N') ?></div>
            </div>
            
            <div class="label-footer">
                EPS RIOJA
            </div>
        </div>

        <!-- Código QR -->
        <div class="label-qr-container">
            <?php if ($asset['qr_code']): ?>
                <img src="<?= BASE_URL . $asset['qr_code'] ?>" alt="QR Code">
            <?php else: ?>
                <span style="font-size: 8px; color: #ccc;">Sin QR</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['print']) && $_GET['print'] === 'true'): ?>
        <script>
            window.onload = function() {
                window.print();
                // Cerrar la pestaña después de imprimir (o cuando se cancela el diálogo)
                setTimeout(function() {
                    window.close();
                }, 500);
            }
        </script>
    <?php endif; ?>

</body>
</html>
