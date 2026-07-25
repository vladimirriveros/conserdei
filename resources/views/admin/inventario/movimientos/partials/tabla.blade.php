<table id="example1" class="table table-striped table-bordered table-hover table-sm">
    <thead>
        <tr>
            <th>Nro</th>
            <th>Tipo</th>
            <th>Lote</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Sucursal</th>
            <th>Usuario</th>  {{-- 👈 NUEVA COLUMNA --}}
            <th>Fecha</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody id="tabla-movimientos-body">
        @forelse ($movimientos as $movimiento)
            <tr>
                <td style="text-align: center">{{ $loop->iteration }}</td>
                <td>
                    @if($movimiento->tipo_movimiento == 'Entrada')
                        <span class="badge badge-success">Entrada</span>
                    @elseif($movimiento->tipo_movimiento == 'Salida')
                        <span class="badge badge-danger">Salida</span>
                    @else
                        {{ $movimiento->tipo_movimiento }}
                    @endif
                </td>
                <td>{{ $movimiento->lote->codigo_lote ?? 'N/A' }}</td>
                <td>{{ $movimiento->producto->nombre ?? 'N/A' }}</td>
                <td style="text-align: center">{{ $movimiento->cantidad }}</td>
                <td>{{ $movimiento->sucursal->nombre ?? 'N/A' }}</td>
                <td>
                    @if($movimiento->nombre_usuario && $movimiento->nombre_usuario != 'N/A')
                        @if($movimiento->tipo_referencia == 'compra')
                            <span class="badge badge-info">
                                <i class="fas fa-shopping-cart"></i> {{ $movimiento->nombre_usuario }}
                            </span>
                        @elseif($movimiento->tipo_referencia == 'salida')
                            <span class="badge badge-warning">
                                <i class="fas fa-truck"></i> {{ $movimiento->nombre_usuario }}
                            </span>
                        @else
                            <span class="badge badge-secondary">{{ $movimiento->nombre_usuario }}</span>
                        @endif
                    @else
                        <span class="badge badge-secondary">N/A</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('Y-m-d H:i') }}</td>
                <td style="max-width: 300px; white-space: normal; word-wrap: break-word;">
                    @if(str_contains($movimiento->observaciones, 'Baja por caducidad'))
                        <span class="badge badge-warning">CADUCIDAD</span>
                        <br>
                        <small>{{ $movimiento->observaciones }}</small>
                    @else
                        {{ $movimiento->observaciones }}
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No hay movimientos que coincidan con los filtros</td>
            </tr>
        @endforelse
    </tbody>
</table>
