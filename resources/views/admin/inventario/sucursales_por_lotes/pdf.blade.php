<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - {{ $sucursal->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .header h3 {
            color: #7f8c8d;
            margin: 5px 0 0;
            font-weight: normal;
            font-size: 14px;
        }

        .info-sucursal {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 8px;
    margin-bottom: 15px;
}

.info-sucursal table {
    width: 100%;
    border-collapse: collapse;
}

.info-sucursal td {
    padding: 5px 4px;
    font-size: 9px;
    vertical-align: top;
}

.info-sucursal .label {
    font-weight: bold;
    background-color: #e9ecef;
    padding: 5px 6px;
    white-space: nowrap;
    width: auto;
}

.info-sucursal td:not(.label) {
    padding-left: 8px;
    padding-right: 8px;
}

/* Para que las celdas con texto largo no se desborden */
.info-sucursal td {
    word-break: break-word;
}

        .resumen-estadistico {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .stat-box {
            flex: 1;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
        }

        .stat-box .label {
            font-size: 9px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .stat-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-box.total .value {
            color: #17a2b8;
        }

        .stat-box.entradas .value {
            color: #28a745;
        }

        .stat-box.salidas .value {
            color: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }

        th {
            background-color: #3498db;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        td {
            padding: 4px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
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

        .text-left {
            text-align: left;
        }

        .badge-danger {
            color: #dc3545;
            font-weight: bold;
            background-color: #f8d7da;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .badge-success {
            color: #28a745;
            font-weight: bold;
            background-color: #d4edda;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .badge-warning {
            color: #856404;
            font-weight: bold;
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .stock-bajo {
            background-color: #fff3cd;
        }

        .stock-cero {
            background-color: #f8d7da;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
        }

        .fecha-generacion {
            font-size: 8px;
            color: #6c757d;
            text-align: right;
            margin-top: 10px;
        }

        .observaciones {
            margin-top: 15px;
            padding: 8px;
            background-color: #e7f3ff;
            border-left: 4px solid #3498db;
            font-size: 9px;
        }

        .totales-finales {
            margin-top: 15px;
            display: flex;
            justify-content: flex-end;
        }

        .totales-finales table {
            width: 300px;
            border: 1px solid #dee2e6;
        }

        .totales-finales td {
            padding: 6px;
            font-weight: bold;
        }

        .totales-finales .total-final {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>INVENTARIO DE PRODUCTOS</h1>
        <h3>Reporte Detallado por Sucursal</h3>
    </div>

    <div class="info-sucursal">
    <table style="width: 100%; border-collapse: collapse;">
        <!-- Primera fila - 3 columnas -->
        <tr>
            <td class="label" style="width: 12%;">Sucursal:</td>
            <td style="width: 25%;"><strong>{{ $sucursal->nombre }}</strong></td>

            <td class="label" style="width: 10%;">Dirección:</td>
            <td style="width: 28%;">{{ $sucursal->direccion ?? 'No especificada' }}</td>

            <td class="label" style="width: 10%;">Teléfono:</td>
            <td style="width: 15%;">{{ $sucursal->telefono ?? 'No especificado' }}</td>
        </tr>

        <!-- Segunda fila - 3 columnas -->
        <tr>
            <td class="label">Fecha Reporte:</td>
            <td>{{ $fecha_generacion }}</td>

            <td class="label">Generado por:</td>
            <td>{{ auth()->user()->name ?? 'Sistema' }}</td>

            <td class="label">Estado:</td>
            <td>
                @if ($sucursal->activa)
                    <span style="color: #28a745; font-weight: bold;">ACTIVA</span>
                @else
                    <span style="color: #dc3545; font-weight: bold;">INACTIVA</span>
                @endif
            </td>
        </tr>
    </table>
</div>


    {{-- Tabla de Productos --}}
    <table>
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="8%">Código</th>
                <th width="20%">Producto</th>
                <th width="8%">Entradas</th>
                <th width="8%">Salidas</th>
                <th width="8%">Stock Actual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventario as $index => $item)
                @php
                    $stock_actual = $item->cantidad ?? 0;
                    $entradas = $item->total_entradas ?? 0;
                    $salidas = $item->total_salidas ?? 0;
                    $movimiento_neto = $entradas - $salidas;
                    $stock_minimo = $item->stock_minimo ?? 0;

                    $clase_fila = '';
                    if ($stock_actual <= 0) {
                        $clase_fila = 'stock-cero';
                    } elseif ($stock_actual <= $stock_minimo) {
                        $clase_fila = 'stock-bajo';
                    }

                    $ultimo_mov = $item->ultimo_movimiento ?? 'N/A';
                    if ($ultimo_mov != 'N/A') {
                        $ultimo_mov = \Carbon\Carbon::parse($ultimo_mov)->format('d/m/Y');
                    }
                @endphp
                <tr class="{{ $clase_fila }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $item->codigo_producto }}</td>
                    <td class="text-center">{{ $item->producto }}</td>
                    <td class="text-center" style="color: #28a745;">{{ number_format($entradas, 0) }}</td>
                    <td class="text-center" style="color: #dc3545;">{{ number_format($salidas, 0) }}</td>
                    <td class="text-center"><strong>{{ number_format($stock_actual, 0) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No hay productos en el inventario de esta sucursal</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    {{-- Resumen Estadístico Compacto Horizontal --}}
    <div style="margin: 15px 0 10px 0; padding: 0;">
        <table style="width: 100%; border-collapse: collapse; font-size: 8px;">
            <tr>
                <td style="width: 20%; padding: 2px;">
                    <table
                        style="width: 100%; border: 1px solid #dee2e6; border-radius: 3px; background-color: #f8f9fa;">
                        <tr>
                            <td style="padding: 3px; text-align: center;">
                                <div
                                    style="font-weight: bold; color: #2c3e50; font-size: 7px; text-transform: uppercase;">
                                    TOTAL PRODUCTOS</div>
                                <div style="font-size: 11px; font-weight: bold; color: #17a2b8;">{{ $total_productos }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="width: 20%; padding: 2px;">
                    <table
                        style="width: 100%; border: 1px solid #28a745; border-radius: 3px; background-color: #d4edda;">
                        <tr>
                            <td style="padding: 3px; text-align: center;">
                                <div
                                    style="font-weight: bold; color: #155724; font-size: 7px; text-transform: uppercase;">
                                    ENTRADAS</div>
                                <div style="font-size: 11px; font-weight: bold; color: #28a745;">
                                    +{{ number_format($total_entradas ?? 0, 0) }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 20%; padding: 2px;">
                    <table
                        style="width: 100%; border: 1px solid #dc3545; border-radius: 3px; background-color: #f8d7da;">
                        <tr>
                            <td style="padding: 3px; text-align: center;">
                                <div
                                    style="font-weight: bold; color: #721c24; font-size: 7px; text-transform: uppercase;">
                                    SALIDAS</div>
                                <div style="font-size: 11px; font-weight: bold; color: #dc3545;">
                                    -{{ number_format($total_salidas ?? 0, 0) }}</div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="width: 20%; padding: 2px;">
                    <table
                        style="width: 100%; border: 1px solid #dee2e6; border-radius: 3px; background-color: #f8f9fa;">
                        <tr>
                            <td style="padding: 3px; text-align: center;">
                                <div
                                    style="font-weight: bold; color: #2c3e50; font-size: 7px; text-transform: uppercase;">
                                    TOTAL UNIDADES</div>
                                <div style="font-size: 11px; font-weight: bold; color: #17a2b8;">
                                    {{ number_format($total_items, 0) }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                {{-- <td style="width: 20%; padding: 2px;">
                    <table
                        style="width: 100%; border: 1px solid #ffc107; border-radius: 3px; background-color: #fff3cd;">
                        <tr>
                            <td style="padding: 3px; text-align: center;">
                                <div
                                    style="font-weight: bold; color: #856404; font-size: 7px; text-transform: uppercase;">
                                    STOCK BAJO</div>
                                <div style="font-size: 11px; font-weight: bold; color: #856404;">
                                    {{ $productos_stock_bajo }}</div>
                            </td>
                        </tr>
                    </table>
                </td> --}}
            </tr>
        </table>
    </div>

    <div class="fecha-generacion">
        Documento generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>

    {{-- Línea divisoria opcional --}}
    <div style="border-top: 1px dashed #dee2e6; margin: 5px 0 10px 0;"></div>

    <div class="footer">
        <p>Este reporte muestra el movimiento de inventario por producto (Entradas - Salidas = Stock Actual)</p>
        <p>Sistema de Gestión de Inventarios - {{ date('Y') }}</p>
    </div>
</body>

</html>
