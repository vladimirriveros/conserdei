<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Bajo - {{ $sucursal->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #dc3545;
            margin: 0;
            font-size: 24px;
        }
        .header h3 {
            color: #7f8c8d;
            margin: 5px 0 0;
            font-weight: normal;
        }
        .alert-stock {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .info-sucursal {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .info-sucursal table {
            width: 100%;
        }
        .info-sucursal td {
            padding: 3px;
        }
        .info-sucursal .label {
            font-weight: bold;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #dc3545;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
        }
        .estadisticas {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        .estadistica-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            flex: 1;
            text-align: center;
        }
        .estadistica-box .numero {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
        }
        .estadistica-box .label {
            font-size: 11px;
            color: #7f8c8d;
        }
        .observaciones {
            margin-top: 15px;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 3px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE STOCK BAJO</h1>
        <h3>Productos que requieren reposición inmediata</h3>
    </div>

    {{-- <div class="alert-stock">
        <strong>ALERTA DE STOCK CRÍTICO</strong><br>
        Los siguientes productos tienen un nivel de inventario por debajo del mínimo establecido
    </div> --}}

    <div class="info-sucursal">
        <table>
            <tr>
                <td class="label">Sucursal:</td>
                <td><strong>{{ $sucursal->nombre }}</strong></td>
                <td class="label">Dirección:</td>
                <td>{{ $sucursal->direccion ?? 'No especificada' }}</td>
            </tr>
            <tr>
                <td class="label">Teléfono:</td>
                <td>{{ $sucursal->telefono ?? 'No especificado' }}</td>
                <td class="label">Fecha Reporte:</td>
                <td>{{ $fecha_generacion }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="15%">Código</th>
                <th width="35%">Producto</th>
                <th width="15%">Stock Actual</th>
                <th width="15%">Stock Mínimo</th>
                {{-- <th width="15%">Faltante</th> --}}
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->codigo_producto }}</td>
                <td>{{ $item->producto }}</td>
                <td class="text-center text-danger"><strong>{{ number_format($item->cantidad, 0) }}</strong></td>
                <td class="text-center">{{ number_format($item->stock_minimo, 0) }}</td>
                {{-- <td class="text-center text-danger">
                    {{ number_format(max(0, $item->stock_minimo - $item->cantidad), 0) }}
                </td> --}}
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No hay productos con stock bajo en esta sucursal</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- <div class="estadisticas">
        <div class="estadistica-box">
            <div class="numero">{{ $total_productos }}</div>
            <div class="label">Productos con Stock Bajo</div>
        </div>
        <div class="estadistica-box">
            <div class="numero">{{ number_format($total_unidades_actuales, 0) }}</div>
            <div class="label">Unidades Actuales</div>
        </div>
        <div class="estadistica-box">
            <div class="numero">{{ number_format($total_unidades_faltantes, 0) }}</div>
            <div class="label">Unidades Faltantes</div>
        </div>
        <div class="estadistica-box">
            <div class="numero">${{ number_format($valor_reposicion, 2) }}</div>
            <div class="label">Valor Reposición</div>
        </div>
    </div> --}}

    {{-- @if($observaciones)
    <div class="observaciones">
        <strong>📋 Observaciones:</strong><br>
        {{ $observaciones }}
    </div>
    @endif --}}

    <div class="footer">
        <p>Documento generado automáticamente por el sistema de gestión de inventarios | {{ date('d/m/Y H:i:s') }}</p>
        <p>Este reporte muestra los productos que requieren reposición inmediata</p>
    </div>
</body>
</html>
