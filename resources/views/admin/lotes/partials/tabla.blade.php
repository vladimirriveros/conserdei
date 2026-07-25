<table id="example1" class="table table-striped table-bordered table-hover table-sm">
    <thead>
        <tr>
            <th>Nro</th>
            <th>Codigo de lote</th>
            <th>Categoria</th>
            <th>Marca</th>
            <th>Producto</th>
            <th>Proveedor</th>
            <th>Sucursal Destino</th>
            <th>Fecha de entrada</th>
            <th>Fecha de vencimiento</th>
            <th>Días restantes</th>
            <th>Cantidad inicial</th>
            <th>Cantidad actual</th>
            <th>precio unitario de compra</th>
            {{-- <th>Precio Venta</th> --}}
            <th>Total Compra</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody id="tabla-lotes-body">
        @forelse ($lotes as $lote)
            <tr class="{{ $lote->cantidad_actual <= 0 ? '' : ($lote->is_expired ? 'table-danger' : '') }}">
                <td style="text-align: center">{{ $loop->iteration }}</td>
                <td>{{ $lote->codigo_lote }}</td>
                <td>{{ $lote->producto->categoria->nombre ?? 'N/A' }}</td>
                <td>{{ $lote->producto->marca ?? 'N/A' }}</td>
                <td>{{ $lote->producto->nombre }}</td>
                <td>{{ $lote->proveedor->nombre ?? 'N/A' }}</td>
                <td>
                    @foreach ($lote->inventarioSucuralLotes as $inventario)
                        {{ $inventario->sucursal->nombre }} <br>
                    @endforeach
                </td>
                <td>{{ $lote->fecha_entrada }}</td>
                <td>{{ $lote->fecha_vencimiento }}</td>
                <td>
                    @if ($lote->cantidad_actual <= 0)
                        &nbsp;
                    @else
                        {{ $lote->day_to_expired }} días
                    @endif
                </td>
                <td>{{ $lote->cantidad_inicial }}</td>
                <td>{{ $lote->cantidad_actual }}</td>
                <td>{{ $lote->precio_compra }}</td>

                {{-- <td>
                    @if ($lote->precio_venta)
                        <strong class="text-success">Bs {{ number_format($lote->precio_venta, 2) }}</strong>
                        <br>
                        <small class="text-muted">
                            (Compra: Bs {{ number_format($lote->precio_compra, 2) }}
                            +{{ $lote->producto->porcentaje_ganancia ?? 30 }}%)
                        </small>
                        @if ($lote->precio_venta != $lote->precio_compra * (1 + ($lote->producto->porcentaje_ganancia ?? 30) / 100))
                            <br>
                            <small class="text-info">
                                <i class="fas fa-exchange-alt"></i> Ajustado por TC
                            </small>
                        @endif
                    @else
                        <span class="badge badge-warning">No calculado</span>
                    @endif
                </td> --}}
                <td>{{ $lote->precio_compra * $lote->cantidad_inicial }}</td>
                <td>
                    @if ($lote->cantidad_actual <= 0)
                        <span class="badge badge-secondary">Lote terminado</span>
                    @elseif ($lote->is_expired)
                        <span class="badge badge-danger">Vencido</span>
                    @elseif ($lote->day_to_expired <= 7 && $lote->day_to_expired !== null)
                        <span class="badge badge-warning">Por caducar</span>
                    @elseif (!$lote->fecha_vencimiento)
                        <span class="badge badge-success">Vigente</span>
                    @else
                        <span class="badge badge-success">Vigente</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="14" class="text-center">No hay lotes que coincidan con los filtros</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background-color: #f4f4f4; font-weight: bold;">
            <td colspan="13" style="text-align: right;"><b>TOTAL COMPRAS:</b></td>
            <td>
                @php
                    $totalTabla = $lotes->sum(function ($lote) {
                        return $lote->precio_compra * $lote->cantidad_inicial;
                    });
                @endphp
                <b>$ {{ number_format($totalTabla, 2) }}</b>
            </td>
            <td></td>
        </tr>
    </tfoot>
</table>
