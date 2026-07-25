<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Compra #{{ $compra->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #1e3a5f;
            margin: 0;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e88e5;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #1e88e5;
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .header h3 {
            color: #5f7d9c;
            margin: 4px 0 0;
            font-size: 12px;
            font-weight: normal;
        }

        .empresa-info {
            text-align: center;
            margin-bottom: 15px;
            font-size: 10px;
            color: #4a6a8a;
        }

        .info-section {
            margin-bottom: 18px;
            padding: 10px 12px;
            background-color: #f0f7ff;
            border-radius: 5px;
            border-left: 4px solid #1e88e5;
        }

        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-section td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .info-section .label {
            font-weight: bold;
            width: 100px;
            color: #2c5282;
        }

        .info-section .value {
            color: #1e3a5f;
        }

        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }

        .productos-table th {
            background-color: #1e88e5;
            color: white;
            padding: 7px 6px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }

        .productos-table td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            font-size: 9px;
        }

        .productos-table tbody tr:hover {
            background-color: #f0f7ff;
        }

        .productos-table tfoot {
            font-weight: bold;
            background-color: #e6f0fa;
        }

        .productos-table tfoot td {
            padding: 7px 6px;
            border-top: 2px solid #1e88e5;
            font-size: 9px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .firma-section {
            margin-top: 35px;
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        .firma-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .firma-line {
            border-top: 1px solid #4a6a8a;
            margin-top: 30px;
            padding-top: 6px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .footer {
            position: fixed;
            bottom: 15px;
            left: 15px;
            right: 15px;
            text-align: center;
            font-size: 8px;
            color: #7a9bcb;
            border-top: 1px dashed #bdd4e7;
            padding-top: 10px;
        }

        .badge-success {
            background-color: #1e88e5;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .resumen-box {
            margin-top: 15px;
            padding: 10px 12px;
            background-color: #f0f7ff;
            border-radius: 5px;
            border: 1px solid #cbdde9;
        }

        .resumen-box table {
            width: 280px;
            margin-left: auto;
        }

        .resumen-box td {
            padding: 4px 6px;
            font-size: 9px;
        }

        .resumen-box .total-final {
            font-size: 12px;
            font-weight: bold;
            color: #1e88e5;
        }

        .fecha-generacion {
            font-size: 8px;
            color: #7a9bcb;
            margin-top: 8px;
            text-align: right;
        }

        h4 {
            margin-bottom: 8px;
            color: #1e88e5;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>NOTA DE COMPRA</h1>
        <h3>Comprobante de Recepción de Productos</h3>
    </div>

    <div class="empresa-info">
        <strong>CONSERDEI</strong><br>
        Sistema de Gestión de Inventarios<br>
        {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td class="label">N° de Compra:</td>
                <td class="value"><strong>#{{ $compra->id }}</strong></td>
                <td class="label">Fecha:</td>
                <td class="value">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Proveedor:</td>
                <td class="value">{{ $compra->proveedor->nombre ?? 'Sin proveedor' }}</td>
                <td class="label">Estado:</td>
                <td class="value"><span class="badge-success">RECIBIDO</span></td>
            </tr>
            <!-- === NUEVA FILA DEL USUARIO === -->
            @if (isset($compra->usuario) || isset($compra->user))
                <tr>
                    <td class="label">Usuario comprador:</td>
                    <td class="value" colspan="3">
                        <strong>
                            {{ $compra->usuario->name ?? ($compra->user->name ?? ($compra->user_id ?? 'No especificado')) }}
                        </strong>
                        @if (isset($compra->usuario) && $compra->usuario->email)
                            <br><span style="font-size: 8px;">{{ $compra->usuario->email }}</span>
                        @endif
                    </td>
                </tr>
            @endif
            <!-- ========================== -->
            @if ($sucursal ?? null)
                <tr>
                    <td class="label">Sucursal destino:</td>
                    <td class="value" colspan="3">{{ $sucursal->nombre }}</td>
                </tr>
            @endif
            @if ($compra->observaciones)
                <tr>
                    <td class="label">Observaciones:</td>
                    <td class="value" colspan="3">{{ $compra->observaciones }}</td>
                </tr>
            @endif
        </table>
    </div>

    <h4>Detalle de Productos Recibidos</h4>

    <table class="productos-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="15%">Código</th>
                <th width="25%">Producto</th>
                <th width="15%">Lote</th>
                <th width="10%">Cantidad</th>
                <th width="15%">Precio Unit.</th>
                <th width="15%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detalles as $index => $detalle)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $detalle->producto->codigo ?? ($detalle['producto_codigo'] ?? '') }}</td>
                    <td class="text-left">{{ $detalle->producto->nombre ?? ($detalle['producto_nombre'] ?? '') }}</td>
                    <td>{{ $detalle->lote->codigo_lote ?? ($detalle['codigo_lote'] ?? '') }}</td>
                    <td>{{ number_format($detalle->cantidad ?? ($detalle['cantidad'] ?? 0), 0) }}</td>
                    <td class="text-right">
                        ${{ number_format($detalle->precio_unitario ?? ($detalle['precio_unitario'] ?? 0), 2) }}</td>
                    <td class="text-right">${{ number_format($detalle->subtotal ?? ($detalle['subtotal'] ?? 0), 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>${{ number_format($compra->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="resumen-box">
        <table>

            <tr>
                <td class="text-right total-final">TOTAL A PAGAR:</td>
                <td class="text-right total-final">${{ number_format($compra->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="firma-section">
        <div class="firma-box">
            <div class="firma-line">_________________________</div>
            <div>Recibi Conforme</div>
            <div style="font-size: 8px; color: #5f7d9c;">Nombre y Firma</div>
        </div>

        <div class="firma-box">
            <div class="firma-line">_________________________</div>
            <div>Entregue Conforme</div>
            <div style="font-size: 8px; color: #5f7d9c;">Nombre y Firma</div>
        </div>
    </div>



    <div class="footer">
        <p>Este documento es un comprobante de recepción de productos.</p>
        <p>Conservar para fines contables y de inventario.</p>
    </div>

</body>

</html>
