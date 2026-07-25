@extends('adminlte::page')

@section('title', 'Lotes Salida Vencidos')

@section('content_header')
    <h3>
        ⚠ Productos Vencidos - Sucursal:
        <span class="badge badge-info">
            {{ $sucursal->nombre }}
        </span>
    </h3>
    <hr>
@stop

@section('content')

    {{-- CARD 1: PRODUCTOS VENCIDOS DISPONIBLES (CON BOTÓN AGREGAR y AGREGAR TODOS) --}}
    <div class="card card-outline card-danger">
        <div class="card-header bg-danger">
            <h3 class="card-title">
                <b><i class="fas fa-box"></i> Inventario vencido en sucursal</b>
            </h3>
            <div class="card-tools">
                @if ($productos_vencidos_transformados->count())
                    {{-- BOTÓN AGREGAR TODOS --}}
                    <form action="{{ route('lotes.vencidos.agregar-todos') }}" method="POST"
                        style="display: inline-block; margin-right: 10px;"
                        onsubmit="event.preventDefault(); confirmarAgregarTodos(this);">
                        @csrf
                        <input type="hidden" name="session_key" value="{{ $sessionKey }}">
                        <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i class="fas fa-cart-plus"></i> Agregar Todos
                            ({{ $productos_vencidos_transformados->count() }})
                        </button>
                    </form>
                @endif
                <span class="badge badge-light">
                    Total: {{ $productos_vencidos_transformados->count() }} productos
                </span>
            </div>
        </div>
        <div class="card-body">
            @if ($productos_vencidos_transformados->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>F. Vencimiento</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos_vencidos_transformados as $item)
                                <tr class="table-danger">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->codigo_producto }}</td>
                                    <td>{{ $item->producto }}</td>
                                    <td>{{ $item->codigo_lote }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-warning">{{ $item->cantidad }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">Vencido</span>
                                    </td>
                                    <td class="text-center">
                                        {{-- BOTÓN AGREGAR INDIVIDUAL --}}
                                        <form action="{{ route('lotes.vencidos.agregar') }}" method="POST"
                                            style="display: inline-block;"
                                            onsubmit="event.preventDefault(); confirmarAgregar(this, {{ $item->cantidad }});">
                                            @csrf
                                            <input type="hidden" name="session_key" value="{{ $sessionKey }}">
                                            <input type="hidden" name="lote_id" value="{{ $item->lote_id }}">
                                            <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                                            <input type="hidden" name="cantidad" value="{{ $item->cantidad }}">
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-cart-plus"></i> Agregar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle"></i>
                    Los productos que están en esta tabla <strong>NO ESTÁN en la salida pendiente</strong>.
                    Usa el botón <span class="badge badge-warning">Agregar</span> para incluirlos individualmente o
                    <span class="badge badge-warning">Agregar Todos</span> para incluirlos de una sola vez.
                </div>
            @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    No hay productos vencidos pendientes de agregar en esta sucursal.
                </div>
            @endif
        </div>
    </div>

    {{-- CARD 2: SALIDA PENDIENTE / CARRITO --}}
    <div class="card card-outline card-primary">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <b><i class="fas fa-shopping-cart"></i> Salida pendiente por caducidad</b>
            </h3>
            <div class="card-tools">
                @if ($detalles_carrito->count())
                    {{-- BOTÓN VACIAR CARRITO (OPCIONAL) --}}
                    <form action="{{ route('lotes.vencidos.vaciar-carrito') }}" method="POST"
                        style="display: inline-block; margin-right: 10px;"
                        onsubmit="event.preventDefault(); confirmarVaciarCarrito(this);">
                        @csrf
                        <input type="hidden" name="session_key" value="{{ $sessionKey }}">
                        <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash-alt"></i> Vaciar Carrito
                        </button>
                    </form>
                @endif
                <span class="badge badge-light">Carrito</span>
                <span class="badge badge-warning">{{ $detalles_carrito->count() }} items</span>
            </div>
        </div>
        <div class="card-body">
            @if ($detalles_carrito->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>F. Vencimiento</th>
                                <th>Cantidad</th>
                                <th>Precio Compra</th>
                                <th>Pérdida Total</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detalles_carrito as $detalle)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detalle->producto }}</td>
                                    <td>{{ $detalle->codigo_lote }}</td>
                                    <td>{{ \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ $detalle->cantidad }}</td>
                                    <td class="text-right">S/ {{ number_format($detalle->precio_compra, 2) }}</td>
                                    <td class="text-right text-danger">S/ {{ number_format($detalle->perdida, 2) }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('lotes.vencidos.eliminar', $detalle->lote_id) }}"
                                            method="POST" onsubmit="event.preventDefault(); confirmarEliminar(this);">
                                            @csrf
                                            <input type="hidden" name="session_key" value="{{ $sessionKey }}">
                                            <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="6" class="text-right">PÉRDIDA TOTAL:</th>
                                <th class="text-right text-danger font-weight-bold">
                                    S/ {{ number_format($total_perdida, 2) }}
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- BOTÓN PARA FINALIZAR SALIDA --}}
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="{{ route('lotes.vencidos.finalizar') }}" method="POST"
                                onsubmit="event.preventDefault(); confirmarFinalizar(this);">
                                @csrf
                                <input type="hidden" name="session_key" value="{{ $sessionKey }}">
                                <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                                <button type="submit" class="btn btn-danger btn-lg btn-block">
                                    <i class="fas fa-trash-alt"></i>
                                    Dar de baja productos (Pérdida: S/ {{ number_format($total_perdida, 2) }})
                                </button>
                            </form>
                        </div>
                        <div class="col-md-8">

                        </div>
                    </div>


                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay productos en la salida pendiente.
                </div>
            @endif
        </div>
    </div>

    {{-- ALERTAS CON SWEETALERT2 --}}
    @if (session('mensaje'))
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: '{{ session('icono') }}',
                title: '{{ session('mensaje') }}',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif

    <div class="mt-3">
        <button type="button" class="btn btn-secondary" onclick="history.back()">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </div>

