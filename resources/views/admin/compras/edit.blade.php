@extends('adminlte::page')

@section('title', 'Pedido Producto')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compra nro {{ $compra->id }}</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    {{-- CARD-BODY CON LOS DATOS DE LA COMPRA CREADA --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>Paso 1 | Compra creada</b></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="proveedor_id">Proveedores</label>
                                <p>{{ $compra->proveedor->nombre }}</p>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="fecha">Fecha de la compra</label>
                                <p>{{ $compra->fecha }}</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <p>{{ $compra->observaciones }}</p>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="estado">Estado de la compra</label>
                                <p>
                                    @if ($compra->estado == 'Recibido')
                                        <span class="badge badge-success">RECIBIDO</span>
                                    @elseif($compra->detalles->count() > 0)
                                        <span class="badge badge-warning">Productos guardados</span>
                                    @else
                                        <span class="badge badge-info">{{ $compra->estado }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- COMPONENTE DE LIVEwIRE (MANEJA TODO EL PASO 2) --}}
    <div class="row">
        <div class="col-md-12">
            {{-- maneja detallecompra como detalles --}}
            <div class="card {{ $compra->detalles->count() > 0 ? 'card-success' : 'card-primary' }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <b>
                            @if ($compra->detalles->count() > 0)
                                <i class="fas fa-check-circle"></i> Paso 2 | Compra Por Finalizar
                            @else
                                <i class="fas fa-shopping-cart"></i> Paso 2 | Carrito de Compras
                            @endif
                        </b>
                    </h3>
                    @if ($compra->detalles->count() > 0)
                        <div class="card-tools">
                            <span class="badge badge-success">
                                {{ $compra->detalles->count() }} productos recibidos
                            </span>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <livewire:admin.compras.items-compra
                        :compra="$compra"
                        :wire:key="'items-'.$compra->id"
                        {{-- se dirige directo a cargar a mount de ItemsCompra --}}
                        :productos_sugeridos="request('productos', '')"
                    />
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .select2-container .select2-selection--single {
            height: 40px !important;
        }
    </style>
    @livewireStyles
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts

    <script>
        document.addEventListener('livewire:init', function() {
            console.log('✅ Livewire inicializado');

            // 🟢 NUEVO LISTENER PARA ALERTAS DE STOCK MÁXIMO
            // 🟢 LISTENER PARA ALERTAS DE STOCK MÁXIMO
            Livewire.on('mostrar-alerta-stock', (data) => {
                console.log('📢 Alerta de stock máximo RECIBIDA:', data);
                console.log('🔍 Tipo de data:', typeof data);
                console.log('🔍 ¿Es array?', Array.isArray(data));

                //necesitamos acceder al primer elemento de data
                let mensajeData = Array.isArray(data) ? data[0] : data;

                console.log('🔍 mensajeData:', mensajeData);
                console.log('🔍 mensajeData.mensaje:', mensajeData?.mensaje);
                console.log('🔍 mensajeData.icono:', mensajeData?.icono);

                let titulo = '⚠️ Advertencia de Stock';
                if (mensajeData?.icono === 'error') titulo = '❌ Error de Stock';
                else if (mensajeData?.icono === 'success') titulo = '✅ Éxito';
                else if (mensajeData?.icono === 'info') titulo = 'ℹ️ Información';

                let mensaje = mensajeData?.mensaje || 'No se recibió mensaje';

                Swal.fire({
                    title: titulo,
                    html: mensaje,
                    icon: mensajeData?.icono || 'warning',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#d33'
                });
            });

            //LISTENER PARA OTRAS ALERTAS DEL SISTEMA
            Livewire.on('mostrar-alerta', (data) => {
                console.log('Alerta general recibida:', data);

                let mensajeData = Array.isArray(data) ? data[0] : data;

                Swal.fire({
                    title: 'Notificación',
                    text: mensajeData?.mensaje || 'No se recibió mensaje',
                    icon: mensajeData?.icono || 'info',
                    confirmButtonText: 'Aceptar'
                });
            });

            // Evento para mostrar confirmación de finalización
            Livewire.on('mostrar-confirmacion-finalizar', function(data) {
                console.log('✅ Evento mostrar-confirmacion-finalizar recibido:', data);

                let total = parseFloat(data.total) || 0;
                let sucursal_id = data.sucursal_id;
                let cantidad = parseInt(data.cantidad) || 0;

                Swal.fire({
                    title: '¿Finalizar compra?',
                    html: `Se guardarán todos los productos y se agregarán al inventario de la sucursal seleccionada.<br>
                       <strong>Total: $${total.toFixed(2)}</strong><br>
                       <strong>Productos: ${cantidad}</strong>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, finalizar compra',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Procesando...',
                            text: 'Guardando productos y actualizando inventario',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        Livewire.dispatch('procesar-finalizacion', {
                            sucursal_id: sucursal_id
                        });
                    }
                });
            });

            // Evento para cuando la compra se finaliza correctamente con nota
            Livewire.on('compra-finalizada-con-nota', (data) => {
                console.log('✅ Compra finalizada correctamente', data);

                Swal.fire({
                    title: '¡Compra finalizada!',
                    html: `
                        <p>Los productos han sido agregados al inventario.</p>
                        <p>¿Deseas ver o descargar la nota de compra?</p>
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
                    confirmButtonText: 'Ir a Compras'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('compras.index') }}';
                    }
                });
            });

            // Evento para cuando la compra se finaliza correctamente (simple)
            Livewire.on('compra-finalizada', function(data) {
                console.log('✅ Evento compra-finalizada recibido:', data);
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Compra finalizada exitosamente.',
                    showConfirmButton: true,
                    confirmButtonText: 'Ver compras'
                }).then(() => {
                    window.location.href = '{{ route('compras.index') }}';
                });
            });

            // Evento para errores
            Livewire.on('error-procesando', function(data) {
                console.log('❌ Error:', data);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje || 'Ocurrió un error al procesar la compra',
                    confirmButtonText: 'Aceptar'
                });
            });
        });

        // Función para enviar correo
        function confirmarEnvioCorreo(compraId) {
            Swal.fire({
                title: '¿Enviar pedido al proveedor?',
                text: 'Se enviará un correo con el detalle del carrito actual',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Enviando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    window.location.href = '{{ route('compras.enviarCorreo', $compra->id) }}';
                }
            });
        }

        function confirmarEnvioWhatsappPdf(compraId) {
            Swal.fire({
                title: '¿Enviar pedido por Whatsapp?',
                text: 'Se generará un PDF y lo prepararemos para enviar',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'green',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar PDF',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Generando PDF...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    let url = "{{ route('compras.enviarWhatsappPdf', ':id') }}";
                    url = url.replace(':id', compraId);

                    fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    throw new Error(text);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.close();
                            if (data.success) {
                                window.open(data.url, '_blank');
                                Swal.fire({
                                    title: '¡PDF Generado!',
                                    html: `
                            <p>✅ WhatsApp se abrirá en una nueva pestaña</p>
                            <p>📎 <strong>Para adjuntar el PDF:</strong></p>
                            <ol style="text-align: left;">
                                <li>Escribe el mensaje en WhatsApp</li>
                                <li>Haz clic en el ícono 📎 (adjuntar)</li>
                                <li>Selecciona "Documento"</li>
                                <li>Descarga y adjunta este PDF:</li>
                            </ol>
                            <a href="${data.pdf_url}" target="_blank" class="btn btn-success" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;" id="btnDescargarPDF">
                                📥 DESCARGAR PDF
                            </a>
                        `,
                                    icon: 'success',
                                    showConfirmButton: true,
                                    confirmButtonText: 'Entendido'
                                }).then(() => {
                                    // Recargar la página después de cerrar el SweetAlert
                                    location.reload();
                                });

                                // Agregar evento al botón de descarga después de que se renderice el DOM
                                setTimeout(() => {
                                    const btnDescargar = document.getElementById('btnDescargarPDF');
                                    if (btnDescargar) {
                                        btnDescargar.addEventListener('click', function(e) {
                                            // Recargar la página después de un breve tiempo para permitir la descarga
                                            setTimeout(() => {
                                                location.reload();
                                            }, 1000);
                                        });
                                    }
                                }, 100);
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.message || 'No se pudo preparar el mensaje',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire({
                                title: 'Error',
                                text: error.message || 'Error al conectar con el servidor',
                                icon: 'error'
                            });
                        });
                }
            });
        }
    </script>
@stop
