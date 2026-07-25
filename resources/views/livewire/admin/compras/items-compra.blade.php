<div>
    {{-- SECCIÓN DE PRODUCTOS SUGERIDOS (STOCK BAJO) --}}
    @if (
        !empty($productos_a_comprar) &&
            is_array($productos_a_comprar) &&
            count($productos_a_comprar) > 0 &&
            $compra->detalles()->count() == 0)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Productos con stock bajo pendientes de reponer
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-success"
                                wire:click="agregarTodosLosProductosSugeridosAlCarrito">
                                <i class="fas fa-cart-plus"></i> Agregar todos al carrito
                                ({{ is_array($productos_a_comprar) ? count($productos_a_comprar) : 0 }})
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-warning">
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Stock Act.</th>
                                        <th>Cant. Sugerida</th>
                                        <th>Lote</th>
                                        <th>F. Vencimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productos_a_comprar as $index => $producto)
                                        @php
                                            $info = $sugerenciasInfo[$producto['id']] ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $producto['codigo'] ?? '' }}</td>
                                            <td>{{ $producto['nombre'] ?? '' }}</td>
                                            <td class="text-center">
                                                @if ($info)
                                                    <span
                                                        class="badge {{ $info['stock_actual'] <= $info['rop'] ? 'badge-danger' : 'badge-success' }}">
                                                        {{ $info['stock_actual'] }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">?</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <input type="number"
                                                    wire:model.live="productos_a_comprar.{{ $index }}.cantidad_sugerida"
                                                    class="form-control form-control-sm text-center" style="width: 80px"
                                                    min="1">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    wire:model.live="productos_a_comprar.{{ $index }}.codigo_lote"
                                                    class="form-control form-control-sm" style="width: 150px">
                                            </td>
                                            <td>
                                                <input type="date"
                                                    wire:model.live="productos_a_comprar.{{ $index }}.fecha_vencimiento"
                                                    class="form-control form-control-sm">
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-success"
                                                        wire:click="agregarProductoSugeridoAlCarrito({{ $index }})"
                                                        title="Agregar al carrito">
                                                        <i class="fas fa-cart-plus"></i>
                                                    </button>
                                                    <button class="btn btn-danger"
                                                        wire:click="eliminarProductoSugerido({{ $index }})"
                                                        title="Eliminar de la lista">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-2 mb-0">
                            <i class="fas fa-info-circle"></i>
                            Estos productos provienen de la alerta de stock bajo. Al agregarlos, se añadirán al carrito
                            temporal.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        {{-- FORMULARIO PARA AGREGAR PRODUCTOS AL CARRITO --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cart-plus"></i> Agregar producto al carrito
                    </h3>
                </div>
                <div class="card-body">
                    {{-- SELECCIONAR PRODUCTO PARA AGREGAR AL CARRITO --}}
                    {{-- @if ($compra->estado != 'Recibido') --}}
                    @if ($compra->estado == 'pendiente')
                        <div class="form-group">
                            <label for="nombre"> Producto <b style="color: red">(*)</b></label>

                            <div class="row">
                                {{-- Reemplaza el div del buscador en la sección de agregar producto --}}
                                <div class="col-md-9" style="position: relative;">
                                    <input type="text" class="form-control" id="buscador-producto"
                                        placeholder="Escriba para buscar producto o haga clic para ver todos..."
                                        onkeyup="filtrarProductos(this.value)" onclick="mostrarTodosProductos()"
                                        onfocus="mostrarTodosProductos()" autocomplete="off">

                                    {{-- Se selecciona un producto y se guarda y se envia su ID --}}
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

                                    {{-- Muestra los resultados filtrados debajo del input --}}
                                    <div id="resultados-busqueda" class="list-group"
                                        style="max-height: 300px; overflow-y: auto; display: none;
                                        position: absolute; z-index: 1000; width: 100%;
                                        background: white; border: 1px solid #ddd; border-radius: 4px;
                                        margin-top: 2px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary btn-block" wire:click="agregarAlCarrito"
                                        @if (!$productoId) disabled @endif>
                                        <i class="fas fa-cart-plus"></i> AGREGAR
                                    </button>
                                </div>
                            </div>

                            @error('productoId')
                                <small style="color: red">{{ $message }}</small>
                            @enderror
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            La compra ya ha sido enviada al proveedor. No se pueden agregar más productos.
                        </div>
                        <a href="{{ route('compras.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Volver a compras
                        </a>
                    @endif
                </div>
            </div>

            {{-- BOTON DE SELECCIONAR SUCURSAL --}}
            {{-- @if (count($carrito) > 0 && $compra->estado != 'Recibido') --}}
            @if (count($carrito) > 0 && $compra->estado == 'enviado al proveedor')
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-info">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="sucursal_id">Sucursal de destino <b
                                                    style="color: red">(*)</b></label>
                                            <select wire:model.live="sucursal_id"
                                                class="form-control @error('sucursal_id') is-invalid @enderror">
                                                <option value="">Seleccione una sucursal</option>
                                                @foreach ($sucursales as $sucursal)
                                                    <option value="{{ $sucursal->id }}">
                                                        {{ $sucursal->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('sucursal_id')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button
                                            class="btn btn-lg btn-block {{ empty($sucursal_id) ? 'btn-danger' : 'btn-success' }}"
                                            wire:click="confirmarYFinalizar"
                                            {{ empty($sucursal_id) ? 'disabled' : '' }}>
                                            <i
                                                class="fas {{ empty($sucursal_id) ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                                            {{ empty($sucursal_id) ? 'Seleccione una sucursal' : 'Finalizar Compra y Recibir Productos' }}
                                        </button>
                                        <small class="text-muted">Al finalizar, los productos se agregarán al
                                            inventario de la sucursal seleccionada.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- CARRITO DE COMPRAS --}}
        <div class="col-md-8">
            <div class="card {{ $compra->estado == 'Recibido' ? 'card-success' : 'card-info' }} card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        @if ($compra->estado == 'Recibido')
                            <i class="fas fa-check-circle"></i> Productos Recibidos
                        @elseif ($compra->detalles()->count() > 0 && $compra->estado != 'Recibido')
                            <i class="fas fa-paper-plane"></i> Productos Enviados al Proveedor
                        @else
                            <i class="fas fa-shopping-cart"></i> Carrito de Compras
                        @endif
                    </h3>

                    {{-- BOTONES DE ENVIAR WHATSAPP-EMAIL-LIMPIAR CARRITO SOLO SI ESTÁ PENDIENTE Y HAY PRODUCTOS EN EL CARRITO --}}
                    <div class="card-tools">
                        @if ($compra->estado == 'pendiente' && count($carrito) > 0)
                            <button type="button" class="btn btn-success btn-sm mr-2"
                                onclick="confirmarEnvioWhatsappPdf({{ $compra->id }})">
                                <i class="fab fa-whatsapp mr-2"></i> Enviar pedido Proveedor Whatsapp
                            </button>
                            <button type="button" class="btn btn-info btn-sm mr-2"
                                onclick="confirmarEnvioCorreo({{ $compra->id }})">
                                <i class="fas fa-envelope"></i> Enviar pedido Proveedor Email
                            </button>
                            <button class="btn btn-danger btn-sm" wire:click="limpiarCarrito">
                                <i class="fas fa-trash"></i> Limpiar Carrito
                            </button>
                        @endif

                        {{-- Botón para descargar PDF del pedido original --}}
                        @if ($compra->estado == 'enviado al proveedor' || $compra->estado == 'Recibido')
                            <a href="{{ route('compras.descargarPdf', $compra->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-download"></i> Descargar PDF Pedido
                            </a>
                        @endif
                    </div>
                </div>

                @if ($compra->estado == 'anulado')
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-ban"></i>
                        <strong>Pedido anulado.</strong> Este pedido fue cancelado porque no quedaron productos
                        pendientes.
                    </div>
                @endif

                <div class="card-body">
                    {{-- MOSTRAR PRODUCTOS RECIBIDOS y REGISTRADOS YA (con lotes) --}}
                    @if ($compra->estado == 'Recibido')
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr style="text-align: center">
                                        <th style="text-align: center">#</th>
                                        <th>Producto</th>
                                        <th>Marca</th>
                                        <th>Lote</th>
                                        <th>F. Vencimiento</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($compra->detalles as $detalle)
                                        <tr>
                                            <td style="text-align: center">{{ $loop->iteration }}</td>
                                            <td>{{ $detalle->producto->nombre }}</td>
                                            <td>{{ $detalle->producto->marca ?? 'Sin marca' }}</td>
                                            <td>{{ $detalle->lote ? $detalle->lote->codigo_lote : 'Pendiente' }}</td>
                                            <td class="text-center">
                                                {{ $detalle->lote && $detalle->lote->fecha_vencimiento ? date('d/m/Y', strtotime($detalle->lote->fecha_vencimiento)) : 'No especificado' }}
                                            </td>
                                            <td style="text-align: center">{{ $detalle->cantidad }}</td>
                                            <td style="text-align: center">
                                                ${{ number_format($detalle->precio_unitario, 2) }}</td>
                                            <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-right">Total:</th>
                                        <th class="text-right">${{ number_format($compra->total, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        {{-- MOSTRAR CARRITO PREVIO A ENVIAR A PROVEEDOR (desde sesión o DB) --}}
                        @if (count($carrito) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover table-sm">
                                    <thead>
                                        <tr style="text-align: center">
                                            <th style="text-align: center">#</th>
                                            <th>Producto</th>
                                            <th>Marca</th>
                                            <th>Lote</th>
                                            <th>F. Vencimiento</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unit.</th>
                                            <th>Subtotal</th>
                                            <th style="text-align: center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($carrito as $index => $item)
                                            <tr>
                                                <td style="text-align: center">{{ $loop->iteration }}</td>
                                                <td>{{ $item['producto_nombre'] }}</td>
                                                <td>
                                                    <input type="text"
                                                        wire:change="actualizarMarcaCarrito('{{ $item['id'] }}', $event.target.value)"
                                                        value="{{ $item['marca'] }}"
                                                        class="form-control form-control-sm" style="width: 120px;">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        wire:model.live="carrito.{{ $index }}.codigo_lote"
                                                        class="form-control form-control-sm" style="width: 150px;">
                                                </td>
                                                <td style="text-align: center; width: 150px;">
                                                    <div class="input-group input-group-sm">
                                                        <input type="date"
                                                            wire:model.live="carrito.{{ $index }}.fecha_vencimiento"
                                                            class="form-control form-control-sm"
                                                            style="width: 130px;">
                                                        <button class="btn btn-sm btn-outline-secondary"
                                                            type="button"
                                                            wire:click="limpiarFechaVencimiento('{{ $item['id'] }}')"
                                                            title="Eliminar fecha">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td style="text-align: center; width: 100px;">
                                                    <input type="number"
                                                        wire:change="actualizarCantidadCarrito('{{ $item['id'] }}', $event.target.value)"
                                                        value="{{ $item['cantidad'] }}"
                                                        class="form-control form-control-sm text-center"
                                                        min="1" style="width: 80px;">
                                                </td>
                                                <td style="text-align: center; width: 120px;">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number"
                                                            wire:change="actualizarPrecioUnitario('{{ $item['id'] }}', $event.target.value)"
                                                            value="{{ $item['precio_unitario'] }}"
                                                            class="form-control form-control-sm" step="0.01"
                                                            min="0.01" style="width: 80px;">
                                                    </div>
                                                </td>
                                                <td class="text-right">${{ number_format($item['subtotal'], 2) }}</td>
                                                <td style="text-align: center">

                                                    <button class="btn btn-danger btn-sm"
                                                        wire:click="eliminarDelCarrito('{{ $item['id'] }}')"
                                                        title="Eliminar del carrito">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="8" class="text-right">Total:</th>
                                            <th class="text-right">${{ number_format($totalCompra, 2) }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                El carrito está vacío. Agregue productos usando el formulario de la izquierda.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR PRODUCTO DEL CARRITO --}}

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

            }, 200);
        }

        function mostrarListaProductos(productos, textoResaltar = '') {
            const resultados = document.getElementById('resultados-busqueda');

            if (!resultados) return;

            if (productos.length === 0) {
                resultados.innerHTML =
                    '<div class="list-group-item text-muted" style="padding: 8px 12px;">No se encontraron productos</div>';
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
                        <small class="text-muted d-block">Código: ${prod.codigo} | Marca: ${prod.marca}</small>
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

            Livewire.dispatch('set-producto-id', {
                id: id
            });

            setTimeout(() => {
                if (typeof Livewire !== 'undefined' && Livewire.first) {
                    Livewire.first().set('productoId', id);
                }
            }, 50);
        }

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

                if (typeof Livewire !== 'undefined' && Livewire.first) {
                    Livewire.first().set('productoId', null);
                }
            });

            // RECIBE DATOS DE ItemsCompra.php DESDE confirmarYFinalizar() PARA SELECCIONAR SUCURSAL
            Livewire.on('mostrar-confirmacion-finalizar', (data) => {
                Swal.fire({
                    title: '¿Finalizar compra?',
                    html: `
                        <div style="text-align: left">
                            <p><strong>Total:</strong> $${data.total.toFixed(2)}</p>
                            <p><strong>Productos:</strong> ${data.cantidad}</p>
                            <p><strong>Sucursal:</strong> ${data.sucursal_id}</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, finalizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('procesar-finalizacion', {
                            sucursal_id: data.sucursal_id
                        });
                        Swal.fire({
                            title: 'Procesando...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    }
                });
            });

            Livewire.on('compra-finalizada-con-nota', (data) => {
                Swal.fire({
                    title: '¡Compra finalizada!',
                    html: `
                        <p>Los productos han sido agregados al inventario.</p>
                        <p>¿Deseas ver o descargar la nota de compra?</p>
                        <div style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
                            <a href="${data.notaUrl}" target="_blank" class="btn btn-success" style="padding: 8px 15px;">Ver Nota</a>
                            <a href="${data.descargarUrl}" class="btn btn-primary" style="padding: 8px 15px;">Descargar</a>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Ir a Compras'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('compras.index') }}';
                    }
                });
            });
        });

        document.addEventListener('click', function(e) {
            const buscador = document.getElementById('buscador-producto');
            const resultados = document.getElementById('resultados-busqueda');
            if (!buscador || !resultados) return;
            if (!e.target.closest('#buscador-producto') && !e.target.closest('#resultados-busqueda')) {
                resultados.style.display = 'none';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const resultados = document.getElementById('resultados-busqueda');
                if (resultados) resultados.style.display = 'none';
            }
        });

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
                        didOpen: () => Swal.showLoading()
                    });
                    window.location.href = '{{ route('compras.enviarCorreo', ':id') }}'.replace(':id', compraId);
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
                        didOpen: () => Swal.showLoading()
                    });

                    fetch('{{ route('compras.enviarWhatsappPdf', ':id') }}'.replace(':id', compraId), {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.close();
                            if (data.success) {
                                window.open(data.url, '_blank');
                                Swal.fire({
                                    title: '¡PDF Generado!',
                                    html: `<a href="${data.pdf_url}" target="_blank" class="btn btn-success">📥 DESCARGAR PDF</a>`,
                                    icon: 'success'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.message,
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire({
                                title: 'Error',
                                text: error.message,
                                icon: 'error'
                            });
                        });
                }
            });
        }
    </script>
@endpush
