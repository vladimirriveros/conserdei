@extends('adminlte::page')

@section('title', 'Stock Bajo - ' . $sucursal->nombre)

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sucursal_por_lotes.index') }}">Inventario por sucursal</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('mostrar_inventario_por_sucursal.show', $sucursal->id) }}">
                    {{ $sucursal->nombre }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Stock Bajo</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <div class="row w-100 align-items-center">
                        <div class="col-md-8">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>
                                <b>Productos con Stock Bajo</b>
                            </h3>
                            <small class="text-muted ml-2">
                                <i class="fas fa-store"></i> Sucursal: {{ $sucursal->nombre }}
                                @if ($productos_stock_bajo->count() > 0)
                                    <span class="badge badge-danger ml-2">
                                        {{ $productos_stock_bajo->count() }} productos críticos
                                    </span>
                                @endif
                            </small>
                        </div>
                        {{-- SOLO MOSTRAR BOTONES SI HAY PRODUCTOS CON STOCK BAJO --}}
                        @if ($productos_stock_bajo->count() > 0)
                            <div class="col-md-4 text-right">
                                <div class="btn-group" role="group">
                                    {{-- Botón Volver --}}
                                    <a href="{{ route('mostrar_inventario_por_sucursal.show', $sucursal->id) }}"
                                        class="btn btn-info btn-sm" title="Volver al inventario completo">
                                        <i class="fas fa-arrow-left"></i>
                                        <span class="d-none d-md-inline">Volver</span>
                                    </a>
                                    {{-- Botón PDF --}}
                                    @can('inventario.sucursal.pdf')
                                        <a href="{{ route('inventario.stock_bajo.pdf', $sucursal->id) }}"
                                            class="btn btn-danger btn-sm" target="_blank"
                                            title="Generar PDF de productos con stock bajo">
                                            <i class="fas fa-file-pdf"></i>
                                            <span class="d-none d-md-inline">PDF</span>
                                        </a>
                                    @endcan
                                    @php
                                        //Convierte en texto separado por comas el producto
                                        $ids_productos = $productos_stock_bajo->pluck('producto_id')->implode(',');
                                    @endphp
                                    <a href="{{ route('compras.create', [
                                        'obs' => 'Reposición urgente - ' . $sucursal->nombre,
                                        'productos' => $ids_productos,
                                    ]) }}"
                                        class="btn btn-success btn-sm" title="Reponer stock automáticamente">
                                        <i class="fas fa-cart-plus"></i>
                                        <span class="d-none d-md-inline">Reponer</span>
                                    </a>


                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if ($productos_stock_bajo->count())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> ¡Alerta de Stock Crítico!</h5>
                            Se encontraron <strong>{{ $productos_stock_bajo->count() }} productos</strong> con stock por
                            debajo del nivel mínimo.
                            Se recomienda realizar una reposición urgente.
                        </div>

                        <div class="table-responsive">
                            <table id="tabla-stock-bajo" class="table table-bordered table-hover">
                                <thead class="bg-danger text-white">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="10%">Código</th>
                                        <th width="25%">Producto</th>
                                        <th width="10%">Cantidad Actual</th>
                                        <th width="10%">Stock Mínimo</th>
                                        <th width="10%">ROP</th>
                                        <th width="10%">Faltante</th>
                                        <th width="10%">Tipo Alerta</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productos_stock_bajo as $item)
                                        <tr class="table-danger">
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->codigo_producto }}</td>
                                            <td>
                                                <strong>{{ $item->producto }}</strong>
                                                <br>

                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $item->cantidad <= $item->stock_minimo ? 'badge-danger' : 'badge-warning' }}"
                                                    style="font-size: 14px;">
                                                    {{ number_format($item->cantidad, 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ number_format($item->stock_minimo, 0) }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    {{ $item->rop ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if (isset($item->faltante) && $item->faltante > 0)
                                                    <span class="badge badge-danger">{{ $item->faltante }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($item->tipo_alerta == 'Stock mínimo')
                                                    <span class="badge badge-danger">Stock mínimo</span>
                                                @else
                                                    <span class="badge badge-warning">Necesita reorden (ROP)</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('compras.create') }}?productos={{ $item->producto_id }}&sucursal={{ $sucursal->nombre }}"
                                                    class="btn btn-success btn-sm" title="Comprar este producto">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    <span class="d-none d-md-inline">Comprar</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> ¡Todo en orden!</h5>
                            No hay productos con stock bajo en esta sucursal. El inventario está en niveles óptimos.
                        </div>

                        <div class="text-center mt-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                            <h4 class="mt-3">No se encontraron productos críticos</h4>
                            <p class="text-muted">Todos los productos tienen stock por encima del nivel mínimo</p>
                            <a href="{{ route('mostrar_inventario_por_sucursal.show', $sucursal->id) }}"
                                class="btn btn-primary mt-2">
                                <i class="fas fa-arrow-left"></i> Volver al inventario
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Estilos para impresión */
        @media print {

            .sidebar,
            .main-header,
            .card-header .btn-group,
            .alert,
            .info-box {
                display: none !important;
            }

            .card,
            .card-body {
                border: none !important;
                padding: 0 !important;
            }

            .table {
                font-size: 10px;
            }

            .badge {
                border: none !important;
            }

            .btn-group {
                display: none !important;
            }
        }

        .table tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .info-box {
            margin-bottom: 0;
        }

        .info-box .info-box-number {
            font-size: 24px;
            font-weight: bold;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .card-header .btn-group {
                flex-wrap: wrap;
                gap: 5px;
            }

            .card-header .btn-group .btn {
                font-size: 12px;
                padding: 4px 8px;
            }

            .info-box .info-box-number {
                font-size: 18px;
            }
        }
    </style>
@stop

@section('js')
    @if ($productos_stock_bajo->count() > 0)
        <script>
            $(function() {
                $("#tabla-stock-bajo").DataTable({
                    "pageLength": 10,
                    "language": {
                        "emptyTable": "No hay productos con stock bajo",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ productos críticos",
                        "infoEmpty": "Mostrando 0 a 0 de 0 productos",
                        "infoFiltered": "(Filtrado de _MAX_ total productos)",
                        "lengthMenu": "Mostrar _MENU_ productos",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscador:",
                        "zeroRecords": "Sin resultados encontrados",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
                    },
                    "responsive": true,
                    "lengthChange": true,

                });
            });
        </script>
    @endif
@stop
