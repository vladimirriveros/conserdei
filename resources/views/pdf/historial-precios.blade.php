<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Precios - {{ $producto->nombre }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .header h3 {
            margin: 5px 0;
            font-size: 16px;
            color: #666;
        }

        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }

        .info-producto {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .info-producto table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-producto td {
            padding: 5px;
            font-size: 11px;
        }

        .info-producto .label {
            font-weight: bold;
            width: 120px;
        }

        .filtros {
            background-color: #e9ecef;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }

        th {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }

        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-muted {
            color: #6c757d;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            margin-top: 10px;
        }

        .resumen {
            margin-top: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 10px;
        }

        @page {
            margin: 1.5cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Historial de Precios</h1>
        <h3>{{ $producto->nombre }} ({{ $producto->codigo }})</h3>
        <p>Fecha de generación: {{ $fechaGeneracion->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-producto">
        <table>
            <tr>
                <td class="label">Código:</td>
                <td>{{ $producto->codigo }}</td>
                <td class="label">Categoría:</td>
                <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Marca:</td>
                <td>{{ $producto->marca ?? 'N/A' }}</td>
                <td class="label">Unidad de Medida:</td>
                <td>{{ $producto->unidad_medida ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Precio Actual:</td>
                <td class="text-success font-weight-bold">Bs. {{ number_format($producto->precio_compra, 2) }}</td>
                <td class="label">Stock Mínimo/Máximo:</td>
                <td>{{ $producto->stock_minimo ?? 0 }} / {{ $producto->stock_maximo ?? 0 }}</td>
            </tr>
        </table>
    </div>

    @if($fechaInicio || $fechaFin)
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        @if($fechaInicio) Fecha Inicio: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} @endif
        @if($fechaFin) Fecha Fin: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }} @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Compra #</th>
                <th>Precio Anterior</th>
                <th>Precio Nuevo</th>
                <th>Diferencia</th>
                <th>% Cambio</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historial as $registro)
            <tr>
                <td class="text-center">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $registro->user->name ?? 'N/A' }}</td>
                <td class="text-center">
                    @if($registro->compra)
                        #{{ $registro->compra_id }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-right text-muted">
                    <del>Bs. {{ number_format($registro->precio_anterior, 2) }}</del>
                </td>
                <td class="text-right text-success font-weight-bold">
                    Bs. {{ number_format($registro->precio_nuevo, 2) }}
                </td>
                <td class="text-right {{ $registro->diferencia >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $registro->diferencia >= 0 ? '+' : '' }}Bs. {{ number_format($registro->diferencia, 2) }}
                </td>
                <td class="text-center {{ $registro->porcentaje_cambio >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $registro->porcentaje_cambio >= 0 ? '+' : '' }}{{ number_format($registro->porcentaje_cambio, 2) }}%
                </td>
                <td>{{ $registro->motivo }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">
                    No hay registros de cambios de precio para este producto.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="resumen">
        <strong>Resumen:</strong>
        Total de registros: {{ $totalRegistros }} |
        Precio mínimo histórico: Bs. {{ number_format($historial->min('precio_nuevo') ?? 0, 2) }} |
        Precio máximo histórico: Bs. {{ number_format($historial->max('precio_nuevo') ?? 0, 2) }} |
        Promedio de precios: Bs. {{ number_format($historial->avg('precio_nuevo') ?? 0, 2) }}
    </div>

    <div class="footer">
        Documento generado por {{ $usuarioGenerador }} - Sistema de Gestión
    </div>
</body>
</html>
