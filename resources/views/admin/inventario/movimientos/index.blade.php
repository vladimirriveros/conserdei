@extends('adminlte::page')

@section('title', 'Movimientos')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            {{-- <li class="breadcrumb-item"><a href="{{ url('/admin/inventario') }}">Historial de Inventario</a></li> --}}
            <li class="breadcrumb-item active" aria-current="page">Movimientos de Inventario</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="ROW">
        <div class="COL-MD-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Filtrado de datos</b></h3>
                    <div class="card-tools">
                        <span class="badge badge-info mr-2" id="resultados-count">{{ count($movimientos) }}
                            resultados</span>
                        <button type="button" class="btn btn-danger btn-sm" id="btn-exportar-pdf">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="row">
                        {{-- DESDE LA FECHA --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_desde">Desde:</label>
                                <input type="date" class="form-control fecha-filtro" name="fecha_desde" id="fecha_desde"
                                    value="{{ request('fecha_desde') }}">
                            </div>
                        </div>
                        {{-- HASTA LA FECHA --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_hasta">Hasta:</label>
                                <input type="date" class="form-control fecha-filtro" name="fecha_hasta" id="fecha_hasta"
                                    value="{{ request('fecha_hasta') }}">
                            </div>
                        </div>

                        {{-- BUSCADOR --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">Buscar en tiempo real:</label>
                                <input type="text" class="form-control" name="search" id="search"
                                    placeholder="Tipo, producto, lote, observación..." value="{{ request('search') }}"
                                    autocomplete="off">
                                <small class="text-muted" id="buscando-msg" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Buscando...
                                </small>
                            </div>
                        </div>

                        {{-- BOTÓN LIMPIAR --}}
                        {{-- BOTÓN LIMPIAR --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-secondary btn-block" id="btn-limpiar">
                                    <i class="fas fa-times"></i> Limpiar filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Movimientos de Inventario</b></h3>
                </div>
                <div class="card-body table-responsive" id="tabla-container">
                    @include('admin.inventario.movimientos.partials.tabla')
                </div>
            </div>
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

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
            padding: 3px 7px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 5px;
            display: inline-block;
        }

        /* Estilos para el filtrado en tiempo real */
        .fecha-filtro,
        #search {
            transition: border-color 0.3s;
        }

        .fecha-filtro:focus,
        #search:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .table tbody tr {
            transition: background-color 0.2s;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            let timeoutId;

            // Inicializar DataTable
            var table = $("#example1").DataTable({
                "pageLength": 10,
                "language": {
                    "scrollX": true,
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Movimientos",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Movimientos",
                    "infoFiltered": "(Filtrado de _MAX_ total Movimientos)",
                    "lengthMenu": "Mostrar _MENU_ Movimientos",
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
            });

            // Función para actualizar los resultados mediante AJAX
            function actualizarResultados() {
                var fecha_desde = $('#fecha_desde').val();
                var fecha_hasta = $('#fecha_hasta').val();
                var search = $('#search').val();

                // Mostrar mensaje de "buscando"
                $('#buscando-msg').show();

                $.ajax({
                    url: "{{ route('movimientos.index') }}",
                    type: 'GET',
                    data: {
                        fecha_desde: fecha_desde,
                        fecha_hasta: fecha_hasta,
                        search: search,
                        ajax: true
                    },
                    success: function(response) {
                        // Destruir la tabla actual
                        table.destroy();

                        // Actualizar el HTML de la tabla
                        $('#tabla-container').html(response.html);

                        // Actualizar contador de resultados
                        $('#resultados-count').text(response.total + ' resultados');

                        // Reinicializar DataTable
                        table = $("#example1").DataTable({
                            "pageLength": 10,
                            "language": {
                                "scrollX": true,
                                "emptyTable": "No hay información",
                                "info": "Mostrando _START_ a _END_ de _TOTAL_ Movimientos",
                                "infoEmpty": "Mostrando 0 a 0 de 0 Movimientos",
                                "infoFiltered": "(Filtrado de _MAX_ total Movimientos)",
                                "lengthMenu": "Mostrar _MENU_ Movimientos",
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
                        });

                        // Ocultar mensaje de "buscando"
                        $('#buscando-msg').hide();
                    },
                    error: function() {
                        $('#buscando-msg').html('<span class="text-danger">Error al buscar</span>');
                        setTimeout(function() {
                            $('#buscando-msg').fadeOut();
                        }, 2000);
                    }
                });
            }

            // Evento para búsqueda en tiempo real (con debounce)
            $('#search').on('keyup', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(actualizarResultados,
                    500); // Esperar 500ms después de dejar de escribir
            });

            // Evento para cambios en fechas
            $('#fecha_desde, #fecha_hasta').on('change', function() {
                actualizarResultados();
            });

            // Botón para limpiar filtros
            $('#btn-limpiar').on('click', function() {
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');
                $('#search').val('');
                actualizarResultados();
            });

            // Botón para exportar PDF con filtros actuales
            $('#btn-exportar-pdf').on('click', function() {
                var fecha_desde = $('#fecha_desde').val();
                var fecha_hasta = $('#fecha_hasta').val();
                var search = $('#search').val();

                // Construir URL con los filtros actuales
                var params = new URLSearchParams();
                if (fecha_desde) params.append('fecha_desde', fecha_desde);
                if (fecha_hasta) params.append('fecha_hasta', fecha_hasta);
                if (search) params.append('search', search);

                var url = "{{ route('movimientos.pdf') }}?" + params.toString();

                // Abrir en nueva pestaña
                window.open(url, '_blank');
            });

            // Actualizar la URL con los filtros actuales (opcional)
            function actualizarURL() {
                var params = new URLSearchParams();
                if ($('#fecha_desde').val()) params.append('fecha_desde', $('#fecha_desde').val());
                if ($('#fecha_hasta').val()) params.append('fecha_hasta', $('#fecha_hasta').val());
                if ($('#search').val()) params.append('search', $('#search').val());

                var newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', newURL);
            }

            // Actualizar URL cuando cambian los filtros (opcional)
            $('#search, #fecha_desde, #fecha_hasta').on('change keyup', function() {
                actualizarURL();
            });
        });
    </script>
@stop
