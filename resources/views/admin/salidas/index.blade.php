@extends('adminlte::page')

@section('title', 'Salidas')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('salidas.index') }}">Salidas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Salidas</li>
        </ol>
    </nav>
    <hr>

    @if(session('nota_salida_url'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Salida finalizada!',
                    html: `
                        <p>La salida ha sido procesada correctamente.</p>
                        <p>¿Deseas ver o descargar la nota de salida?</p>
                        <div style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
                            <a href="{{ session('nota_salida_url') }}" target="_blank" class="btn btn-success" style="padding: 8px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
                                <i class="fas fa-eye"></i> Ver Nota
                            </a>
                            <a href="{{ route('salidas.descargar-nota', session('salida_id')) }}" class="btn btn-primary" style="padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    `,
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endif
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- TARJETAS DE RESUMEN DE TOTALES --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $salidas->count() }}</h3>
                            <p>Total de Salidas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            <i class="fas fa-chart-line"></i> Registradas
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>${{ number_format($salidas->sum('total'), 2) }}</h3>
                            <p>Total en Salidas</p>
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
                                $salidasPendientes = $salidas->where('estado', 'pendiente')->count();
                                $salidasEntregadas = $salidas->where('estado', 'Entregado')->count();
                                $salidasEnProceso = $salidas->where('estado', 'en proceso')->count();
                            @endphp
                            <h3>{{ $salidasPendientes }} / {{ $salidasEntregadas }} / {{ $salidasEnProceso }}</h3>
                            <p>Pendientes / Entregadas / En Proceso</p>
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
                    <h3 class="card-title"><b>Salidas registradas</b></h3>

                    <div class="card-tools">
                        <a class="btn btn-primary" href="{{ route('salidas.create') }}">
                            Crear nuevo
                        </a>
                    </div>
                </div>

                <div class="card-body table-responsive">

                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">

                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Sucursal</th>
                                <th>Fecha de la Salida</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th style="text-align:center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($salidas as $salida)
                                <tr>
                                    <td style="text-align:center">{{ $loop->iteration }}</td>
                                    <td>{{ $salida->sucursal()->first()->nombre }}</td>
                                    <td>{{ $salida->fecha }}</td>
                                    <td class="text-right">${{ number_format($salida->total, 2) }}</td>
                                    <td>
                                        @if($salida->estado == 'Entregado')
                                            <span class="badge badge-success">{{ $salida->estado }}</span>
                                        @elseif($salida->estado == 'pendiente')
                                            <span class="badge badge-warning">{{ $salida->estado }}</span>
                                        @elseif($salida->estado == 'en proceso')
                                            <span class="badge badge-info">{{ $salida->estado }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $salida->estado }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center">
                                        <div class="btn-group">
                                            <a href="{{ route('salidas.show', $salida->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if ($salida->estado != 'Entregado')
                                                <a href="{{ route('salidas.edit', $salida->id) }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-edit"></i> Continuar
                                                </a>
                                                <form action="{{ route('salidas.destroy', $salida->id) }}" id="miformulario{{ $salida->id }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="preguntar{{ $salida->id }}(event)">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                <script>
                                                    function preguntar{{ $salida->id }}(event) {
                                                        event.preventDefault();
                                                        Swal.fire({
                                                            title: "¿Eliminar salida #{{ $salida->id }}?",
                                                            text: "Esta acción no se puede revertir",
                                                            icon: "question",
                                                            showCancelButton: true,
                                                            confirmButtonColor: "#3085d6",
                                                            cancelButtonColor: "#d33",
                                                            confirmButtonText: "Si, eliminar",
                                                            cancelButtonText: "Cancelar"
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                document.getElementById("miformulario{{ $salida->id }}").submit();
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
                                <th class="text-right">${{ number_format($salidas->sum('total'), 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>

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

        /* Estilos para badges de estado */
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
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        /* Estilos para las tarjetas de resumen */
        .small-box {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
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
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Salidas",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Salidas",
                    "infoFiltered": "(Filtrado de _MAX_ total Salidas)",
                    "lengthMenu": "Mostrar _MENU_ Salidas",
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
