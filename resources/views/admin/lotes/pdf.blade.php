<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotes - Reporte</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            line-height: 1.3;
            margin: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .info-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 15px;
        }
        .info-section table {
            width: 100%;
        }
        .info-section td {
            padding: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #3498db;
            color: white;
            padding: 5px;
            text-align: center;
            font-size: 7px;
            text-transform: uppercase;
        }
        td {
            padding: 3px;
            border-bottom: 1px solid #dee2e6;
        }
        .badge-secondary { background: #6c757d; color: white; padding: 2px 4px; border-radius: 3px; }
        .badge-danger { background: #dc3545; color: white; padding: 2px 4px; border-radius: 3px; }
        .badge-warning { background: #ffc107; color: #212529; padding: 2px 4px; border-radius: 3px; }
        .badge-success { background: #28a745; color: white; padding: 2px 4px; border-radius: 3px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 6px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE LOTES</h1>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td><strong>Período:</strong> {{ $fecha_desde }} - {{ $fecha_hasta }}</td>
                <td><strong>Búsqueda:</strong> {{ $search }}</td>
                <td><strong>Total lotes:</strong> {{ $total_lotes }}</td>
            </tr>
            <tr>
                <td><strong>Generado:</strong> {{ $fecha_generacion }}</td>
                <td><strong>Usuario:</strong> {{ $usuario }}</td>
                <td><strong>Total compras:</strong> ${{ number_format($total_compras, 2) }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="8%">Lote</th>
                <th width="8%">Categoría</th>
                <th width="12%">Producto</th>
                <th width="10%">Proveedor</th>
                <th width="8%">F. Entrada</th>
                <th width="8%">F. Venc.</th>
                <th width="5%">Días</th>
                <th width="5%">C.Ini</th>
                <th width="5%">C.Act</th>
                <th width="6%">Precio</th>
                <th width="8%">Total</th>
                <th width="8%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lotes as $index => $lote)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $lote->codigo_lote }}</td>
                <td>{{ $lote->producto->categoria->nombre ?? 'N/A' }}</td>
                <td>{{ $lote->producto->nombre }}</td>
                <td>{{ $lote->proveedor->nombre ?? 'N/A' }}</td>
                <td class="text-center">{{ $lote->fecha_entrada ? date('d/m/Y', strtotime($lote->fecha_entrada)) : 'N/A' }}</td>
                <td class="text-center">{{ $lote->fecha_vencimiento ? date('d/m/Y', strtotime($lote->fecha_vencimiento)) : 'N/A' }}</td>
                <td class="text-center">{{ $lote->day_to_expired ?? '-' }}</td>
                <td class="text-center">{{ $lote->cantidad_inicial }}</td>
                <td class="text-center">{{ $lote->cantidad_actual }}</td>
                <td class="text-right">${{ number_format($lote->precio_compra, 2) }}</td>
                <td class="text-right">${{ number_format($lote->precio_compra * $lote->cantidad_inicial, 2) }}</td>
                <td class="text-center">
                    @if($lote->cantidad_actual <= 0)
                        <span class="badge-secondary">Terminado</span>
                    @elseif($lote->is_expired)
                        <span class="badge-danger">Vencido</span>
                    @elseif($lote->day_to_expired !== null && $lote->day_to_expired <= 3)
                        <span class="badge-warning">Por caducar</span>
                    @else
                        <span class="badge-success">Vigente</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f4f4f4; font-weight: bold;">
                <td colspan="11" class="text-right">TOTAL COMPRAS:</td>
                <td class="text-right">${{ number_format($total_compras, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Sistema de Gestión de Inventarios - Reporte de Lotes
    </div>
</body>
</html>
