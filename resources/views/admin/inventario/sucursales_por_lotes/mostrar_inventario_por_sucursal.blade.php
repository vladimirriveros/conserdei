@extends('adminlte::page')

@section('title', 'Sucursales Stock')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sucursal_por_lotes.index') }}">Listado de sucursales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sucursal: {{ $sucursal->nombre }}</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <!-- /.card-header -->
                <div class="card-header">
                    <div class="row w-100 align-items-center">
                        <div class="col-md-8">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-warehouse mr-2 text-primary"></i>
                                <b>Inventario - Sucursal: {{ $sucursal->nombre }}</b>
                            </h3>
                            <small class="text-muted ml-2">
                                <i class="fas fa-boxes"></i> Total: {{ count($inventario_sucursal_por_lotes) }} productos
                            </small>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="btn-group" role="group">
                                <a href="{{ route('inventario.sucursal.pdf', $sucursal->id) }}"
                                    class="btn btn-danger btn-sm" target="_blank"
                                    title="Generar PDF del inventario completo">
                                    <i class="fas fa-file-pdf"></i>
                                    <span class="d-none d-md-inline">PDF</span>
                                </a>

                                <a href="{{ route('inventario.stock_bajo_sucursal', $sucursal->id) }}"
                                    class="btn btn-warning btn-sm" title="Ver solo productos con stock bajo">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span class="d-none d-md-inline">Stock Bajo</span>
                                </a>

                                <button type="button" class="btn btn-info btn-sm" onclick="window.location.reload()"
                                    title="Actualizar vista">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="d-none d-md-inline">Actualizar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- //TARJETAS --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $inventario_sucursal_por_lotes->where('necesita_reorden', true)->count() }}</h3>
                                <p>Productos por reordenar</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cart-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $inventario_sucursal_por_lotes->where('cantidad', '>', 0)->count() }}</h3>
                                <p>Productos con stock</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $inventario_sucursal_por_lotes->where('cantidad', 0)->count() }}</h3>
                                <p>Sin stock</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Código Producto</th>
                                <th>Producto</th>
                                <th>Cantidad Total</th>
                                <th>Stock Mínimo</th>
                                <th>Stock Máximo</th>
                                <th>Consumo/día</th>
                                <th>T. Entrega</th>
                                <th>ROP</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventario_sucursal_por_lotes as $item)
                                <tr
                                    class="
                                            @if ($item->cantidad <= $item->stock_minimo) table-danger
                                            @elseif($item->cantidad <= $item->rop) table-warning @endif
                                        ">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->codigo_producto }}</td>
                                    <td>
                                        {{ $item->producto }}
                                        {{-- Prioridad 1: Crítico --}}
                                        @if ($item->cantidad <= $item->stock_minimo)
                                            <span class="badge badge-danger float-right">¡Urgente!</span>
                                            {{-- Prioridad 2: Reorden (Menor a ROP pero mayor a mínimo) --}}
                                        @elseif($item->cantidad <= $item->rop)
                                            <span class="badge badge-warning float-right">Realizar pedido</span>
                                        @endif
                                    </td>
                                    <td
                                        class="text-center font-weight-bold {{ $item->cantidad == 0 ? 'text-danger' : ($item->cantidad <= $item->rop ? 'text-dark' : 'text-success') }}">
                                        {{ $item->cantidad }}
                                    </td>
                                    <td class="text-center">{{ $item->stock_minimo }}</td>
                                    <td class="text-center">{{ $item->stock_maximo }}</td>
                                    <td class="text-center">{{ $item->consumo_diario ?? '?' }}</td>
                                    <td class="text-center">{{ $item->tiempo_entrega ?? '?' }} días</td>
                                    <td class="text-center">
                                        <span
                                            class="badge {{ $item->cantidad <= $item->rop ? 'badge-danger' : 'badge-info' }}">
                                            {{ $item->rop }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- Columna de Estado simplificada --}}
                                        @if ($item->cantidad == 0)
                                            <span class="badge badge-danger">Sin stock</span>
                                        @elseif ($item->cantidad <= $item->stock_minimo)
                                            <span class="badge badge-danger">Stock crítico</span>
                                        @elseif ($item->cantidad <= $item->rop)
                                            <span class="badge badge-warning">Pedido pendiente</span>
                                        @elseif ($item->cantidad >= $item->stock_maximo)
                                            <span class="badge badge-success">Lleno</span>
                                        @else
                                            <span class="badge badge-info">OK</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{-- Botones de acción --}}
                                        @if ($item->cantidad == 0)
                                            <a href="{{ route('compras.create') }}?productos={{ $item->producto_id }}&sucursal={{ $sucursal->nombre }}"
                                                class="btn btn-success btn-sm mb-1" title="Comprar este producto">
                                                <i class="fas fa-shopping-cart"></i> Comprar
                                            </a>
                                            <form action="{{ route('productos.desactivar', $item->producto_id) }}"
                                                method="POST" style="display:inline;" class="form-desactivar-producto">
                                                @csrf
                                                @method('PUT')
                                                <button type="button" class="btn btn-secondary btn-sm btn-desactivar"
                                                    data-producto-id="{{ $item->producto_id }}"
                                                    data-producto-nombre="{{ $item->producto }}">
                                                    <i class="fas fa-ban"></i> Desactivar
                                                </button>
                                            </form>
                                        @elseif ($item->cantidad <= $item->rop)
                                            <a href="{{ route('compras.create') }}?productos={{ $item->producto_id }}&sucursal={{ $sucursal->nombre }}"
                                                class="btn btn-success btn-sm" title="Comprar">
                                                <i class="fas fa-shopping-cart"></i> Pedir
                                            </a>
                                        @endif

                                        <button type="button" class="btn btn-info btn-sm mt-1"
                                            onclick="verDetalleROP({{ $item->producto_id }})">
                                            <i class="fas fa-chart-line"></i> Detalle ROP
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>

    {{-- MODAL DETALLE ROP --}}
    <div class="modal fade" id="modalDetalleROP" tabindex="-1" role="dialog" aria-labelledby="modalDetalleROPLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetalleROPLabel">
                        <i class="fas fa-chart-line"></i> Detalle ROP - <span id="productoNombre">Cargando...</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Pestañas -->
                    <ul class="nav nav-tabs mb-3" id="ropTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="demanda-tab" data-toggle="tab" href="#demanda"
                                role="tab">
                                <i class="fas fa-chart-bar"></i> Demanda Diaria
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tiempos-tab" data-toggle="tab" href="#tiempos" role="tab">
                                <i class="fas fa-truck"></i> Tiempos de Entrega
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- SECCIÓN A: Demanda Diaria -->
                        <div class="tab-pane fade show active" id="demanda" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h6 class="card-title"><i class="fas fa-calendar-alt"></i> Filtro por fechas
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>Fecha desde</label>
                                                    <input type="date" id="demanda_fecha_desde"
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Fecha hasta</label>
                                                    <input type="date" id="demanda_fecha_hasta"
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>&nbsp;</label>
                                                    <button class="btn btn-info btn-sm btn-block"
                                                        onclick="aplicarFiltroDemanda()">
                                                        <i class="fas fa-search"></i> Aplicar filtro
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info" id="resumenDemanda">
                                <i class="fas fa-info-circle"></i> Seleccione fechas para ver el resumen
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm" id="tablaDemanda">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nro</th>
                                            <th>Fecha</th>
                                            <th class="text-center">Cantidad vendida</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyDemanda">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Seleccione fechas para
                                                cargar datos</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- SECCIÓN B: Tiempos de Entrega -->
                        <div class="tab-pane fade" id="tiempos" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card card-outline card-warning">
                                        <div class="card-header">
                                            <h6 class="card-title"><i class="fas fa-chart-line"></i> Rango de compras</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Mostrar últimas compras</label>
                                                    <select id="tiempos_rango" class="form-control form-control-sm"
                                                        onchange="aplicarFiltroTiempos()">
                                                        <option value="2">Últimas 2 compras</option>
                                                        <option value="3">Últimas 3 compras</option>
                                                        <option value="5">Últimas 5 compras</option>
                                                        <option value="6">Últimas 6 compras</option>
                                                        <option value="7">Últimas 7 compras</option>
                                                        <option value="8">Últimas 8 compras</option>
                                                        <option value="9">Últimas 9 compras</option>
                                                        <option value="10">Últimas 10 compras</option>
                                                        <option value="30">Últimas 30 compras</option>
                                                        <option value="60">Últimas 60 compras</option>
                                                        <option value="todas" selected>Todas las compras</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>&nbsp;</label>
                                                    <button class="btn btn-warning btn-sm btn-block"
                                                        onclick="aplicarFiltroTiempos()">
                                                        <i class="fas fa-sync-alt"></i> Actualizar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning" id="resumenTiempos">
                                <i class="fas fa-info-circle"></i> Seleccione un rango para ver el resumen
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm" id="tablaTiempos">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Fecha pedido</th>
                                            <th>Fecha entrada</th>
                                            <th class="text-center">Días de entrega</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyTiempos">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Seleccione un rango para
                                                cargar datos</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Estilos para DataTables */
        #example1_wrapper .dt-buttons {
            background-color: transparent;
            box-shadow: none;
            border: none;
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        #example1_wrapper .btn {
            color: #fff;
            border-radius: 4px;
            padding: 5px 15px;
            font-size: 14px;
        }

        /* Colores de botones */
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-info {
            background-color: #17a2b8;
            border: none;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
            border: none;
        }

        .btn-default {
            background-color: #6e7176;
            color: #212529;
            border: none;
        }

        /* Estilos del header */
        /* Estilos del header */
        .card-header {
            padding: 0.75rem 1.25rem;
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }

        .card-header .row {
            margin: 0;
            width: 100%;
        }

        .card-header .col-md-8,
        .card-header .col-md-4 {
            padding: 0;
        }

        .card-header .text-right {
            text-align: right !important;
        }

        .card-tools .btn-group .btn {
            border-radius: 4px !important;
            margin-left: 5px;
        }

        .card-tools .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .card-header .col-md-8,
            .card-header .col-md-4 {
                text-align: center !important;
                margin-bottom: 10px;
            }

            .card-header .col-md-4 {
                margin-bottom: 0;
            }

            .card-header .btn-group {
                justify-content: center;
            }

            .card-tools {
                margin-top: 10px;
                width: 100%;
            }

            .card-tools .btn-group {
                display: flex;
                width: 100%;
            }

            .card-tools .btn-group .btn {
                flex: 1;
                margin: 0 2px;
            }

            .d-none.d-md-inline {
                display: inline !important;
                margin-left: 5px;
            }
        }
    </style>
