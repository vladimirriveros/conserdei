<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Orden de Compra #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
        }

        .nota-compra {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 40px);
            /* Altura completa menos el margen */
            width: 100%;
        }

        .content {
            flex: 1 1 auto;
            padding: 0 0 20px 0;
            display: flex;
            flex-direction: column;
        }

        .company-proveedor {
            padding: 8px 12px;
            background: #f8f9fc;
            border-bottom: 1px dashed #ddd;
            flex-shrink: 0;
        }

        .productos-section {
            padding: 10px 12px;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }

        .section-title {
            color: #1a237e;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 5px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #1a237e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        .table-container {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            border: 1px solid #ddd;
            margin-bottom: 0;
        }

        thead {
            display: table-header-group;
        }

        tbody {
            display: table-row-group;
        }

        th {
            background: #1a237e;
            color: white;
            padding: 8px 6px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 10px;
            border: 1px solid #1a237e;
        }

        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .empty-rows-container {
            border: 1px dashed #ccc;
            border-radius: 4px;
            margin-top: 5px;
            width: 100%;
        }

        .empty-row {
            display: flex;
            padding: 4px 8px;
            border-bottom: 1px dashed #eee;
            color: #999;
            font-size: 10px;
        }

        .empty-row:last-child {
            border-bottom: none;
        }

        .empty-col-1 {
            width: 5%;
        }

        .empty-col-2 {
            width: 40%;
        }

        .empty-col-3 {
            width: 15%;
        }

        .empty-col-4 {
            width: 10%;
        }

        .empty-col-5 {
            width: 15%;
        }

        .empty-col-6 {
            width: 15%;
        }

        .total-section {
            margin-top: 10px;
            border-top: 2px solid #1a237e;
            padding-top: 8px;
            flex-shrink: 0;
        }

        .grand-total {
            font-size: 14px;
            color: #1a237e;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-label {
            font-weight: 600;
        }

        .total-value {
            font-weight: bold;
            font-size: 14px;
        }

        .observaciones {
            margin: 8px 12px 0 12px;
            padding: 8px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            font-size: 11px;
            border: 1px solid #ffeeba;
            flex-shrink: 0;
        }

        .footer {
            background: #1a237e;
            color: white;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            flex-shrink: 0;
            margin-top: auto;
            width: 100%;
        }

        .footer-contact {
            display: flex;
            gap: 20px;
        }

        .footer-contact span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            /* Centrado vertical */
            justify-content: center;
            /* Centrado horizontal */
        }

        .logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .company-details {
            font-size: 11px;
            line-height: 1.5;
        }

        .company-details strong {
            font-size: 14px;
            color: #1a237e;
            display: block;
            margin-bottom: 5px;
        }

        .watermark {
            position: fixed;
            bottom: 20px;
            right: 20px;
            opacity: 0.03;
            font-size: 60px;
            font-weight: bold;
            color: #1a237e;
            z-index: -1;
            transform: rotate(-15deg);
            pointer-events: none;
        }

        @media print {
            body {
                background: white;
            }

            .nota-compra {
                box-shadow: none;
                margin: 0;
                min-height: 100vh;
                max-width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>
    <div class="nota-compra">
        <div class="content">
            {{-- INFORMACIÓN EMPRESA Y PROVEEDOR --}}
            <div class="company-proveedor">
                @php
                    // Ruta del logo
                    $logoPath = public_path('images/conserdei.png');
                    // $logoPath = public_path('images/plome.jpg');
                    // $logoPath = public_path('images/otro.png');
                    $logoBase64 = null;
                    $logoError = null;

                    // Intentar cargar la imagen
                    if (file_exists($logoPath)) {
                        try {
                            $logoData = file_get_contents($logoPath);
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mimeType = finfo_file($finfo, $logoPath);
                            finfo_close($finfo);

                            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoData);
                        } catch (\Exception $e) {
                            $logoError = 'Error al leer la imagen: ' . $e->getMessage();
                        }
                    } else {
                        $logoError = 'Archivo no encontrado en: ' . $logoPath;

                        // Buscar en ubicaciones alternativas
                        $alternativePaths = [
                            // public_path('img/conserdei.png'),
                            public_path('img/plome.jpg'),
                            // public_path('assets/images/conserdei.png'),
                            public_path('assets/images/plome.jpg'),
                            // public_path('images/logo.png'),
                            // storage_path('app/public/images/conserdei.png'),
                            storage_path('app/public/images/plome.jpg'),
                            // base_path('resources/img/conserdei.png'),
                        ];

                        foreach ($alternativePaths as $altPath) {
                            if (file_exists($altPath)) {
                                $logoPath = $altPath;
                                try {
                                    $logoData = file_get_contents($logoPath);
                                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                    $mimeType = finfo_file($finfo, $logoPath);
                                    finfo_close($finfo);

                                    $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoData);
                                    $logoError = null;
                                    break;
                                } catch (\Exception $e) {
                                    $logoError = 'Error al leer la imagen alternativa: ' . $e->getMessage();
                                }
                            }
                        }
                    }

                    $empresaNombre = config('app.name', 'CONSERDEI');
                    $empresaTelefono = config('app.telefono', '74006143');
                    $empresaEmail = config('app.email', 'conserdei.luisfernando@gmail.com');
                    $empresaDireccion = config('app.direccion', 'Zona 12 de Octubre, Calle 9 # 178');
                    $empresaNit = config('app.nit', '6824235016');

                    $productos = $compra->detalles->isNotEmpty()
                        ? $compra->detalles
                        : session('carrito_compra_' . $compra->id, []);
                    $totalProductos = count($productos);
                    $lineasVacias = max(0, 25 - $totalProductos);
                @endphp

                <table width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td width="20%" style="vertical-align: top;">
                            <div class="logo-container"
                                style="display: flex; align-items: center; justify-content: center;">
                                @if ($logoBase64)
                                    <img src="{{ $logoBase64 }}" class="logo" alt="Logo CONSERDEI"
                                        style="max-width: 100%; max-height: 100%; object-fit: contain; display: block; margin: auto;">
                                @else
                                    <div
                                        style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f5f5f5; color:#999; font-size:10px; text-align:center; padding:5px;">
                                        {{ $logoError ? 'Error' : 'Logo' }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        <td width="50%" style="vertical-align: top;">
                            <div class="company-details">
                                <strong>{{ $empresaNombre }}</strong>
                                <div>{{ $empresaDireccion }}</div>
                                <div>Tel: (591) {{ $empresaTelefono }}</div>
                                <div>Email: {{ $empresaEmail }}</div>
                                <div>NIT: {{ $empresaNit }}</div>
                            </div>
                        </td>

                        <td width="30%" style="text-align:right; vertical-align: top;">
                            <h2 style="margin:0;color:#1a237e;">ORDEN DE COMPRA</h2>
                            <div
                                style="background:#1a237e;color:white;padding:8px;margin-top:5px;font-weight:bold;font-size:16px;border-radius:4px;display:inline-block;">
                                #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}
                            </div>
                            <div style="margin-top:8px;">
                                <strong>FECHA:</strong>
                                {{ $compra->fecha ? date('d/m/Y', strtotime($compra->fecha)) : date('d/m/Y') }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- DETALLE DE PRODUCTOS --}}
            <div class="productos-section">
                <div class="section-title">
                    DETALLE DE PRODUCTOS SOLICITADOS
                </div>

                @php
                    $total = 0;
                @endphp

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="40%" class="text-left">DESCRIPCIÓN DEL PRODUCTO</th>
                                <th width="15%" class="text-left">MARCA</th>
                                <th width="10%" class="text-center">CANTIDAD</th>
                                <th width="15%" class="text-right">PRECIO UNIT.</th>
                                <th width="15%" class="text-right">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $index => $item)
                                @php
                                    if ($item instanceof \App\Models\DetalleCompra) {
                                        $producto = $item->producto->nombre ?? 'Producto sin nombre';
                                        $marca = $item->producto->marca ?? 'Sin marca';
                                        $cantidad = $item->cantidad;
                                        $precio = $item->precio_unitario ?? 0;
                                    } else {
                                        $producto = $item['producto_nombre'] ?? 'Producto no tiene nada nombre';
                                        $marca = $item['marca'] ?? 'Sin marca';
                                        $cantidad = $item['cantidad'] ?? 0;
                                        $precio = $item['precio_unitario'] ?? 0;
                                    }
                                    $subtotal = $cantidad * $precio;
                                    $total += $subtotal;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $producto }}</td>
                                    <td>{{ $marca }}</td>
                                    <td class="text-center">{{ number_format($cantidad, 0) }}</td>
                                    <td class="text-right">Bs {{ number_format($precio, 2) }}</td>
                                    <td class="text-right">Bs {{ number_format($subtotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 20px; color: #666;">
                                        No hay productos registrados en esta orden
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- LÍNEAS VACÍAS PARA RELLENAR ESPACIO --}}
                    @if ($lineasVacias > 0)
                        <div class="empty-rows-container">
                            @for ($i = 1; $i <= $lineasVacias; $i++)
                                <div class="empty-row">
                                    <span class="empty-col-1 text-center">-</span>
                                    <span class="empty-col-2">-</span>
                                    <span class="empty-col-3">-</span>
                                    <span class="empty-col-4 text-center">-</span>
                                    <span class="empty-col-5 text-right">-</span>
                                    <span class="empty-col-6 text-right">-</span>
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>

                {{-- TOTALES --}}
                <div class="total-section">
                    <div class="grand-total">
                        <span class="total-label">TOTAL ORDEN DE COMPRA:</span>
                        <span class="total-value">Bs {{ number_format($total, 2) }}</span>
                    </div>
                </div>

                {{-- OBSERVACIONES --}}
                @if ($compra->observaciones)
                    <div class="observaciones">
                        <strong>OBSERVACIONES Y CONDICIONES:</strong>
                        {{ $compra->observaciones }}
                    </div>
                @endif
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-contact">
                <span>(591) {{ $empresaTelefono }}</span>
                <span>{{ $empresaEmail }}</span>
                <span>www.{{ strtolower(str_replace(' ', '', $empresaNombre)) }}.com</span>
            </div>
            <div style="font-size:9px; opacity:0.9;">
                {{ date('d/m/Y H:i') }}
            </div>
        </div>

        {{-- WATERMARK --}}
        <div class="watermark">
            ORDEN DE COMPRA
        </div>
    </div>
</body>

</html>
