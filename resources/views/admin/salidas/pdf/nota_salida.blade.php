<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Salida #{{ $salida->id }}</title>
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
        .badge-motivo {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }
        .badge-venta { background-color: #28a745; }
        .badge-caducidad { background-color: #dc3545; }
        .badge-ajuste { background-color: #ffc107; color: #1e3a5f; }
        .badge-otro { background-color: #6c757d; }

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
        .observaciones {
            margin-top: 10px;
            padding: 6px 10px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 3px;
            font-size: 9px;
        }
        .usuario-info {
            margin-top: 8px;
            font-size: 8px;
            color: #5f7d9c;
        }
        h4 {
            margin-bottom: 8px;
            color: #1e88e5;
            font-size: 11px;
        }
        .badge-estado-entregado {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-estado-pendiente {
            background-color: #ffc107;
            color: #1e3a5f;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NOTA DE SALIDA</h1>
        <h3>Comprobante de Egreso de Productos</h3>
    </div>

    <div class="empresa-info">
        <strong>CONSERDEI</strong><br>
        Sistema de Gestión de Inventarios<br>
        {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="info-section">
         <table>
             <tr>
                <td class="label">N° de Salida:</td>
                <td class="value"><strong>#{{ $salida->id }}</strong></td>
                <td class="label">Fecha:</td>
                <td class="value">{{ \Carbon\Carbon::parse($salida->fecha)->format('d/m/Y') }}</td>
             </tr>
             <tr>
                <td class="label">Sucursal:</td>
                <td class="value">{{ $salida->sucursal->nombre ?? 'N/A' }}</td>
                <td class="label">Estado:</td>
                <td class="value">
                    @if($salida->estado == 'Entregado')
                        <span class="badge-estado-entregado">ENTREGADO</span>
                    @else
                        <span class="badge-estado-pendiente">{{ $salida->estado }}</span>
                    @endif
                </td>
             </tr>
             <tr>
                <td class="label">Motivo:</td>
                <td class="value" colspan="3">
                    @php
                        $motivoClass = 'badge-otro';
                        if($salida->motivo == 'Venta') $motivoClass = 'badge-venta';
                        elseif($salida->motivo == 'Caducidad') $motivoClass = 'badge-caducidad';
                        elseif($salida->motivo == 'Ajuste') $motivoClass = 'badge-ajuste';
                    @endphp
                    <span class="badge-motivo {{ $motivoClass }}">{{ $salida->motivo }}</span>
                </td>
             </tr>
             <tr>
                <td class="label">Usuario:</td>
                <td class="value" colspan="3">{{ $salida->usuario->name ?? 'N/A' }}</td>
             </tr>
         </table>

        @if($salida->observaciones)
        <div class="observaciones">
            <strong>Observaciones:</strong><br>
            {{ $salida->observaciones }}
        </div>
        @endif
    </div>

    <h4>📤 Detalle de Productos Egresados</h4>

    <table class="productos-table">
        <thead>
             <tr>
                <th width="5%">#</th>
                <th width="15%">Código</th>
                <th width="20%">Producto</th>
                <th width="12%">Lote</th>
                <th width="10%">F. Vencimiento</th>
                <th width="10%">Cantidad</th>
                <th width="12%">Precio Unit.</th>
                <th width="13%">Subtotal</th>
             </tr>
        </thead>
        <tbody>
            @foreach($detalles as $index => $detalle)
             <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $detalle->producto->codigo ?? '' }}</td>
                <td class="text-left">{{ $detalle->producto->nombre ?? '' }}</td>
                <td>{{ $detalle->lote->codigo_lote ?? '' }}</td>
                <td>{{ $detalle->lote->fecha_vencimiento ? date('d/m/Y', strtotime($detalle->lote->fecha_vencimiento)) : 'N/A' }}</td>
                <td>{{ number_format($detalle->cantidad, 0) }}</td>
                <td class="text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
             </tr>
            @endforeach
        </tbody>
        <tfoot>
             <tr>
                <td colspan="7" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>${{ number_format($salida->total, 2) }}</strong></td>
             </tr>
        </tfoot>
     </table>

    <div class="resumen-box">
         <table>
             <tr>
                <td class="text-right">Subtotal:</td>
                <td class="text-right">${{ number_format($salida->total, 2) }}</td>
             </tr>
             <tr>
                <td class="text-right total-final">TOTAL EGRESADO:</td>
                <td class="text-right total-final">${{ number_format($salida->total, 2) }}</td>
             </tr>
         </table>
    </div>

    <div class="firma-section">
        <div class="firma-box">
            <div class="firma-line">_________________________</div>
            <div>Entregué Conforme</div>
            <div style="font-size: 8px; color: #5f7d9c;">Nombre y Firma</div>
        </div>
        <div class="firma-box">
            <div class="firma-line">_________________________</div>
            <div>Recibí Conforme</div>
            <div style="font-size: 8px; color: #5f7d9c;">Nombre y Firma</div>
        </div>
    </div>



    <div class="footer">
        <p>Este documento es un comprobante de egreso de productos.</p>
        <p>Conservar para fines contables y de control de inventario.</p>
    </div>
</body>
</html>