@stop

@section('js')



    <script>
        $(function() {
            $("#example1").DataTable({
                "pageLength": 10,
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
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
                "autoWidth": false,

            }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
        });

        // Versión simple con envío normal del formulario
        $(document).on('click', '.btn-desactivar', function(e) {
            e.preventDefault();

            const $boton = $(this);
            const $form = $boton.closest('form');
            const productoNombre = $boton.data('producto-nombre') || 'este producto';

            Swal.fire({
                title: '¿Desactivar producto?',
                html: `
            <div style="text-align: center;">
                <i class="fas fa-box" style="font-size: 2rem; color: #dc3545; margin-bottom: 10px;"></i>
                <p><strong>${productoNombre}</strong></p>
                <p style="color: #dc3545;">Este producto se desactivará hasta que se vuelva a adquirir</p>
            </div>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $form.submit();
                }
            });
        });
    </script>

    <script>
        // Variable global para almacenar el ID del producto actual
        var productoActualId = null;
        var sucursalActualId = {{ $sucursal->id }};

        // Función para abrir el modal y cargar datos iniciales
        function verDetalleROP(productoId) {
            productoActualId = productoId;

            // Limpiar y mostrar modal
            $('#modalDetalleROP').modal('show');
            $('#productoNombre').text('Cargando...');

            // Establecer fechas por defecto (últimos 30 días)
            var hoy = new Date();
            var hace30Dias = new Date();
            hace30Dias.setDate(hoy.getDate() - 30);

            $('#demanda_fecha_desde').val(hace30Dias.toISOString().split('T')[0]);
            $('#demanda_fecha_hasta').val(hoy.toISOString().split('T')[0]);
            $('#tiempos_rango').val('todas');

            // Cargar datos iniciales
            cargarDemandaDiaria();
            cargarTiemposEntrega();
        }

        // Cargar demanda diaria
        function cargarDemandaDiaria() {
            var fechaDesde = $('#demanda_fecha_desde').val();
            var fechaHasta = $('#demanda_fecha_hasta').val();

            if (!fechaDesde || !fechaHasta) {
                return;
            }

            $.ajax({
                // url: '{{ url('/admin/inventario/producto') }}/' + productoActualId + '/detalle-rop',
                url: '{{ route("inventario.detalle.rop", ["productoId" => ":id"]) }}'.replace(':id', productoActualId),
                type: 'GET',
                data: {
                    sucursal_id: sucursalActualId,
                    fecha_desde: fechaDesde,
                    fecha_hasta: fechaHasta
                },
                success: function(response) {
                    if (response.success) {
                        $('#productoNombre').text(response.producto);

                        var tbody = $('#tbodyDemanda');
                        tbody.empty();

                        if (response.demanda.data.length > 0) {
                            var contador = 1;
                            $.each(response.demanda.data, function(index, item) {
                                tbody.append(`
                            <tr>
                                <td>${contador}</td>
                                <td>${item.fecha}</td>
                                <td class="text-center font-weight-bold">${item.total_vendido}</td>
                            </tr>
                        `);
                                contador++;
                            });

                            $('#resumenDemanda').html(`
                        <i class="fas fa-chart-line"></i>
                        <strong>Resumen:</strong>
                        Total vendido: ${response.demanda.total_vendido} unidades |
                        Días con ventas: ${response.demanda.dias_totales} |
                        <span class="text-warning">Promedio diario: ${response.demanda.promedio} unidades/día</span>
                    `);
                        } else {
                            tbody.append(
                                '</td><td colspan="2" class="text-center text-muted">No hay ventas en el rango seleccionado</td></tr>'
                            );
                            $('#resumenDemanda').html(
                                '<i class="fas fa-info-circle"></i> No hay datos en el rango seleccionado');
                        }
                    }
                },
                error: function() {
                    $('#tbodyDemanda').html(
                        '<tr><td colspan="2" class="text-center text-danger">Error al cargar datos</td></tr>'
                    );
                }
            });
        }

        // Cargar tiempos de entrega
        function cargarTiemposEntrega() {
            var rango = $('#tiempos_rango').val();

            $.ajax({
                // url: '{{ url('/admin/inventario/producto') }}/' + productoActualId + '/detalle-rop',
                url: '{{ route("inventario.detalle.rop", ["productoId" => ":id"]) }}'.replace(':id', productoActualId),
                type: 'GET',
                data: {
                    sucursal_id: sucursalActualId,
                    rango_compras: rango
                },
                success: function(response) {
                    if (response.success) {
                        var tbody = $('#tbodyTiempos');
                        tbody.empty();

                        if (response.tiempos_entrega.data.length > 0) {
                            $.each(response.tiempos_entrega.data, function(index, item) {
                                var colorBadge = item.dias_entrega <= 3 ? 'success' : (item
                                    .dias_entrega <= 7 ? 'warning' : 'danger');
                                tbody.append(`
                            <tr>
                                <td>${item.fecha_pedido}</td>
                                <td>${item.fecha_entrada || '-'}</td>
                                <td class="text-center">
                                    <span class="badge badge-${colorBadge}">${item.dias_entrega} días</span>
                                </td>
                            </tr>
                        `);
                            });

                            $('#resumenTiempos').html(`
                        <i class="fas fa-truck"></i>
                        <strong>Resumen:</strong>
                        Total compras analizadas: ${response.tiempos_entrega.total_compras} |
                        <span class="text-primary">Tiempo de entrega promedio: ${response.tiempos_entrega.promedio} días</span>
                    `);
                        } else {
                            tbody.append(
                                '<tr><td colspan="3" class="text-center text-muted">No hay compras registradas</td></tr>'
                            );
                            $('#resumenTiempos').html(
                                '<i class="fas fa-info-circle"></i> No hay datos de compras');
                        }
                    }
                },
                error: function() {
                    $('#tbodyTiempos').html(
                        '<tr><td colspan="3" class="text-center text-danger">Error al cargar datos</td></tr>'
                    );
                }
            });
        }

        // Aplicar filtro de demanda
        function aplicarFiltroDemanda() {
            cargarDemandaDiaria();
        }

        // Aplicar filtro de tiempos
        function aplicarFiltroTiempos() {
            cargarTiemposEntrega();
        }
    </script>
@stop
