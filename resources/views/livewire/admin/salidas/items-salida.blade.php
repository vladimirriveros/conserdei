<div>
    <div class="row">
        {{-- FORMULARIO AGREGAR PRODUCTO --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-minus-circle"></i> Agregar producto a la salida
                    </h3>
                </div>
                <div class="card-body">
                    @if ($salida->estado == 'Pendiente')
                        {{-- SELECCIONAR PRODUCTO CON BUSCADOR MEJORADO --}}
                        <div class="form-group">
                            <label for="nombre"> Producto <b style="color: red">(*)</b></label>

                            <div class="row">
                                {{-- Reemplaza el div del buscador (líneas 15-45 aproximadamente) --}}
                                <div class="col-md-9" style="position: relative;">
                                    <input type="text" class="form-control" id="buscador-producto"
                                        placeholder="Escriba para buscar producto o haga clic para ver todos..."
                                        onkeyup="filtrarProductos(this.value)" onclick="mostrarTodosProductos()"
                                        onfocus="mostrarTodosProductos()" autocomplete="off"
                                        value="{{ $productoSeleccionadoNombre ?? '' }}">

                                    <select wire:model.live="productoId" class="form-control" required
                                        id="producto-select" style="display: none;">
                                        <option value="">Seleccione un producto</option>
                                        @foreach ($productos as $producto)
                                            <option value="{{ $producto->id }}"
                                                data-nombre="{{ $producto->codigo }} {{ $producto->nombre }} {{ $producto->marca ?? '' }}"
                                                data-codigo="{{ $producto->codigo }}"
                                                data-marca="{{ $producto->marca ?? 'Sin marca' }}">
                                                {{ $producto->codigo . ' - ' . $producto->nombre }}
                                                ({{ $producto->marca ?? 'Sin marca' }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <div id="resultados-busqueda" class="list-group"
                                        style="max-height: 300px; overflow-y: auto; display: none; position: absolute; z-index: 1000; width: 100%; background: white; border: 1px solid #ddd; border-radius: 4px; margin-top: 2px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary btn-block" wire:click="agregarItems"
                                        @if (
                                            !$productoId ||
                                                ($salida->motivo != 'Venta' && !$loteSeleccionado) ||
                                                ($salida->motivo == 'Venta' && empty($lotesDisponibles))) disabled @endif>
                                        <i class="fas fa-cart-plus"></i> AGREGAR
                                    </button>
                                </div>
                            </div>

                            @error('productoId')
                                <small style="color: red">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- SELECCIONAR LOTE (si hay producto seleccionado) --}}
                        @if ($salida->motivo != 'Venta')
                            @if ($productoId)
                                <div class="form-group">
                                    <label>Lote disponible <b style="color:red">(*)</b></label>
                                    <select wire:model.live="loteSeleccionado" class="form-control">
                                        <option value="">Seleccione lote</option>
                                        @foreach ($lotesDisponibles as $lote)
                                            <option value="{{ $lote->lote_id }}">
                                                {{ $lote->codigo_lote }} -
                                                Vence:
                                                {{ $lote->fecha_vencimiento ? date('d/m/Y', strtotime($lote->fecha_vencimiento)) : 'N/A' }}
                                                -
                                                <strong>Disp: {{ $lote->stock_disponible }}</strong>
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('loteSeleccionado')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            @endif
                        @else
                            {{-- Para Venta, mostrar información de lotes disponibles --}}
                            @if ($productoId && count($lotesDisponibles) > 0)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Modo FIFO activado:</strong> Los productos se tomarán automáticamente de los
                                    lotes más antiguos.
                                    <br>
                                    <small>Lotes disponibles:
                                        @foreach ($lotesDisponibles as $lote)
                                            <span class="badge badge-info">{{ $lote->codigo_lote }}
                                                ({{ $lote->stock_disponible }} unid.)
                                            </span>
                                        @endforeach
                                    </small>
                                </div>
                            @endif
                        @endif

                        {{-- CANTIDAD --}}
                        <div class="form-group">
                            <label>Cantidad <b style="color:red">(*)</b></label>
                            <input type="number" wire:model="cantidad" class="form-control" min="1"
                                max="{{ $loteSeleccionado ? collect($lotesDisponibles)->firstWhere('lote_id', $loteSeleccionado)?->stock_disponible ?? 999 : 999 }}"
                                step="1">
                            @error('cantidad')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            @if ($loteSeleccionado && $lotesDisponibles->isNotEmpty())
                                @php
                                    $loteActual = collect($lotesDisponibles)->firstWhere('lote_id', $loteSeleccionado);
                                @endphp
                                @if ($loteActual)
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Stock disponible en este lote:
                                        <strong>{{ $loteActual->stock_disponible }}</strong> unidades
                                    </small>
                                    @if ($cantidad > $loteActual->stock_disponible)
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            La cantidad ingresada ({{ $cantidad }}) excede el stock disponible
                                            ({{ $loteActual->stock_disponible }})
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Esta salida no está pendiente. Estado: {{ $salida->estado }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- LISTA DE PRODUCTOS DEL CARRITO (desde SESIÓN) --}}
        <div class="col-md-8">
            <div class="card {{ count($carritoItems) > 0 ? 'card-success' : 'card-info' }} card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        @if (count($carritoItems) > 0)
                            <i class="fas fa-check-circle"></i> Productos en salida ({{ count($carritoItems) }})
                        @else
                            <i class="fas fa-shopping-cart"></i> Carrito de salida
                        @endif
                    </h3>
                    @if (count($carritoItems) > 0 && $salida->estado == 'Pendiente')
                        <div class="card-tools">
                            <button class="btn btn-danger btn-sm" onclick="confirmarVaciarCarrito()">
                                <i class="fas fa-trash-alt"></i> Vaciar Carrito
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if (count($carritoItems) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-primary text-white">
                                    <tr class="text-center">
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th>Lote</th>
                                        <th>F. Vencimiento</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($carritoItems as $index => $item)
                                        @php
                                            $maxData = $maximosPermitidos[$item['id']] ?? [
                                                'max_permitido' => 999,
                                                'stock_lote' => 0,
                                            ];
                                            $maxPermitido = $maxData['max_permitido'];
                                            $stockLote = $maxData['stock_lote'];
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item['producto_nombre'] }}</td>
                                            <td>
                                                {{ $item['codigo_lote'] }}
                                                @if ($stockLote > 0)
                                                    <small class="badge badge-secondary">Stock:
                                                        {{ $stockLote }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $item['fecha_vencimiento'] ? date('d/m/Y', strtotime($item['fecha_vencimiento'])) : 'N/A' }}
                                            </td>
                                            <td class="text-center" style="width: 100px;">
                                                @if ($salida->estado == 'Pendiente')
                                                    <input type="number" value="{{ $item['cantidad'] }}"
                                                        wire:change="actualizarCantidadItem('{{ $item['id'] }}', $event.target.value)"
                                                        class="form-control form-control-sm text-center" min="1"
                                                        max="{{ $maxPermitido }}" style="width: 80px;">
                                                    @if ($item['cantidad'] > $maxPermitido)
                                                        <div class="text-danger small mt-1">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Máx: {{ $maxPermitido }}
                                                        </div>
                                                    @endif
                                                @else
                                                    {{ $item['cantidad'] }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                ${{ number_format($item['precio_unitario'], 2) }}
                                            </td>
                                            <td class="text-center">${{ number_format($item['subtotal'], 2) }}</td>
                                            <td class="text-center">
                                                @if ($salida->estado == 'Pendiente')
                                                    <button class="btn btn-danger btn-sm"
                                                        wire:click="borrarItem('{{ $item['id'] }}')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="6" class="text-right">TOTAL:</th>
                                        <th class="text-center">${{ number_format($totalCarrito, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No hay productos en esta salida. Use el formulario de la izquierda para agregar.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Advertencia si producto ya está en carrito --}}
    @if ($productoId && collect($carritoItems)->contains('producto_id', $productoId))
        <div class="alert alert-warning mt-2 p-2">
            <small>
                <i class="fas fa-exclamation-triangle"></i>
                Este producto ya está en el carrito. Use la tabla para ajustar cantidades.
            </small>
        </div>
    @endif
</div>

{{-- SCRIPTS PARA EL BUSCADOR --}}
@push('js')
    <script>
        let timeoutBusqueda = null;
        let productoSeleccionadoNombre = '';
        let todosLosProductos = []; // Cache de productos

        // Inicializar caché de productos cuando el DOM está listo
        document.addEventListener('DOMContentLoaded', function() {
            cargarCacheProductos();
        });

        // También recargar después de que Livewire actualice
        document.addEventListener('livewire:init', function() {
            cargarCacheProductos();
        });

        function cargarCacheProductos() {
            const select = document.getElementById('producto-select');
            if (!select) return;

            const opciones = Array.from(select.options).slice(1);
            todosLosProductos = opciones.map(opt => ({
                value: opt.value,
                text: opt.text,
                nombreCompleto: opt.getAttribute('data-nombre') || opt.text,
                codigo: opt.getAttribute('data-codigo') || '',
                marca: opt.getAttribute('data-marca') || ''
            }));
        }

        // Mostrar todos los productos (sin filtro)
        function mostrarTodosProductos() {
            const resultados = document.getElementById('resultados-busqueda');
            const buscador = document.getElementById('buscador-producto');

            if (!resultados || !buscador) return;

            // Si ya hay un producto seleccionado y el buscador tiene valor, no mostrar todos
            if (buscador.value && buscador.value.length > 0) {
                return;
            }

            mostrarListaProductos(todosLosProductos);
        }

        function filtrarProductos(texto) {
            if (timeoutBusqueda) {
                clearTimeout(timeoutBusqueda);
            }

            timeoutBusqueda = setTimeout(() => {
                const resultados = document.getElementById('resultados-busqueda');

                if (!resultados) return;

                // Si no hay texto, mostrar todos los productos
                if (!texto || texto.length === 0) {
                    mostrarListaProductos(todosLosProductos);
                    return;
                }

                texto = texto.toLowerCase().trim();

                // Si el texto es muy corto (1 carácter), mostrar todos pero resaltar coincidencias
                if (texto.length === 1) {
                    const filtrados = todosLosProductos.filter(prod =>
                        prod.nombreCompleto.toLowerCase().includes(texto)
                    );
                    mostrarListaProductos(filtrados, texto);
                    return;
                }

                // Búsqueda normal con mínimo 2 caracteres
                if (texto.length < 2) {
                    mostrarListaProductos(todosLosProductos);
                    return;
                }

                const filtradas = todosLosProductos.filter(prod =>
                    prod.nombreCompleto.toLowerCase().includes(texto)
                );

                mostrarListaProductos(filtradas, texto);

            }, 200); // Reducido de 300 a 200ms para mejor respuesta
        }

        function mostrarListaProductos(productos, textoResaltar = '') {
            const resultados = document.getElementById('resultados-busqueda');

            if (!resultados) return;

            if (productos.length === 0) {
                resultados.innerHTML =
                    '<div class="list-group-item text-muted" style="padding: 8px 12px;">No se encontraron productos en esta sucursal</div>';
                resultados.style.display = 'block';
                return;
            }

            resultados.innerHTML = productos.map(prod => {
                let textoMostrar = prod.text;

                if (textoResaltar && textoResaltar.length > 0) {
                    const regex = new RegExp(`(${textoResaltar.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    textoMostrar = textoMostrar.replace(regex,
                        '<strong style="background-color: #ffc107; color: #000;">$1</strong>');
                }

                return `<a href="#" class="list-group-item list-group-item-action"
                       onclick="seleccionarProducto('${prod.value}', '${prod.text.replace(/'/g, "\\'")}'); return false;"
                       style="padding: 8px 12px; border-bottom: 1px solid #eee; cursor: pointer;">
                        ${textoMostrar}
                        <small class="text-muted d-block">Código: ${prod.codigo}</small>
                    </a>`;
            }).join('');

            resultados.style.display = 'block';
        }

        function seleccionarProducto(id, nombre) {
            const select = document.getElementById('producto-select');
            const buscador = document.getElementById('buscador-producto');
            const resultados = document.getElementById('resultados-busqueda');

            if (!select || !buscador) return;

            select.value = id;
            productoSeleccionadoNombre = nombre;
            buscador.value = nombre;
            resultados.style.display = 'none';

            // Disparar evento para Livewire
            Livewire.dispatch('set-producto-id', {
                id: id
            });

            setTimeout(() => {
                if (typeof Livewire !== 'undefined' && Livewire.first()) {
                    Livewire.first().set('productoId', id);
                }
            }, 50);
        }

        function confirmarVaciarCarrito() {
            Swal.fire({
                title: '¿Vaciar todo el carrito?',
                text: 'Esta acción eliminará todos los productos de la salida. ¿Estás seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, vaciar todo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof Livewire !== 'undefined' && Livewire.first()) {
                        Livewire.first().call('vaciarCarrito');
                    }
                }
            });
        }

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            const buscador = document.getElementById('buscador-producto');
            const resultados = document.getElementById('resultados-busqueda');

            if (!buscador || !resultados) return;

            if (!e.target.closest('#buscador-producto') && !e.target.closest('#resultados-busqueda')) {
                resultados.style.display = 'none';
            }
        });

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const resultados = document.getElementById('resultados-busqueda');
                if (resultados) {
                    resultados.style.display = 'none';
                }
            }
        });

        // Livewire event listeners
        document.addEventListener('livewire:init', function() {
            Livewire.on('producto-agregado', function() {
                const buscador = document.getElementById('buscador-producto');
                const productoSelect = document.getElementById('producto-select');

                if (buscador) {
                    buscador.value = '';
                    buscador.placeholder = 'Escriba para buscar producto o haga clic para ver todos...';
                }

                if (productoSelect) {
                    productoSelect.value = '';
                }

                productoSeleccionadoNombre = '';

                // Recargar caché de productos por si cambió
                setTimeout(() => cargarCacheProductos(), 100);

                if (typeof Livewire !== 'undefined' && Livewire.first()) {
                    Livewire.first().set('productoId', null);
                }
            });

            Livewire.on('limpiar-buscador', function() {
                const buscador = document.getElementById('buscador-producto');
                const productoSelect = document.getElementById('producto-select');

                if (buscador) {
                    buscador.value = '';
                }

                if (productoSelect) {
                    productoSelect.value = '';
                }

                productoSeleccionadoNombre = '';
            });

            // Recargar caché después de cualquier actualización de Livewire
            Livewire.on('reload-cache', function() {
                setTimeout(() => cargarCacheProductos(), 100);
            });
        });
    </script>
@endpush
