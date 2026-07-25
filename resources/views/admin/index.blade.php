@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>CONSERDEI</h1>
@stop

@section('content')

    {{-- TARJETAS DE RESUMEN FINANCIERO --}}
    @role('admin')
        <div class="row">

            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>${{ number_format($total_compras_lotes, 2) }}</h3>
                        <p>Total en Compras (Capital)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <!-- Bootstrap 4 usa data-toggle y data-target -->
                    <button type="button" class="small-box-footer" data-toggle="modal" data-target="#modalCompras"
                        style="border: none; background: transparent; width: 100%;">
                        Ver Detalle <i class="fas fa-arrow-circle-right"></i>
                    </button>
                </div>
            </div>

            <!-- Modal para Bootstrap 4 -->
            <div class="modal fade" id="modalCompras" tabindex="-1" role="dialog" aria-labelledby="modalComprasLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalComprasLabel">Detalle de Compras</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <p><b>Total en Compras (Pedidos):</b> ${{ number_format($total_compras_monto, 2) }}</p>
                            <p><b>Total en Lotes:</b> ${{ number_format($total_compras_lotes, 2) }}</p>
                            <p><b>Total en Salidas:</b> ${{ number_format($total_salidas_monto, 2) }}</p>
                            <hr>
                            <p><b>Balance (Compras - Salidas):</b>
                                @php
                                    $balance = $total_compras_lotes - $total_salidas_monto;
                                @endphp
                                ${{ number_format($balance, 2) }}
                                @if ($balance > 0)
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> Positivo</span>
                                @elseif($balance < 0)
                                    <span class="text-danger"><i class="fas fa-arrow-down"></i> Negativo</span>
                                @else
                                    <span><i class="fas fa-equals"></i> Equilibrado</span>
                                @endif
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="{{ route('compras.index') }}" class="btn btn-success">Ir a Compras</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>${{ number_format($total_salidas_monto, 2) }}</h3>
                        <p>Total en Salidas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <a href="{{ route('salidas.index') }}" class="small-box-footer">
                        Ver salidas <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        @php
                            $balance = $total_compras_lotes - $total_salidas_monto;
                        @endphp
                        <h3>${{ number_format($balance, 2) }}</h3>
                        <p>Balance (Compras - Salidas)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        @if ($balance > 0)
                            <i class="fas fa-arrow-up text-success"></i> Positivo
                        @elseif($balance < 0)
                            <i class="fas fa-arrow-down text-danger"></i> Negativo
                        @else
                            <i class="fas fa-equals"></i> Equilibrado
                        @endif
                    </a>
                </div>
            </div>
        </div>
    @endrole

    {{-- ============================================ --}}
    {{-- TARJETAS DE PRODUCTOS MÁS Y MENOS VENDIDOS --}}
    {{-- ============================================ --}}
    @role('admin')
        <div class="row">
            {{-- TARJETA 1: PRODUCTOS MÁS VENDIDOS EN TIENDA --}}
            {{-- <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><b><i class="fas fa-chart-line"></i> Top 5 Productos más vendidos en Tienda</b>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-success">Total: ${{ number_format($total_ventas_tienda, 2) }}</span>
                            @if ($productos_mas_salidas->count() >= 5)
                                <span class="badge badge-info ml-1">Top 5</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productos_mas_salidas as $index => $producto)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $producto->producto }}</strong>
                                            </td>
                                            <td><code>{{ $producto->codigo }}</code></td>
                                            <td class="text-right">
                                                <span class="badge badge-success">{{ number_format($producto->total_vendido) }}
                                                    uds</span>
                                            </td>
                                            <td class="text-right">${{ number_format($producto->total_monto, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No hay ventas registradas en tienda
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('salidas.index', ['motivo' => 'tienda']) }}" class="small-box-footer">
                            Ver todas las salidas a tienda <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div> --}}

            {{-- TARJETA 2: PRODUCTOS MENOS VENDIDOS EN TIENDA (Basado en MONTO) --}}
            {{-- <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><b><i class="fas fa-chart-bar"></i> Productos con menor Salida</b>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-warning">Menor monto</span>
                            @if ($productos_con_cero_ventas > 0)
                                <span class="badge badge-danger ml-1">{{ $productos_con_cero_ventas }} sin ventas</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th class="text-right">Monto Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productos_menos_salidas as $index => $producto)
                                        <tr class="{{ $producto->total_monto == 0 ? 'table-danger' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $producto->producto }}</strong>
                                            </td>
                                            <td><code>{{ $producto->codigo }}</code></td>
                                            <td class="text-right">
                                                @if ($producto->total_monto == 0)
                                                    <span class="badge badge-danger">$0.00</span>
                                                @else
                                                    <span
                                                        class="badge badge-warning">${{ number_format($producto->total_monto, 2) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No hay productos registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    @endrole

    {{-- Modal para productos con cero ventas (opcional) --}}
    <div class="modal fade" id="modalCeroVentas" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Productos sin ninguna venta en tienda</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php
                        $productos_sin_ventas = DB::table('productos')
                            ->leftJoin('detalle_salidas', 'productos.id', '=', 'detalle_salidas.producto_id')
                            ->leftJoin('salidas', function ($join) {
                                $join
                                    ->on('detalle_salidas.salida_id', '=', 'salidas.id')
                                    ->where('salidas.motivo', 'tienda')
                                    ->where('salidas.estado', 'Entregado');
                            })
                            ->where('productos.estado', true)
                            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
                            ->havingRaw('COALESCE(SUM(detalle_salidas.cantidad), 0) = 0')
                            ->select('productos.id', 'productos.nombre', 'productos.codigo')
                            ->get();
                    @endphp

                    @if ($productos_sin_ventas->isEmpty())
                        <p class="text-center text-success">
                            <i class="fas fa-check-circle"></i> ¡Todos los productos han tenido al menos una venta!
                        </p>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            Estos {{ $productos_sin_ventas->count() }} productos no han registrado ninguna venta en tienda.
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Código</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productos_sin_ventas as $index => $producto)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $producto->nombre }}</td>
                                        <td><code>{{ $producto->codigo }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <a href="{{ route('productos.index') }}" class="btn btn-primary">Gestionar Productos</a>
                </div>
            </div>
        </div>
    </div>

    {{-- TARJETAS DE ESTADOS DE COMPRAS Y SALIDAS --}}
    @role('admin')
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><b>📊 Estado de Compras</b></h3>
                        <div class="card-tools">
                            <span class="badge badge-success">{{ $compras_count }} total</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="knob-label">
                                    <h4>{{ $compras_pendientes }}</h4>
                                    <small class="text-muted">Pendientes</small>
                                    <div class="progress progress-xs mt-2">
                                        @php
                                            $porcentajePendientes =
                                                $compras_count > 0
                                                    ? round(($compras_pendientes / $compras_count) * 100)
                                                    : 0;
                                        @endphp
                                        <div class="progress-bar bg-warning" style="width: {{ $porcentajePendientes }}%">
                                        </div>
                                    </div>
                                    <span class="badge badge-warning">{{ $porcentajePendientes }}%</span>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="knob-label">
                                    <h4>{{ $compras_enviadas }}</h4>
                                    <small class="text-muted">Enviadas</small>
                                    <div class="progress progress-xs mt-2">
                                        @php
                                            $porcentajeEnviadas =
                                                $compras_count > 0
                                                    ? round(($compras_enviadas / $compras_count) * 100)
                                                    : 0;
                                        @endphp
                                        <div class="progress-bar bg-info" style="width: {{ $porcentajeEnviadas }}%"></div>
                                    </div>
                                    <span class="badge badge-info">{{ $porcentajeEnviadas }}%</span>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="knob-label">
                                    <h4>{{ $compras_recibidas }}</h4>
                                    <small class="text-muted">Recibidas</small>
                                    <div class="progress progress-xs mt-2">
                                        @php
                                            $porcentajeRecibidas =
                                                $compras_count > 0
                                                    ? round(($compras_recibidas / $compras_count) * 100)
                                                    : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $porcentajeRecibidas }}%">
                                        </div>
                                    </div>
                                    <span class="badge badge-success">{{ $porcentajeRecibidas }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('compras.index') }}" class="small-box-footer">
                            Ver todas las compras <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><b>📊 Estado de Salidas</b></h3>
                        <div class="card-tools">
                            <span class="badge badge-danger">{{ $salidas_count }} total</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="knob-label">
                                    <h4>{{ $salidas_pendientes }}</h4>
                                    <small class="text-muted">Pendientes</small>
                                    <div class="progress progress-xs mt-2">
                                        @php
                                            $porcentajePendientes =
                                                $salidas_count > 0
                                                    ? round(($salidas_pendientes / $salidas_count) * 100)
                                                    : 0;
                                        @endphp
                                        <div class="progress-bar bg-warning" style="width: {{ $porcentajePendientes }}%">
                                        </div>
                                    </div>
                                    <span class="badge badge-warning">{{ $porcentajePendientes }}%</span>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="knob-label">
                                    <h4>{{ $salidas_proceso }}</h4>
                                    <small class="text-muted">En Proceso</small>
                                    <div class="progress progress-xs mt-2">
                                        @php
                                            $porcentajeProceso =
                                                $salidas_count > 0
                                                    ? round(($salidas_proceso / $salidas_count) * 100)
                                                    : 0;
                                        @endphp
                                        <div class="progress-bar bg-info" style="width: {{ $porcentajeProceso }}%"></div>
                                    </div>
                                    <span class="badge badge-info">{{ $porcentajeProceso }}%</span>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="knob-label">
                                    <h4>{{ $salidas_entregadas }}</h4>
                                    <small class="text-muted">Entregadas</small>
                                    <div class="progress progress-xs mt-2">
                                        @php
                                            $porcentajeEntregadas =
                                                $salidas_count > 0
                                                    ? round(($salidas_entregadas / $salidas_count) * 100)
                                                    : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $porcentajeEntregadas }}%">
                                        </div>
                                    </div>
                                    <span class="badge badge-success">{{ $porcentajeEntregadas }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('salidas.index') }}" class="small-box-footer">
                            Ver todas las salidas <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endrole

    <hr>
    <p>Resumen General</p>

    <div class="row">
        {{-- ICONO PRODUCTOS --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('productos.index') }}">
                    <span class="info-box-icon bg-info">
                        <img src="{{ url('/img/construccion.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Productos</b></span>
                    <span class="info-box-number">{{ $total_productos }} productos</span>
                </div>
            </div>
        </div>

        {{-- ICONO CATEGORIAS --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('categorias.index') }}">
                    <span class="info-box-icon bg-info">
                        <img src="{{ url('/img/carpetas.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Categorias</b></span>
                    <span class="info-box-number">{{ $total_categorias }} categorias</span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>

        {{-- ICONO PROVEEDORES --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('proveedores.index') }}">
                    <span class="info-box-icon bg-info">
                        <img src="{{ url('/img/camion.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Proveedores</b></span>
                    <span class="info-box-number">{{ $total_proveedores }} proveedores</span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>

        {{-- ICONO SUCURSALES --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('sucursales.index') }}">
                    <span class="info-box-icon bg-info">
                        <img src="{{ url('/img/propiedades.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Sucursales</b></span>
                    <span class="info-box-number">{{ $total_sucursales }} sucursales</span>
                </div>
            </div>
        </div>

        {{-- ICONO COMPRAS-PEDIDOS --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('compras.index') }}">
                    <span class="info-box-icon bg-info">
                        <img src="{{ url('/img/pedido.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Pedidos</b></span>
                    <span class="info-box-number">{{ $compras_count }} pedidos</span>
                    <small class="text-muted">Total: ${{ number_format($total_compras_monto, 2) }}</small>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>

        {{-- ICONO SALIDAS --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('salidas.index') }}">
                    <span class="info-box-icon bg-danger">
                        <img src="{{ url('/img/camion.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Salidas</b></span>
                    <span class="info-box-number">{{ $salidas_count }} salidas</span>
                    <small class="text-muted">Total: ${{ number_format($total_salidas_monto, 2) }}</small>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>

        {{-- ICONO ALERTAS --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('lotes.index') }}">
                    <span class="info-box-icon bg-info">
                        <img src="{{ url('/img/notificaciones.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Lotes vencidos</b></span>
                    <span class="info-box-number">{{ $total_lotes_vencidos }} lotes</span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>

        {{-- ICONO INVENTARIO TOTAL --}}
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <a href="{{ route('sucursal_por_lotes.index') }}">
                    <span class="info-box-icon bg-success">
                        <img src="{{ url('/img/inventario.gif') }}" alt="">
                    </span>
                </a>

                <div class="info-box-content">
                    <span class="info-box-text"><b> Inventario</b></span>
                    <span class="info-box-number">{{ $total_productos_inventario }} productos</span>
                    <small class="text-muted">En stock</small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Estilos para las tarjetas de resumen */
        .small-box {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .small-box .inner h3 {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }

        .small-box .inner p {
            font-size: 14px;
            margin: 5px 0 0 0;
        }

        .small-box .icon {
            font-size: 50px;
            opacity: 0.3;
        }

        /* Estilos para las tarjetas de estado */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .knob-label h4 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .badge {
            font-size: 11px;
            padding: 5px 8px;
            border-radius: 4px;
        }

        /* Mantener estilos existentes */
        .info-box {
            min-height: 100px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .info-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .info-box-icon img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
    </style>
@stop

@section('js')
    <script>
        console.log("Dashboard cargado correctamente");
    </script>
@stop
