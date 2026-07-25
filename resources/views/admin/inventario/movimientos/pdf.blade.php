<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex - Movimientos de Inventario</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
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
            font-size: 22px;
            text-transform: uppercase;
        }

        .header h3 {
            color: #7f8c8d;
            margin: 5px 0 0;
            font-weight: normal;
            font-size: 12px;
        }

        .info-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 15px;
        }

        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-section td {
            padding: 4px 6px;
            font-size: 9px;
        }

        .info-section .label {
            font-weight: bold;
            background-color: #e9ecef;
            width: 120px;
        }

        .resumen-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
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

        .stat-box.entradas {
            background-color: #d4edda;
            border-color: #28a745;
        }

        .stat-box.salidas {
            background-color: #f8d7da;
            border-color: #dc3545;
        }

        .stat-box.total {
            background-color: #cce5ff;
            border-color: #17a2b8;
        }

        .stat-box .label {
            font-size: 8px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .stat-box .value {
            font-size: 14px;
            font-weight: bold;
        }

        .stat-box.entradas .value {
            color: #28a745;
        }

        .stat-box.salidas .value {
            color: #dc3545;
        }

        .stat-box.total .value {
            color: #17a2b8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8px;
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

        .badge-entrada {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7px;
        }

        .badge-salida {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7px;
        }

        .badge-caducidad {
            background-color: #ffc107;
            color: #212529;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7px;
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

        .footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 7px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
        }

        .fecha-generacion {
            font-size: 7px;
            color: #6c757d;
            text-align: right;
            margin-top: 10px;
        }

        .observacion-cell {
            max-width: 200px;
            word-wrap: break-word;
            white-space: normal;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>KARDEX - MOVIMIENTOS DE INVENTARIO</h1>
        <h3>Reporte Detallado de Entradas y Salidas</h3>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td class="label">Período:</td>
                <td><strong>{{ $fecha_desde }} - {{ $fecha_hasta }}</strong></td>
                {{-- <td class="label">Búsqueda:</td>
                <td><strong>{{ $search }}</strong></td> --}}
                <td class="label">Generado por:</td>
                <td>{{ $usuario }}</td>
            </tr>
            <tr>
                <td class="label">Fecha:</td>
                <td colspan="5">{{ $fecha_generacion }}</td>
            </tr>
        </table>
    </div>



    <table>
        <thead>
            <tr>
                <th width="4%">#</th>
                <th width="10%">Tipo</th>
                <th width="10%">Lote</th>
                <th width="18%">Producto</th>
                <th width="7%">Cantidad</th>
                <th width="10%">Sucursal</th>
                <th>Usuario</th>  {{-- 👈 AGREGAR --}}
                <th width="10%">Fecha</th>
                <th width="31%">Observación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $index => $movimiento)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        @if ($movimiento->tipo_movimiento == 'Entrada')
                            <span class="badge-entrada">ENTRADA</span>
                        @elseif($movimiento->tipo_movimiento == 'Salida')
                            <span class="badge-salida">SALIDA</span>
                        @else
                            {{ $movimiento->tipo_movimiento }}
                        @endif
                    </td>
                    <td class="text-center">{{ $movimiento->lote->codigo_lote ?? 'N/A' }}</td>
                    <td class="text-left">{{ $movimiento->producto->nombre ?? 'N/A' }}</td>
                    <td class="text-center"><strong>{{ number_format($movimiento->cantidad, 0) }}</strong></td>
                    <td class="text-center">{{ $movimiento->sucursal->nombre ?? 'N/A' }}</td>
                    <td>{{ $movimiento->nombre_usuario ?? 'N/A' }}</td>  {{-- 👈 AGREGAR --}}
                    <td class="text-center">{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y H:i') }}</td>
                    <td class="observacion-cell">
                        @if (str_contains($movimiento->observaciones, 'caducidad') || str_contains($movimiento->observaciones, 'CADUCIDAD'))
                            <span class="badge-caducidad">CADUCIDAD</span>
                            <br>
                        @endif
                        {{ $movimiento->observaciones }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay movimientos en el período seleccionado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Resumen Estadístico Horizontal Compacto -->
    <div
        style="margin: 10px 0; padding: 5px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 33.33%; padding: 3px; text-align: center; border-right: 1px solid #dee2e6;">
                    <div style="font-size: 7px; color: #28a745; text-transform: uppercase; font-weight: bold;">ENTRADAS
                    </div>
                    <div style="font-size: 10px; font-weight: bold; color: #28a745;">
                        +{{ number_format($total_entradas, 0) }}</div>
                    <div style="font-size: 6px; color: #6c757d;">unidades</div>
                </td>
                <td style="width: 33.33%; padding: 3px; text-align: center; border-right: 1px solid #dee2e6;">
                    <div style="font-size: 7px; color: #dc3545; text-transform: uppercase; font-weight: bold;">SALIDAS
                    </div>
                    <div style="font-size: 10px; font-weight: bold; color: #dc3545;">
                        -{{ number_format($total_salidas, 0) }}</div>
                    <div style="font-size: 6px; color: #6c757d;">unidades</div>
                </td>
                <td style="width: 33.33%; padding: 3px; text-align: center;">
                    <div style="font-size: 7px; color: #17a2b8; text-transform: uppercase; font-weight: bold;">
                        MOVIMIENTOS</div>
                    <div style="font-size: 10px; font-weight: bold; color: #17a2b8;">{{ $total_movimientos }}</div>
                    <div style="font-size: 6px; color: #6c757d;">registros</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="fecha-generacion">
        Documento generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <div class="footer">
        <p>Sistema de Gestión de Inventarios - Kardex de Movimientos</p>
    </div>
</body>

</html>
