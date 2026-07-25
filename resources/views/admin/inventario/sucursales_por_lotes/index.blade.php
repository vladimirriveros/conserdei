@extends('adminlte::page')

@section('title', 'Sucursales')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            {{-- <li class="breadcrumb-item"><a href="{{ route('sucursal_por_lotes.index') }}">Listado de sucursales</a></li> --}}
            <li class="breadcrumb-item active" aria-current="page">Listado de sucursales</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <!-- Agregar meta tag CSRF aquí o asegurarte que está en el layout principal -->
    <meta name="csrf-token" content="{{ csrf_token() }}">



    <div class="row">
        @foreach ($sucursales as $sucursal)
            <div class="col-md-3 col-sm-6 col-12">
                <!-- info-box -->
                <div class="info-box {{ $sucursal->tiene_stock_bajo ? 'bg-danger text-white' : '' }}">
                    <a href="{{ route('mostrar_inventario_por_sucursal.show', $sucursal->id) }}">
                        <span class="info-box-icon {{ $sucursal->tiene_stock_bajo ? 'bg-danger' : 'bg-info' }}">
                            <img src="{{ url('/img/tienda.gif') }}" alt="">
                        </span>
                    </a>

                    <div class="info-box-content">
                        <span class="info-box-text"><b>Sucursal {{ $sucursal->nombre }}</b></span>
                        <span class="info-box-number">{{ $sucursal->total_inventario }} Productos en stock</span>
                        {{-- <span class="info-box-number">{{ $sucursal->total_inventario_calculado ?? $sucursal->inventario_sucural_lotes_count }} Productos en stock</span> --}}
                        @if($sucursal->tiene_stock_bajo)
                            <span class="badge badge-warning mt-1">⚠ Stock bajo</span>
                        @endif
                        @if($sucursal->stock_bajo_count > 0)
                            <small>({{ $sucursal->stock_bajo_count }} productos con stock bajo)</small>
                        @endif
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        @endforeach
    </div>
@stop

@section('css')
    <style>
        /* Para que el texto sea legible cuando el fondo es rojo */
        .info-box.bg-danger.text-white .info-box-content,
        .info-box.bg-danger.text-white .info-box-icon {
            color: #fff !important;
        }

        /* Estilos adicionales para el botón */
        #btnMigrarLotes {
            margin-top: 0;
        }

        /* Estilos para el loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 20px;
        }

        .loading-spinner {
            text-align: center;
            background: rgba(0,0,0,0.8);
            padding: 20px 40px;
            border-radius: 10px;
        }

        .loading-spinner i {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Configuración global de AJAX para incluir CSRF token automáticamente
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Crear overlay de carga
        $('body').append(`
            <div class="loading-overlay" id="loadingOverlay">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Migrando datos, por favor espere...</p>
                </div>
            </div>
        `);

        $('#btnMigrarLotes').on('click', function() {
            let sucursalId = $('#sucursalSelect').val();

            if (!sucursalId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debe seleccionar una sucursal para migrar',
                });
                return;
            }

            // Confirmar antes de migrar
            Swal.fire({
                title: '¿Está seguro?',
                text: "Se migrarán todos los lotes a la sucursal seleccionada. Esta acción creará registros en inventario y movimientos.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, migrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar overlay de carga
                    $('#loadingOverlay').fadeIn();

                    // Deshabilitar botón
                    $('#btnMigrarLotes').prop('disabled', true);

                    $.ajax({
                        url: '{{ route("inventario.migrar") }}',
                        type: 'POST',
                        data: {
                            sucursal_id: sucursalId
                        },
                        success: function(response) {
                            $('#loadingOverlay').fadeOut();

                            if(response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: response.message,
                                    showConfirmButton: true
                                }).then(() => {
                                    // Recargar la página para ver los cambios
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                                $('#btnMigrarLotes').prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            $('#loadingOverlay').fadeOut();
                            $('#btnMigrarLotes').prop('disabled', false);

                            let errorMsg = 'Error en la petición';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@stop