@stop

@section('css')
    <style>
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }

        .btn-block {
            margin-top: 10px;
        }

        .badge {
            font-size: 11pt;
        }
    </style>
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Función para confirmar AGREGAR producto individual
        function confirmarAgregar(form, cantidad) {
            Swal.fire({
                title: '¿Agregar producto?',
                text: `¿Agregar ${cantidad} unidades a la salida?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, agregar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Función para confirmar AGREGAR TODOS los productos
        function confirmarAgregarTodos(form) {
            Swal.fire({
                title: '¿Agregar todos los productos?',
                text: `Se agregarán {{ $productos_vencidos_transformados->count() }} productos al carrito. ¿Estás seguro?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, agregar todos',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Función para confirmar ELIMINAR producto
        function confirmarEliminar(form) {
            Swal.fire({
                title: '¿Eliminar producto?',
                text: '¿Estás seguro de eliminar este producto de la salida?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Función para confirmar VACIAR CARRITO
        function confirmarVaciarCarrito(form) {
            Swal.fire({
                title: '¿Vaciar carrito?',
                text: 'Se eliminarán todos los productos del carrito. ¿Estás seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, vaciar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Función para confirmar FINALIZAR salida
        function confirmarFinalizar(form) {
            Swal.fire({
                title: '¿Finalizar salida?',
                text: 'Se descontarán los productos del inventario. ¿Estás seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    form.submit();
                }
            });
        }
    </script>

    {{-- Mantén las alertas de sesión que ya tienes --}}
    @if (session('mensaje'))
        <script>
            Swal.fire({
                icon: '{{ session('icono') }}',
                title: '{{ session('mensaje') }}',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif
@stop
