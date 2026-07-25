@extends('adminlte::page')

@section('title', 'Lotes')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            {{-- <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Lotes</a></li> --}}
            <li class="breadcrumb-item active" aria-current="page">Listado de Lotes</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Filtrado de datos</b></h3>
                    <div class="card-tools">
                        {{-- <span class="badge badge-info mr-2" id="resultados-count">{{ count($lotes) }} resultados</span> --}}
                        <a href="{{ route('lotes.pdf', request()->all()) }}" class="btn btn-danger btn-sm"
                            id="btn-exportar-pdf" {{-- Agregar este ID --}} target="_blank"
                            title="Generar PDF con los filtros actuales">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </a>
                        <a href="{{ route('lotes.vencidos') }}" class="btn btn-warning btn-sm ml-2">
                            <i class="fas fa-exclamation-triangle"></i> Filtrar Lotes Vencidos
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="row align-items-end">
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
                                    placeholder="Lote, producto, proveedor, categoría..." value="{{ request('search') }}"
                                    autocomplete="off">
                                <small class="text-muted" id="buscando-msg" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Buscando...
                                </small>
                            </div>
                        </div>

                        {{-- BOTÓN LIMPIAR --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="invisible">Limpiar</label>
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
                    <h3 class="card-title"><b>Lotes registrados</b></h3>
                </div>
                <div class="card-body table-responsive" id="tabla-container">
                    @include('admin.lotes.partials.tabla')
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

        /* Estilos para filtros */
        .fecha-filtro,
        #search {
            transition: border-color 0.3s;
        }

        .fecha-filtro:focus,
        #search:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .invisible {
            visibility: hidden;
            height: 0;
            margin: 0;
            padding: 0;
        }

        #btn-limpiar {
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .row.align-items-end .form-group {
            margin-bottom: 0;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            let timeoutId;
            let table;

            // Función para actualizar el enlace del PDF
            function actualizarEnlacePDF() {
                var fecha_desde = $('#fecha_desde').val();
                var fecha_hasta = $('#fecha_hasta').val();
                var search = $('#search').val();

                var params = new URLSearchParams();
                if (fecha_desde) params.append('fecha_desde', fecha_desde);
                if (fecha_hasta) params.append('fecha_hasta', fecha_hasta);
                if (search) params.append('search', search);

                var nuevaURL = "{{ route('lotes.pdf') }}?" + params.toString();
                $('#btn-exportar-pdf').attr('href', nuevaURL);
            }

            // Función para inicializar DataTable
            function initDataTable() {
                if ($.fn.DataTable.isDataTable('#example1')) {
                    $('#example1').DataTable().destroy();
                }

                table = $("#example1").DataTable({
                    "pageLength": 10,
                    "language": {
                        "scrollX": true,
                        "emptyTable": "No hay información",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ Lotes",
                        "infoEmpty": "Mostrando 0 a 0 de 0 Lotes",
                        "infoFiltered": "(Filtrado de _MAX_ total Lotes)",
                        "lengthMenu": "Mostrar _MENU_ Lotes",
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
                    "autoWidth": false
                });
            }

            // Inicializar tabla por primera vez
            initDataTable();

            // Función para actualizar resultados mediante AJAX
            function actualizarResultados() {
                var fecha_desde = $('#fecha_desde').val();
                var fecha_hasta = $('#fecha_hasta').val();
                var search = $('#search').val();

                // Validar que si hay fecha_desde, también debe haber fecha_hasta (o viceversa)
                if ((fecha_desde && !fecha_hasta) || (!fecha_desde && fecha_hasta)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fechas incompletas',
                        text: 'Debe seleccionar ambas fechas o ninguna',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Mostrar mensaje en la tabla
                    $('#tabla-container').html(`
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle"></i>
                            Debe seleccionar ambas fechas para filtrar por rango
                        </div>
                    `);
                    $('#resultados-count').text('0 resultados');
                    $('#buscando-msg').hide();
                    return;
                }

                $('#buscando-msg').show();

                $.ajax({
                    url: "{{ route('lotes.index') }}",
                    type: 'GET',
                    data: {
                        fecha_desde: fecha_desde,
                        fecha_hasta: fecha_hasta,
                        search: search,
                        ajax: true
                    },
                    success: function(response) {
                        $('#tabla-container').html(response.html);
                        $('#resultados-count').text(response.total + ' resultados');

                        // Reinicializar DataTable solo si hay resultados
                        if (response.total > 0) {
                            initDataTable();
                        }

                        actualizarEnlacePDF();
                        $('#buscando-msg').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', error);
                        $('#buscando-msg').html('<span class="text-danger">Error al buscar</span>');
                        setTimeout(function() {
                            $('#buscando-msg').fadeOut();
                            $('#buscando-msg').html(
                                '<i class="fas fa-spinner fa-spin"></i> Buscando...');
                        }, 2000);
                    }
                });
            }

            // Eventos en tiempo real
            $('#search').on('keyup', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(actualizarResultados, 500);
            });

            $('#fecha_desde, #fecha_hasta').on('change', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(actualizarResultados, 300);
            });

            $('#btn-limpiar').on('click', function() {
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');
                $('#search').val('');
                actualizarResultados();
            });

            // Actualizar enlace PDF cuando cambian los filtros (sin recargar)
            $('#search, #fecha_desde, #fecha_hasta').on('change keyup', function() {
                actualizarEnlacePDF();
            });

            // Actualizar URL con filtros
            function actualizarURL() {
                var params = new URLSearchParams();
                if ($('#fecha_desde').val()) params.append('fecha_desde', $('#fecha_desde').val());
                if ($('#fecha_hasta').val()) params.append('fecha_hasta', $('#fecha_hasta').val());
                if ($('#search').val()) params.append('search', $('#search').val());

                var newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', newURL);
            }

            $('#search, #fecha_desde, #fecha_hasta').on('change keyup', function() {
                actualizarURL();
            });

            // Prevenir envío del formulario al presionar Enter
            $('#search').on('keypress', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    return false;
                }
            });

            // Llamar una vez al inicio para establecer el enlace inicial
            actualizarEnlacePDF();
        });
    </script>
@stop
