@extends('adminlte::page')

@section('title', 'Compras')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Compras</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- TARJETA DE RESUMEN DE TOTALES --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $compras->count() }}</h3>
                            <p>Total de Compras</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            <i class="fas fa-chart-line"></i> Registradas
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>${{ number_format($compras->sum('total'), 2) }}</h3>
                            <p>Total Invertido</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            <i class="fas fa-chart-pie"></i> Suma total
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            @php
                                $comprasPendientes = $compras->where('estado', 'pendiente')->count();
                                $comprasRecibidas = $compras->where('estado', 'Recibido')->count();
                                $comprasEnviadas = $compras->where('estado', 'enviado al proveedor')->count();
                                $comprasAnuladas = $compras->where('estado', 'anulado')->count();
                            @endphp
                            <h3>{{ $comprasPendientes }} / {{ $comprasRecibidas }} / {{ $comprasEnviadas }} /
                                {{ $comprasAnuladas }}</h3>
                            <p>Pendientes / Recibidas / Enviadas / Anuladas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            <i class="fas fa-filter"></i> Por estado
                        </a>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Compras registradas</b></h3>

                    <div class="card-tools">
                        <a class="btn btn-primary" href="{{ route('compras.create') }}">Crear nuevo</a>
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive" style="display: block;">
                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Proveedor</th>
                                <th>Fecha de la Compra</th>
                                <th>Total de la Compra</th>
                                <th>Estado</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($compras as $compra)
                                <tr>
                                    <td style="text-align: center">{{ $loop->iteration }}</td>
                                    <td>{{ $compra->proveedor()->first()->nombre }}</td>
                                    <td>{{ $compra->fecha }}</td>
                                    <td class="text-right">${{ number_format($compra->total, 2) }}</td>
                                    <td>
                                        @if ($compra->estado == 'Recibido')
                                            <span class="badge badge-success">{{ $compra->estado }}</span>
                                        @elseif($compra->estado == 'pendiente')
                                            <span class="badge badge-warning">{{ $compra->estado }}</span>
                                        @elseif($compra->estado == 'enviado al proveedor')
                                            <span class="badge badge-info">{{ $compra->estado }}</span>
                                        @elseif($compra->estado == 'anulado')
                                            <span class="badge badge-danger">{{ $compra->estado }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $compra->estado }}</span>
                                        @endif
                                    </td>

                                    <td style="text-align: center">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a href="{{ route('compras.show', $compra->id) }}"
                                                class="btn btn-info btn-sm"><i class="fas fa-eye"></i> </a>

                                            @if ($compra->estado != 'Recibido' && $compra->estado != 'anulado')
                                                <a href="{{ route('compras.edit', $compra->id) }}"
                                                    class="btn btn-success btn-sm"><i class="fas fa-edit"></i> Continuar</a>
                                                <form action="{{ route('compras.destroy', $compra->id) }}"
                                                    id="miformulario{{ $compra->id }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        data-id="{{ $compra->id }}"
                                                        data-tiene-detalles="{{ $compra->tiene_detalles ? 'true' : 'false' }}"
                                                        data-proveedor="{{ $compra->proveedor()->first()->nombre }}"
                                                        onclick="preguntar(event, this)">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                <script>
                                                    function preguntar(event, button) {
                                                        event.preventDefault();

                                                        var compraId = button.getAttribute('data-id');
                                                        var tieneDetalles = button.getAttribute('data-tiene-detalles') === 'true';
                                                        var proveedor = button.getAttribute('data-proveedor');
                                                        var formId = 'miformulario' + compraId;

                                                        var titulo = '';
                                                        var texto = '';
                                                        var icono = 'question';
                                                        var botonConfirmacion = '';

                                                        if (tieneDetalles) {
                                                            titulo = '¿Anular la compra #' + compraId + '?';
                                                            texto = 'Esta compra tiene productos solicitados al proveedor ' + proveedor +
                                                                '. Si la anula, los productos quedarán en el historial como "Anulados". ¿Desea continuar?';
                                                            botonConfirmacion = 'Sí, anular compra';
                                                        } else {
                                                            titulo = '¿Eliminar la compra #' + compraId + '?';
                                                            texto = 'Esta compra no tiene productos asociados. Se eliminará permanentemente.';
                                                            botonConfirmacion = 'Sí, eliminar';
                                                        }

                                                        Swal.fire({
                                                            title: titulo,
                                                            text: texto,
                                                            icon: icono,
                                                            showCancelButton: true,
                                                            confirmButtonColor: tieneDetalles ? "#d33" : "#3085d6",
                                                            cancelButtonColor: "#d33",
                                                            confirmButtonText: botonConfirmacion,
                                                            cancelButtonText: "Cancelar"
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                document.getElementById(formId).submit();
                                                            }
                                                        });
                                                    }
                                                </script>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">TOTAL GENERAL:</th>
                                <th class="text-right">${{ number_format($compras->sum('total'), 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@section('css')
    <style>
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

        .badge {
            font-size: 11px;
            padding: 5px 8px;
            border-radius: 4px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .small-box {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
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
    </style>
@stop

@section('js')
    <script>
        $(function() {
            $("#example1").DataTable({
                "pageLength": 10,
                "language": {
                    "scrollX": true,
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Compras",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Compras",
                    "infoFiltered": "(Filtrado de _MAX_ total Compras)",
                    "lengthMenu": "Mostrar _MENU_ Compras",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando",
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
                buttons: [{
                        text: '<i class="fas fa-copy"></i> COPIAR',
                        extend: 'copy',
                        className: 'btn btn-default',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        extend: 'pdf',
                        className: 'btn btn-danger',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        extend: 'csv',
                        className: 'btn btn-info',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        text: '<i class="fas fa-file-excel"></i> EXCEL',
                        extend: 'excel',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        text: '<i class="fas fa-print"></i> IMPRIMIR',
                        extend: 'print',
                        className: 'btn btn-warning',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                ]
            }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
        });
    </script>
@stop
