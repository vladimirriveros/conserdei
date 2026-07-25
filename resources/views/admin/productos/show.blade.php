@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalle del Producto</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>Detalle del Producto: {{ $producto->nombre }}</b></h3>
                    <div class="card-tools">
                        <a href="{{ route('productos.historial', $producto->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-history"></i> Ver Historial de Precios
                        </a>
                        <a href="{{ route('productos.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-header">
                    <h3 class="card-title"><b>Datos Registrados</b></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- SECCION DE LOS DATOS DEL PRODUCTO --}}
                        <div class="col-md-9">
                            {{-- PRIMER ROW --}}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Categoría</label>
                                        <p>{{ $producto->categoria->nombre }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Código</label>
                                        <p>{{ $producto->codigo }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <p>{{ $producto->nombre }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <p>{{ $producto->marca }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <div>
                                            @if ($producto->estado == 1)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SEGUNDO ROW --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <p>{!! $producto->descripcion !!}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- TERCER ROW --}}
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Precio Compra</label>
                                        <p><span style="color: green"><b>Bs. </b></span>{{ $producto->precio_compra }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Precio Venta</label>
                                        <p><span style="color: green"><b>Bs. </b></span>{{ $producto->precio_venta }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Porcentaje de ganancia</label>
                                        <p>{{ $producto->porcentaje_ganancia }} <span style="color: green"><b>%</b></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Stock Mínimo</label>
                                        <p>{{ $producto->stock_minimo }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Stock Máximo</label>
                                        <p>{{ $producto->stock_maximo }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Unidad de Medida</label>
                                        <p>{{ $producto->unidad_medida }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- CUARTO ROW: CAMPOS DE PLOMERÍA --}}
                            @if ($producto->categoria && $producto->categoria->nombre == 'PLOMERIA')
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="card card-info">
                                            <div class="card-header">
                                                <h3 class="card-title"><b>Especificaciones de Plomería</b></h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Norma Técnica</label>
                                                            <p><strong>{{ $producto->norma ?? 'No especificada' }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Presión de Trabajo</label>
                                                            <p><strong>{{ $producto->presion ?? 'No especificada' }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Diámetro</label>
                                                            <p><strong>{{ $producto->diametro ?? 'No especificado' }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- SECCION DE LA IMAGEN --}}
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Imagen del producto</label>
                                        <br><br>
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" width="100%"
                                            alt="Imagen del producto">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- HISTORIAL DE CAMBIOS DE STOCK --}}
                                        {{-- HISTORIAL DE CAMBIOS DE STOCK --}}
                    @if ($producto->historialStock && $producto->historialStock->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title"><b><i class="fas fa-history"></i> Historial de Cambios de
                                                Stock Mínimo/Máximo</b></h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Usuario</th>
                                                    <th>Stock Mínimo</th>
                                                    <th>Stock Máximo</th>
                                                    <th>Motivo</th>
                                                    <th>Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($producto->historialStock()->latest()->get() as $historial)
                                                    <tr>
                                                        <td>{{ $historial->created_at->format('d/m/Y H:i') }}</td>
                                                        <td>{{ $historial->usuario ? $historial->usuario->name : 'Sistema' }}
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge badge-secondary">{{ $historial->stock_minimo_anterior }}</span>
                                                            <i class="fas fa-arrow-right text-muted"></i>
                                                            <span
                                                                class="badge badge-primary">{{ $historial->stock_minimo_nuevo }}</span>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge badge-secondary">{{ $historial->stock_maximo_anterior ?? '-' }}</span>
                                                            <i class="fas fa-arrow-right text-muted"></i>
                                                            <span
                                                                class="badge badge-primary">{{ $historial->stock_maximo_nuevo ?? '-' }}</span>
                                                        </td>
                                                        <td>{{ $historial->motivo ?? '-' }}</td>
                                                        <td>{{ $historial->observaciones ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- NUEVO: HISTORIAL DE CAMBIOS DE PRECIO DE VENTA --}}
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title"><b><i class="fas fa-chart-line"></i> Historial de Cambios de Precio de Venta</b></h3>
                                </div>
                                <div class="card-body table-responsive">
                                    @if($producto->historialPrecioVenta && $producto->historialPrecioVenta->count() > 0)
                                        <table class="table table-bordered table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Usuario</th>
                                                    <th>Precio Anterior (Bs)</th>
                                                    <th>Precio Nuevo (Bs)</th>
                                                    <th>Tipo de Cambio Aplicado</th>
                                                    <th>Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($producto->historialPrecioVenta->sortByDesc('created_at') as $historial)
                                                    <tr>
                                                        <td>{{ $historial->created_at->format('d/m/Y H:i:s') }}</td>
                                                        <td>{{ $historial->user->name ?? 'N/A' }}</td>
                                                        <td>
                                                            <span class="badge badge-secondary">
                                                                {{ number_format($historial->precio_venta_anterior, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-success">
                                                                {{ number_format($historial->precio_venta_nuevo, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                1 USD = {{ number_format($historial->tipo_cambio_aplicado, 2) }} Bs
                                                            </span>
                                                        </td>
                                                        <td>{{ $historial->motivo }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="alert alert-info text-center mb-0">
                                            <i class="fas fa-info-circle"></i> No hay registros de cambios de precio de venta para este producto.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
