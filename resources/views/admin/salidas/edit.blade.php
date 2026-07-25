@extends('adminlte::page')

@section('title', 'Salida en proceso')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('salidas.index') }}">Salidas</a>
            </li>
            <li class="breadcrumb-item active">
                Salida nro {{ $salida->id }}
            </li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')

    {{-- CARD 1 → DATOS DE LA SALIDA --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <b>Paso 1 | Salida creada</b>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Sucursal</label>
                                <p>{{ $salida->sucursal->nombre }}</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha</label>
                                <p>{{ $salida->fecha }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Motivo</label>
                                <p>{{ $salida->motivo }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Observaciones</label>
                                <p>{{ $salida->observaciones }}</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado</label>
                                <p>{{ $salida->estado }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2 → AGREGAR PRODUCTOS (Livewire) --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <b>Paso 2 | Agregar productos a la salida</b>
                    </h3>
                </div>
                <div class="card-body">
                    <livewire:admin.salidas.items-salida :salida="$salida" :wire:key="'items-'.$salida->id" />
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 3 → FINALIZAR SALIDA --}}
    {{-- CARD 3 → FINALIZAR SALIDA --}}
    <div class="row" x-data="{
            tieneProductos: {{ count($carritoItems ?? []) > 0 ? 'true' : 'false' }},
            actualizarContadores() {
                setTimeout(() => {
                    const filas = document.querySelectorAll('tbody tr');
                    this.tieneProductos = filas.length > 0;
                }, 100);
            }
        }"
        @producto-agregado.window="actualizarContadores()"
        @producto-eliminado.window="actualizarContadores()"
        @producto-actualizado.window="actualizarContadores()">

        <div class="col-md-12">
            <div class="card-body">
                @if ($salida->estado == 'Entregado')
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Esta salida ya ha sido finalizada.</strong>
                    </div>
                @else
                    {{-- Usar x-show para mejor rendimiento --}}
                    <div x-show="tieneProductos" x-cloak>
                        <div class="row">
                            <div class="col-md-4">
                                <button class="btn btn-success btn-lg btn-block"
                                    onclick="confirmarFinalizacionDesdeLivewire()">
                                    <i class="fas fa-check-circle"></i>
                                    FINALIZAR SALIDA
                                </button>
                            </div>
                            <div class="col-md-8">

                            </div>
                        </div>
                    </div>
                    <div x-show="!tieneProductos" x-cloak>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>No hay productos en el carrito.</strong> Agregue productos para poder finalizar la
                            salida.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@stop

@section('css')
    @livewireStyles
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@stop

@section('js')
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmarFinalizacionDesdeLivewire() {
            Swal.fire({
                title: '¿Finalizar salida?',
                text: 'Una vez finalizada, no podrá modificar los productos. El stock se descontará automáticamente.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Llamar al método finalizarSalida del Livewire component
                    if (typeof Livewire !== 'undefined' && Livewire.first()) {
                        Livewire.first().call('finalizarSalida');
                    }
                }
            });
        }

        // Evento cuando la salida se finaliza correctamente
        document.addEventListener('livewire:init', function() {
            Livewire.on('salida-finalizada-con-nota', (data) => {
                console.log('✅ Salida finalizada correctamente', data);

                Swal.fire({
                    title: '¡Salida finalizada!',
                    html: `
                        <p>Los productos han sido descontados del inventario.</p>
                        <p>¿Deseas ver o descargar la nota de salida?</p>
                        <div style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
                            <a href="${data.notaUrl}" target="_blank" class="btn btn-success" style="padding: 8px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
                                <i class="fas fa-eye"></i> Ver Nota
                            </a>
                            <a href="${data.descargarUrl}" class="btn btn-primary" style="padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    `,
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Ir a Salidas'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('salidas.index') }}';
                    }
                });
            });

            // Evento para otros tipos de alerta
            Livewire.on('mostrar-alerta', (data) => {
                let mensajeData = Array.isArray(data) ? data[0] : data;

                Swal.fire({
                    title: mensajeData?.icono === 'success' ? 'Éxito' : 'Información',
                    text: mensajeData?.mensaje || 'No se recibió mensaje',
                    icon: mensajeData?.icono || 'info',
                    confirmButtonText: 'Aceptar'
                });
            });
        });

        // Función para actualizar contadores manualmente
        function actualizarContadoresManual() {
            const filas = document.querySelectorAll('tbody tr');
            const tieneProductos = filas.length > 0;
            const totalProductos = filas.length;

            const totalElement = document.querySelector('tfoot th:last-child');
            let totalMonto = 0;
            if (totalElement) {
                const totalTexto = totalElement.innerText.replace('$', '').replace(/,/g, '');
                totalMonto = parseFloat(totalTexto) || 0;
            }

            // Disparar evento para Alpine.js
            const event = new CustomEvent('producto-agregado');
            window.dispatchEvent(event);
        }
    </script>
@stop
