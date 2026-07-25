<?php

namespace App\Livewire\Admin\Compras;

use App\Models\Compra;
use App\Models\HistorialPrecio;
use App\Models\InventarioSucuralLote;
use App\Services\InventarioService;  // 👈 AGREGAR
use App\Models\Producto;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Sucursal;
use App\Models\TipoCambio;
use App\Models\HistorialPrecioVenta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ItemsCompra extends Component
{
    public $compra;         //recibe la compra desde el edit.blade.php Objeto Compra con su relación detalles ya cargada
    public $productoId;
    public $cantidad = 1;
    public $precioCompra;
    public $fechaVencimiento;
    public $codigoLote;
    public $productos;
    public $totalCompra = 0;

    // Propiedad para la sucursal
    public $sucursal_id;
    public $sucursales;

    // Carrito temporal
    public $carrito = [];

    // Productos sugeridos
    public $sugeridos = [];
    public $sugerenciasInfo = [];

    // Productos sugeridos
    public $productos_a_comprar = [];
    public $productos_sugeridos = '';  //productos sugeridos desde el controlador (stock bajo) o desde la URL (query string)

    // Modal de edición
    public $mostrarModalEdicion = false;
    public $itemEditarIndex = null;

    protected $inventarioService;

    //RECIBE LOS JS DE
    protected $listeners = [
        'procesar-finalizacion' => 'procesarFinalizacion',//recibe la confirmacion de JS de mostrar-confirmacion-finalizar
        'confirmarYFinalizar' => 'confirmarYFinalizar',
    ];
    /**
     * Reglas de validación para agregar productos al carrito
     */
    protected function rulesParaAgregar()
    {
        return [
            'productoId' => 'required',
            'cantidad' => 'required|numeric|min:1',
            'precioCompra' => 'required|numeric|min:0.01',
            'codigoLote' => 'required',
            'fechaVencimiento' => 'nullable|date',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    protected $messages = [
        'productoId.required' => 'Debe seleccionar un producto.',
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.min' => 'La cantidad debe ser al menos 1.',
        'precioCompra.required' => 'El precio de compra es obligatorio.',
        'precioCompra.min' => 'El precio debe ser mayor a 0.',
        'codigoLote.required' => 'El código de lote es obligatorio.',
        'fechaVencimiento.date' => 'La fecha de vencimiento no es válida.',
        'sucursal_id.required' => 'Debe seleccionar una sucursal de destino.',
    ];


    // **************************************************************************************************************
    // METODOS MOUNT************************************************************
    // **************************************************************************************************************
    public function mount(Compra $compra, $productos_sugeridos = '')
    {
        $this->inventarioService = new InventarioService();  // 👈 AGREGAR ESTO AL INICIO

        $this->productos_a_comprar = [];
        $this->compra = $compra;
        $this->productos = Producto::with('categoria')->get();
        $this->productos_sugeridos = $productos_sugeridos;

        // Cargar sucursales
        $this->sucursales = Sucursal::all();

        // 🔴 NUEVA LÓGICA: Verificar si la compra ya tiene detalles guardados en DB
        if ($this->compra->estado == 'Recibido') {
            // Compra ya finalizada, cargar desde DB (solo lectura)
            $this->compra->load('detalles.producto', 'detalles.lote');
            $this->totalCompra = $this->compra->detalles->sum('subtotal');
            $this->carrito = [];
        } elseif ($this->compra->detalles()->count() > 0) {
            // 🔴 IMPORTANTE: La compra tiene detalles guardados (ya se envió al proveedor)
            // Cargar el carrito desde los detalles de DB, no desde sesión
            $this->cargarDetallesDesdeDB();
            $this->totalCompra = $this->compra->detalles->sum('subtotal');

            // También cargar productos sugeridos si existen
            $this->cargarProductosSugeridosDesdeUrl();
        } else {
            // Compra nueva, cargar carrito de sesión
            $this->cargarCarritoDesdeSesion();
            $this->cargarProductosSugeridosDesdeUrl();
        }
    }
    public function cargarDetallesDesdeDB()
    {
        // Guardar valores actuales del carrito (en memoria)
        $valoresActuales = [];
        foreach ($this->carrito as $item) {
            $key = $item['producto_id'];
            $valoresActuales[$key] = [
                'codigo_lote' => $item['codigo_lote'] ?? '',
                'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
            ];
        }

        $this->compra->load(['detalles' => function($query) {
            $query->whereNull('deleted_at');
        }, 'detalles.producto']);

        $this->carrito = [];

        foreach ($this->compra->detalles as $detalle) {
            $key = $detalle->producto_id;

            $this->carrito[] = [
                'id' => 'db_' . $detalle->id,
                'producto_id' => $detalle->producto_id,
                'producto_nombre' => $detalle->producto->nombre,
                'producto_codigo' => $detalle->producto->codigo,
                'marca' => $detalle->producto->marca ?? 'Sin marca',
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => (float)$detalle->precio_unitario,
                'subtotal' => (float)$detalle->subtotal,
                // 🔴 PRESERVAR valores actuales si existen
                'codigo_lote' => $valoresActuales[$key]['codigo_lote'] ?? ($detalle->lote_id ? ($detalle->lote->codigo_lote ?? '') : ''),
                'fecha_vencimiento' => $valoresActuales[$key]['fecha_vencimiento'] ?? ($detalle->lote ? $detalle->lote->fecha_vencimiento : null),
                'detalle_id' => $detalle->id,
            ];
        }

        $this->calcularTotal();
    }
    public function cargarCarritoDesdeSesion()
    {
        $carritoKey = 'carrito_compra_' . $this->compra->id;
        $this->carrito = session($carritoKey, []);
        $this->calcularTotal();
    }
    //Devuelve vacio si no hay stock bajo
    public function cargarProductosSugeridosDesdeUrl()
    {
        if ($this->compra->detalles()->count() > 0) {
            return;
        }

        //solo stock bajo
        $ids_productos = '';
        if (!empty($this->productos_sugeridos)) {
            $ids_productos = $this->productos_sugeridos;
        } elseif (request()->has('productos')) {
            $ids_productos = request('productos', '');
        }

        if (!empty($ids_productos)) {
            $this->sugeridos = explode(',', $ids_productos); // convierte un texto a un array
            $this->productos_a_comprar = []; // Reinicializar como array
            $this->sugerenciasInfo = []; // 👈 LIMPIAR INFO ANTERIOR

            foreach ($this->sugeridos as $productoId) {
                if (empty($productoId)) continue;

                // Cargar producto con categoría
                $producto = Producto::with('categoria')->find($productoId);
                if ($producto) {
                    //revisa carrito si existe producto y devuelvee true o false si no existe
                    $producto_existe_carrito = collect($this->carrito)->contains('producto_id', $producto->id);
                    $producto_existe_db = $this->compra->detalles()
                        ->where('producto_id', $producto->id)
                        ->exists();

                    if (!$producto_existe_carrito && !$producto_existe_db) {
                        // 👇 OBTENER STOCK ACTUAL (si hay sucursal seleccionada, usarla)
                        $stockActual = 0;
                        if (!empty($this->sucursal_id)) {
                            $stockActual = $this->obtenerStockActualProductoSucursal($producto->id, $this->sucursal_id);
                        } else {
                            $stockActual = $this->obtenerStockActualProducto($producto->id);
                        }

                        $cantidadSugerida = $this->calcularCantidadSugerida($producto);

                        $this->productos_a_comprar[] = [
                            'id' => $producto->id,
                            'nombre' => $producto->nombre,
                            'codigo' => $producto->codigo,
                            'marca' => $producto->marca ?? 'Sin marca',
                            'precio_compra' => $producto->precio_compra ?? 0,
                            'codigo_lote' => $this->generateCodigoLote(
                                $producto->nombre,
                                $producto->id,
                                $producto->categoria
                            ),
                            'fecha_vencimiento' => null,
                            'cantidad_sugerida' => $this->calcularCantidadSugerida($producto)
                        ];
                        // 👇 GUARDAR INFO ADICIONAL incluyendo STOCK ACTUAL
                        $this->sugerenciasInfo[$producto->id] = [
                            'stock_actual' => $stockActual,
                            'rop' => $this->inventarioService->puntoReorden($producto->id, $this->sucursal_id ?? 0, $this->compra->proveedor_id),
                            'consumo_diario' => $this->inventarioService->consumoDiario($producto->id, $this->sucursal_id ?? 0),
                            'tiempo_entrega' => $this->inventarioService->tiempoEntregaPromedio($producto->id, $this->compra->proveedor_id),
                            'stock_minimo' => $producto->stock_minimo ?? 0,
                        ];
                    }
                }
            }

            if (count($this->productos_a_comprar) > 0) {
                $this->dispatch('mostrar-alerta', [
                    'mensaje' => count($this->productos_a_comprar) . ' productos con stock bajo están listos para agregar al carrito.',
                    'icono' => 'info'
                ]);
            }
        } else {
            $this->productos_a_comprar = []; // Asegurar que sea array aunque no haya productos
        }
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------



    // **************************************************************************************************************
    // AGREGAR PRODUCTOS A CARRITO Y LOS METODOS ADJUNTOS*******************************************************+++
    // **************************************************************************************************************
    public function agregarAlCarrito()
    {
        // VERIFICACIÓN DOBLE: estado y si ya tiene detalles
        // if ($this->compra->estado == 'Recibido' || $this->compra->detalles()->count() > 0) {
        if ($this->compra->estado == 'Recibido') {
            $this->dispatch('mostrar-alerta',
                mensaje: 'Esta compra ya fue finalizada. No se pueden agregar más productos.',
                icono: 'warning'
            );
            return;
        }

        if (empty($this->productoId)) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'Debe seleccionar un producto',
                'icono' => 'warning'
            ]);
            return;
        }

        $producto = Producto::find($this->productoId);//obtiene el producto seleccionado

        if (!$producto) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'Producto no encontrado',
                'icono' => 'error'
            ]);
            return;
        }

        // VALIDACIÓN DE STOCK MÁXIMO
        $stockActual = $this->obtenerStockActualProducto($producto->id);
        $cantidadActualEnCarrito = $this->obtenerCantidadEnCarrito($producto->id);
        $cantidadTotal = $stockActual + $cantidadActualEnCarrito + $this->cantidad;

        if ($producto->stock_maximo > 0 && $cantidadTotal > $producto->stock_maximo) {
            $disponible = $producto->stock_maximo - ($stockActual + $cantidadActualEnCarrito);
            $this->dispatch('mostrar-alerta-stock', [
                'mensaje' => "⚠️ No se puede agregar {$this->cantidad} unidades de '{$producto->nombre}'.<br><br>" .
                            "📦 Stock actual: {$stockActual} unidades<br>" .
                            "🛒 En carrito: {$cantidadActualEnCarrito} unidades<br>" .
                            "📊 Stock máximo: {$producto->stock_maximo} unidades<br>" .
                            "✅ Solo puedes agregar {$disponible} unidades más.",
                'icono' => 'warning'
            ]);
            return;
        }

        // GENERAR LOTE ÚNICO
        $codigoLote = $this->generarLoteUnico($producto);

        // VALIDACIÓN: Verificar si el lote ya existe en BD para OTRO proveedor
        $loteEnBD = Lote::where('codigo_lote', $codigoLote)
            ->where('proveedor_id', '!=', $this->compra->proveedor_id)
            ->first();
        if ($loteEnBD) {
            $proveedorLote = $loteEnBD->proveedor;
            $mensaje = "El código de lote '{$codigoLote}' ya existe para OTRO proveedor: '{$proveedorLote->nombre}'. ";
            $mensaje .= "No puede usar el mismo código de lote para diferentes proveedores.";

            $this->dispatch('mostrar-alerta', [
                'mensaje' => $mensaje,
                'icono' => 'error'
            ]);
            return;
        }

        // VALIDACIÓN: Buscar si el mismo producto con el mismo lote YA ESTÁ EN EL CARRITO
        $itemExistente = null;
        $itemIndex = null;

        foreach ($this->carrito as $index => $item) {
            if ($item['producto_id'] == $producto->id && $item['codigo_lote'] === $codigoLote) {
                $itemExistente = $item;
                $itemIndex = $index;
                break;
            }
        }

        if ($itemExistente) {
            // Aumentar la cantidad del item existente - VOLVER A VALIDAR STOCK MÁXIMO
            $nuevaCantidad = $itemExistente['cantidad'] + $this->cantidad;

            // Validar nuevamente con la nueva cantidad total
            $stockActual = $this->obtenerStockActualProducto($producto->id);
            $otrasCantidadesEnCarrito = $this->obtenerCantidadEnCarrito($producto->id) - $itemExistente['cantidad'];
            $cantidadTotal = $stockActual + $otrasCantidadesEnCarrito + $nuevaCantidad;

            if ($producto->stock_maximo > 0 && $cantidadTotal > $producto->stock_maximo) {
                $disponible = $producto->stock_maximo - ($stockActual + $otrasCantidadesEnCarrito);
                $this->dispatch('mostrar-alerta-stock', [
                    'mensaje' => "⚠️ No se puede aumentar la cantidad.<br><br>" .
                                "📦 Stock actual: {$stockActual} unidades<br>" .
                                "🛒 En carrito (sin este producto): {$otrasCantidadesEnCarrito} unidades<br>" .
                                "📊 Stock máximo: {$producto->stock_maximo} unidades<br>" .
                                "✅ Solo puedes tener {$disponible} unidades en total de este producto.",
                    'icono' => 'warning'
                ]);
                return;
            }

            $this->carrito[$itemIndex]['cantidad'] = $nuevaCantidad;
            $this->carrito[$itemIndex]['subtotal'] = $nuevaCantidad * $itemExistente['precio_unitario'];

            $this->guardarCarritoEnSesion();
            $this->calcularTotal();

            $this->dispatch('mostrar-alerta', [
                'mensaje' => "Producto ya existente. Cantidad actualizada a {$nuevaCantidad}",
                'icono' => 'info'
            ]);

            $this->reset('productoId');
            $this->cantidad = 1;
            $this->dispatch('producto-agregado');
            return;
        }

        // Si no existe, agregar nuevo item al carrito con el precio actual del producto
        $this->carrito[] = [
            'id' => uniqid(),
            'producto_id' => $producto->id,
            'producto_nombre' => $producto->nombre,
            'producto_codigo' => $producto->codigo,
            'marca' => $producto->marca ?? 'Sin marca',
            'cantidad' => $this->cantidad,
            'precio_unitario' => (float)($producto->precio_compra ?? 0),
            'subtotal' => (float)($this->cantidad * ($producto->precio_compra ?? 0)),
            'codigo_lote' => $codigoLote,
            'fecha_vencimiento' => null,
        ];

        $this->guardarCarritoEnSesion();
        $this->calcularTotal();

        // Resetear
        $this->reset('productoId');
        $this->cantidad = 1;

        $this->dispatch('producto-agregado');
    }
    private function obtenerStockActualProducto($productoId)
    {
        return InventarioSucuralLote::whereHas('lote', function($query) use ($productoId) {
            $query->where('producto_id', $productoId);
        })->sum('cantidad_en_sucursal');
    }
    private function obtenerCantidadEnCarrito($productoId)
    {
        $total = 0;
        foreach ($this->carrito as $item) {
            if ($item['producto_id'] == $productoId) {
                $total += $item['cantidad'];
            }
        }
        return $total;
    }
    private function generarLoteUnico($producto)
    {
        $categoria = $producto->categoria;
        $prefijoCategoria = strtoupper(substr($categoria->nombre ?? 'GEN', 0, 2));
        $prefijoProducto = strtoupper(substr(preg_replace('/[^A-Z]/', '', $producto->nombre), 0, 2));
        $fecha = now()->format('ymd');
        $compraId = str_pad($this->compra->id, 3, '0', STR_PAD_LEFT);

        $maxIntentos = 50;
        $intento = 0;

        do {
            $secuencia = rand(100, 999);
            $codigoLote = "{$prefijoCategoria}{$prefijoProducto}{$fecha}{$compraId}{$secuencia}";

            // Verificar en BD para OTRO proveedor (sí existe para OTRO proveedor, NO puede usar)
            $existeParaOtroProveedor = Lote::where('codigo_lote', $codigoLote)
                ->where('proveedor_id', '!=', $this->compra->proveedor_id)
                ->exists();

            // Verificar en carrito actual (no puede haber duplicados en el mismo carrito)
            $existeEnCarrito = collect($this->carrito)->contains('codigo_lote', $codigoLote);

            $intento++;

            if ($intento >= $maxIntentos) {
                // Si después de muchos intentos no hay éxito, usar timestamp + aleatorio
                $codigoLote = "{$prefijoCategoria}{$prefijoProducto}{$fecha}{$compraId}" . time() . rand(10, 99);
                break;
            }

        } while ($existeParaOtroProveedor || $existeEnCarrito);

        return $codigoLote;
    }
    public function guardarCarritoEnSesion()
    {
        $carritoKey = 'carrito_compra_' . $this->compra->id;
        session([$carritoKey => $this->carrito]);
    }
    public function calcularTotal()
    {
        $this->totalCompra = collect($this->carrito)->sum('subtotal');
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------



    // **************************************************************************************************************
    // BOTON DEL CARRITO (Limpiar Carrito) ANTES DE ENVIAR PROVEEDOR  *******************************************************+++
    // **************************************************************************************************************
    public function limpiarCarrito()
    {
        if ($this->compra->estado == 'Recibido') {
            return;
        }

        // Si la compra ya estaba enviada al proveedor y se limpia el carrito
        if ($this->compra->estado == 'enviado al proveedor') {
            // 🔴 SOFT DELETE de todos los detalles
            DetalleCompra::where('compra_id', $this->compra->id)->delete();

            // Cambiar estado a anulado
            $this->compra->update([
                'estado' => 'anulado',
                'total' => 0
            ]);

            // Limpiar también la sesión
            $this->limpiarCarritoSesion();

            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'El pedido ha sido anulado. Los productos se mantienen en el historial.',
                'icono' => 'warning'
            ]);

            return;
        }

        // Para estado pendiente, solo limpiar sesión
        $this->carrito = [];
        $this->guardarCarritoEnSesion();
        $this->calcularTotal();
    }
    public function limpiarCarritoSesion()
    {
        $carritoKey = 'carrito_compra_' . $this->compra->id;
        session()->forget($carritoKey);
        $this->carrito = [];
        $this->calcularTotal();
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------




    // **************************************************************************************************************
    // FUNCIONES CARRITO, EDICION DE PRODUCTOS DESDE CARRITO *******************************************************+++
    // **************************************************************************************************************
    public function actualizarMarcaCarrito($carritoId, $nuevaMarca)
    {
        if ($this->compra->detalles()->count() > 0) {
            return;
        }

        foreach ($this->carrito as &$item) {
            if ($item['id'] === $carritoId) {
                $item['marca'] = $nuevaMarca;
                break;
            }
        }

        $this->guardarCarritoEnSesion();
    }
    public function limpiarFechaVencimiento($carritoId)
    {
        // if ($this->compra->detalles()->count() > 0) {
        if ($this->compra->estado == 'Recibido') {
            return;
        }

        foreach ($this->carrito as &$item) {
            if ($item['id'] === $carritoId) {
                $item['fecha_vencimiento'] = null;
                break;
            }
        }

        $this->guardarCarritoEnSesion();
    }
    public function actualizarCantidadCarrito($carritoId, $nuevaCantidad)
    {
        if ($this->compra->estado == 'Recibido') {
            return;
        }

        //VALIDACIÓN: Si la cantidad es 0 o negativa, mostrar error
        if (intval($nuevaCantidad) <= 0) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'La cantidad debe ser al menos 1.',
                'icono' => 'warning'
            ]);
            return;
        }

        foreach ($this->carrito as &$item) {
            if ($item['id'] === $carritoId) {
                $producto = Producto::find($item['producto_id']);
                // validacion para stock maximo
                if ($producto && $producto->stock_maximo > 0) {
                    $stockActual = $this->obtenerStockActualProducto($producto->id);
                    $otrasCantidadesEnCarrito = $this->obtenerCantidadEnCarrito($producto->id) - $item['cantidad'];
                    $cantidadTotal = $stockActual + $otrasCantidadesEnCarrito + intval($nuevaCantidad);

                    if ($cantidadTotal > $producto->stock_maximo) {
                        $disponible = $producto->stock_maximo - ($stockActual + $otrasCantidadesEnCarrito);
                        $this->dispatch('mostrar-alerta-stock', [
                            'mensaje' => "⚠️ No se puede establecer {$nuevaCantidad} unidades.<br><br>" .
                                        "📦 Stock actual: {$stockActual} unidades<br>" .
                                        "📊 Stock máximo: {$producto->stock_maximo} unidades<br>" .
                                        "✅ Solo puedes tener {$disponible} unidades en total.",
                            'icono' => 'warning'
                        ]);
                        return;
                    }
                }

                $item['cantidad'] = intval($nuevaCantidad);
                $item['subtotal'] = $item['cantidad'] * $item['precio_unitario'];

                // 🔴 NUEVO: Sincronizar con DB si la compra ya fue enviada al proveedor
                $this->sincronizarDetalleConDB($item);
                break;
            }
        }

        $this->guardarCarritoEnSesion();
        $this->calcularTotal();
    }
    public function actualizarPrecioUnitario($carritoId, $nuevoPrecio)
    {
        if ($this->compra->estado == 'Recibido') {
            return;
        }

        //VALIDACIÓN: Si el precio es 0 o negativo, mostrar error
        if (floatval($nuevoPrecio) <= 0) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'El precio debe ser mayor a 0.',
                'icono' => 'warning'
            ]);
            return;
        }

        foreach ($this->carrito as &$item) {
            if ($item['id'] === $carritoId) {
                $item['precio_unitario'] = floatval($nuevoPrecio);
                $item['subtotal'] = $item['cantidad'] * $item['precio_unitario'];

                // 🔴 NUEVO: Sincronizar con DB si la compra ya fue enviada al proveedor
                $this->sincronizarDetalleConDB($item);
                break;
            }
        }

        $this->guardarCarritoEnSesion();
        $this->calcularTotal();
    }
    private function sincronizarDetalleConDB($item)
    {
        // Solo sincronizar si la compra ya fue enviada al proveedor
        if ($this->compra->estado != 'enviado al proveedor') {
            return;
        }

        $detalle = DetalleCompra::where('compra_id', $this->compra->id)
            ->where('producto_id', $item['producto_id'])
            ->first();

        if ($detalle) {
            $detalle->update([
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'subtotal' => $item['subtotal']
            ]);

            // Recalcular total de la compra
            $nuevoTotal = DetalleCompra::where('compra_id', $this->compra->id)->sum('subtotal');
            $this->compra->update(['total' => $nuevoTotal]);
        }
    }
    //botones editar-eliminar del carrito

    public function eliminarDelCarrito($carritoId)
    {
        if ($this->compra->estado == 'Recibido') {
            return;
        }

        $itemAEliminar = null;
        foreach ($this->carrito as $item) {
            if ($item['id'] === $carritoId) {
                $itemAEliminar = $item;
                break;
            }
        }

        $this->carrito = array_filter($this->carrito, function($item) use ($carritoId) {
            return $item['id'] !== $carritoId;
        });

        $this->carrito = array_values($this->carrito);

        // Sincronizar con DB si la compra ya fue enviada al proveedor
        if ($itemAEliminar && $this->compra->estado == 'enviado al proveedor') {
            // 🔴 SOFT DELETE en lugar de actualizar cantidad a 0
            $detalle = DetalleCompra::where('compra_id', $this->compra->id)
                ->where('producto_id', $itemAEliminar['producto_id'])
                ->first();

            if ($detalle) {
                $detalle->delete(); // Soft delete
            }

            // Verificar si quedan detalles activos (sin soft delete)
            $detallesRestantes = DetalleCompra::where('compra_id', $this->compra->id)
                ->whereNull('deleted_at')
                ->count();

            if ($detallesRestantes == 0) {
                // No quedan productos, anular la compra
                $this->compra->update([
                    'estado' => 'anulado',
                    'total' => 0
                ]);

                $this->dispatch('mostrar-alerta', [
                    'mensaje' => 'El pedido ha sido anulado.',
                    'icono' => 'warning'
                ]);
            } else {
                // Recalcular total solo con detalles activos
                $nuevoTotal = DetalleCompra::where('compra_id', $this->compra->id)
                    ->whereNull('deleted_at')
                    ->sum('subtotal');
                $this->compra->update(['total' => $nuevoTotal]);
            }
        }

        $this->guardarCarritoEnSesion();
        $this->calcularTotal();
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------




    // ***********************************************************************************************************
    // MODAL PARA BOTON DE EDICION EN EL CARRITO*******************************************************************************
    // *********************************************************************************************************

    private function verificarLoteExistente($productoId, $codigoLote, $proveedorId, $excluirCarritoId = null)
    {
        // Verificar en el carrito actual primero
        foreach ($this->carrito as $item) {
            if ($excluirCarritoId && isset($item['id']) && $item['id'] === $excluirCarritoId) {
                continue;
            }

            if ($item['producto_id'] == $productoId && $item['codigo_lote'] === $codigoLote) {
                return 'mismo_carrito';
            }
        }

        // Verificar en la base de datos
        $loteExistente = Lote::where('producto_id', $productoId)
            ->where('codigo_lote', $codigoLote)
            ->first();

        if ($loteExistente) {
            if ($loteExistente->proveedor_id == $proveedorId) {
                return 'mismo_proveedor';
            } else {
                return 'otro_proveedor';
            }
        }

        return false;
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------






    //********************************************************************************************************** */
    // BOTON PARA SELECCIONAR SUCURSAL Y FINALIZAR******************************************************************************
    //********************************************************************************************************** */
    public function confirmarYFinalizar()
    {
        // if ($this->compra->detalles()->count() > 0) {
        if ($this->compra->estado == 'Recibido') {
            // Log::warning('Compra ya finalizada');
            $this->dispatch('mostrar-alerta',
                mensaje: 'Esta compra ya fue finalizada anteriormente.',
                icono: 'info'
            );
            return;
        }

        if (empty($this->carrito)) {
            // Log::warning('Carrito vacío');
            $this->dispatch('mostrar-alerta',
                mensaje: 'No hay productos en el carrito para finalizar.',
                icono: 'warning'
            );
            return;
        }

        if (empty($this->sucursal_id)) {
            // Log::warning('Sucursal no seleccionada');
            $this->dispatch('mostrar-alerta',
                mensaje: 'Debe seleccionar una sucursal de destino.',
                icono: 'warning'
            );
            return;
        }

        // Calcular el total real del carrito
        $totalReal = 0;
        foreach ($this->carrito as $item) {
            $totalReal += $item['subtotal'];
        }

        $cantidadProductos = count($this->carrito);

        // ENVIAR LOS DATOS A items-compra PARA SELECCIONAR SUCURSAL Y FINALIZAR COMPRA
        $this->dispatch('mostrar-confirmacion-finalizar',
            total: $totalReal,
            sucursal_id: $this->sucursal_id,
            cantidad: $cantidadProductos
        );
    }
    public function procesarFinalizacion($sucursal_id)
    {
        try {
            $resultado = $this->procesarFinalizacionCompra($sucursal_id);
            return $resultado;
        } catch (\Exception $e) {
            // Log::error('Error en procesarFinalizacion:', ['error' => $e->getMessage()]);
            $this->dispatch('mostrar-alerta',
                mensaje: 'Error: ' . $e->getMessage(),
                icono: 'error'
            );
            return false;
        }
    }
    public function procesarFinalizacionCompra($sucursal_id) //TODO LOS REGISTROS SE REALIZAN AQUI
    {
        try {
            if (empty($sucursal_id)) {
                throw new \Exception("No se ha seleccionado una sucursal de destino");
            }

            //Obtener detalles desde DB, no desde carrito de sesión
            $detalles = DetalleCompra::where('compra_id', $this->compra->id)
                ->whereNull('lote_id')
                ->get();

            if ($detalles->isEmpty()) {
                throw new \Exception("No hay productos pendientes para finalizar");
            }

            if ($this->compra->estado == 'Recibido') {
                throw new \Exception("Esta compra ya fue recibida anteriormente");
            }

            // Validar stock máximo
            $erroresStockMaximo = [];
            $productosPorProducto = [];

            foreach ($detalles as $detalle) {
                $productoId = $detalle->producto_id;
                if (!isset($productosPorProducto[$productoId])) {
                    $producto = Producto::find($productoId);
                    $productosPorProducto[$productoId] = [
                        'nombre' => $producto->nombre,
                        'cantidad' => 0,
                        'stock_maximo' => $producto->stock_maximo ?? 0
                    ];
                }
                $productosPorProducto[$productoId]['cantidad'] += $detalle->cantidad;
            }

            foreach ($productosPorProducto as $productoId => $data) {
                if ($data['stock_maximo'] > 0) {
                    $stockActual = $this->obtenerStockActualProducto($productoId);
                    $cantidadTotal = $stockActual + $data['cantidad'];

                    if ($cantidadTotal > $data['stock_maximo']) {
                        $excedente = $cantidadTotal - $data['stock_maximo'];
                        $erroresStockMaximo[] = "{$data['nombre']}: Stock actual {$stockActual} + Compra {$data['cantidad']} = {$cantidadTotal} (Stock máximo: {$data['stock_maximo']}). Excede por {$excedente} unidades.";
                    }
                }
            }

            if (!empty($erroresStockMaximo)) {
                throw new \Exception("No se puede finalizar la compra:\n" . implode("\n", $erroresStockMaximo));
            }
            // 🔴 VALIDAR LOTES ANTES DE COMENZAR LA TRANSACCIÓN
            $erroresLotes = $this->validarLotesAntesDeFinalizar();
            if (!empty($erroresLotes)) {
                throw new \Exception("Errores de validación de lotes:\n" . implode("\n", $erroresLotes));
            }

            DB::beginTransaction();

            foreach ($detalles as $detalle) {
                // Buscar el item correspondiente en el carrito
                $itemCarrito = collect($this->carrito)->firstWhere('producto_id', $detalle->producto_id);

                $producto = Producto::find($detalle->producto_id);
                $precioCompra = $detalle->precio_unitario;
                $porcentajeGanancia = $producto->porcentaje_ganancia ?? 30;
                $precioVenta = $precioCompra * (1 + ($porcentajeGanancia / 100));

                // Usar el lote del carrito si existe, sino generar uno
                $codigoLote = ($itemCarrito && !empty($itemCarrito['codigo_lote']))
                    ? $itemCarrito['codigo_lote']
                    : $this->generarLoteUnico($producto);

                $fechaVencimiento = ($itemCarrito && !empty($itemCarrito['fecha_vencimiento']))
                    ? $itemCarrito['fecha_vencimiento']
                    : null;

                $lote = Lote::create([
                    'producto_id' => $detalle->producto_id,
                    'proveedor_id' => $this->compra->proveedor_id,
                    'codigo_lote' => $codigoLote,
                    'fecha_entrada' => now(),
                    'fecha_vencimiento' => $fechaVencimiento,
                    'cantidad_inicial' => $detalle->cantidad,
                    'cantidad_actual' => $detalle->cantidad,
                    'precio_compra' => $detalle->precio_unitario,
                    'precio_venta' => $precioVenta,
                    'estado' => true,
                ]);

                $detalle->update([
                    'lote_id' => $lote->id
                ]);

                // Actualizar precio del producto
                // Actualizar precio del producto y precio de venta
                if ($producto) {
                    $precioCompraAnterior = $producto->precio_compra;
                    $precioVentaAnterior = $producto->precio_venta;

                    // 1. Guardar historial de precio de compra (ya existente)
                    if ($precioCompraAnterior != $detalle->precio_unitario) {
                        HistorialPrecio::create([
                            'producto_id' => $producto->id,
                            'compra_id' => $this->compra->id,
                            'user_id' => Auth::id(),
                            'precio_anterior' => $precioCompraAnterior ?? 0,
                            'precio_nuevo' => $detalle->precio_unitario,
                            'motivo' => 'Actualización por compra',
                            'observaciones' => "Compra #{$this->compra->id}"
                        ]);
                    }

                    // 2. Actualizar precio_compra
                    $producto->precio_compra = $detalle->precio_unitario;

                    // 3. Recalcular precio_venta con la fórmula
                    $porcentajeGanancia = $producto->porcentaje_ganancia ?? 30;
                    $nuevoPrecioVenta = round($detalle->precio_unitario * (1 + $porcentajeGanancia / 100), 2);

                    // 4. Guardar historial de precio de venta SOLO si el precio_venta cambió
                    if ($precioVentaAnterior != $nuevoPrecioVenta) {
                        // Obtener el tipo de cambio oficial actual
                        $tipoCambioOficial = TipoCambio::getOficial();
                        $tcOficialValor = $tipoCambioOficial ? $tipoCambioOficial->precio_dolar : 6.96;

                        // Calcular el tipo de cambio aplicado
                        $tcAplicado = TipoCambio::calcularTipoCambioAplicado(
                            $nuevoPrecioVenta,
                            $detalle->precio_unitario,
                            $porcentajeGanancia,
                            $tcOficialValor
                        );

                        HistorialPrecioVenta::create([
                            'producto_id' => $producto->id,
                            'precio_venta_anterior' => $precioVentaAnterior,
                            'precio_venta_nuevo' => $nuevoPrecioVenta,
                            'tipo_cambio_aplicado' => $tcAplicado,
                            'user_id' => Auth::id(),
                            'motivo' => 'Actualización por compra de producto',
                        ]);
                    }

                    // 5. Asignar el nuevo precio_venta
                    $producto->precio_venta = $nuevoPrecioVenta;

                    // 6. Activar producto si estaba inactivo
                    if ($producto->estado == false) {
                        $producto->estado = true;
                    }

                    $producto->save();
                }

                // Inventario por sucursal
                $inventarioLote = InventarioSucuralLote::firstOrCreate(
                    [
                        'lote_id' => $lote->id,
                        'sucursal_id' => $sucursal_id,
                    ],
                    [
                        'cantidad_en_sucursal' => 0,
                    ]
                );
                $inventarioLote->cantidad_en_sucursal += $detalle->cantidad;
                $inventarioLote->save();

                // Movimiento de inventario
                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'lote_id' => $lote->id,
                    'sucursal_id' => $sucursal_id,
                    'tipo_movimiento' => 'Entrada',
                    'cantidad' => $detalle->cantidad,
                    'fecha' => now(),
                    'observaciones' => 'COMPRA_ID:' . $this->compra->id . ' - Compra #' . $this->compra->id,
                ]);
            }

            // Actualizar total y estado de la compra
            $this->compra->total = $detalles->sum('subtotal');
            $this->compra->estado = 'Recibido';
            $this->compra->save();

            DB::commit();

            // Limpiar carrito de sesión
            $this->limpiarCarritoSesion();

            $notaUrl = route('compras.nota-pdf', $this->compra->id);

            $this->dispatch('compra-finalizada-con-nota',
                compraId: $this->compra->id,
                notaUrl: $notaUrl,
                descargarUrl: route('compras.descargar-nota', $this->compra->id)
            );

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrar-alerta',
                mensaje: 'Error: ' . $e->getMessage(),
                icono: 'error'
            );
            return false;
        }
    }
    private function validarLotesAntesDeFinalizar()
    {
        $errores = [];

        // 🔴 VALIDACIÓN 1: Verificar duplicados en el carrito (mismo producto mismo lote)
        $itemsUnicos = [];
        foreach ($this->carrito as $item) {
            $clave = $item['producto_id'] . '|' . $item['codigo_lote'];
            if (in_array($clave, $itemsUnicos)) {
                $errores[] = "Producto duplicado con mismo lote en carrito: {$item['producto_nombre']} - Lote: {$item['codigo_lote']}";
            }
            $itemsUnicos[] = $clave;
        }

        // 🔴 VALIDACIÓN 2: Verificar lotes de otros proveedores en BD
        foreach ($this->carrito as $item) {
            $loteEnBD = Lote::where('codigo_lote', $item['codigo_lote'])
                ->where('proveedor_id', '!=', $this->compra->proveedor_id)
                ->first();

            if ($loteEnBD) {
                $proveedor = $loteEnBD->proveedor;
                $producto = $loteEnBD->producto;

                $errores[] = "El lote '{$item['codigo_lote']}' ya pertenece a otro proveedor: '{$proveedor->nombre}' " .
                            "(Producto: {$producto->nombre})";
            }
        }

        return $errores;
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------



    // STOCK BAJO

    //********************************************************************************************************** */
    // PROCESO DE EDIT A ITEMSCOMPRA EN MOUNT LUEGO A ITEMS-COMPRA PARA STOCK BAJO Y CARGAR DATOS PRODUCTOS******************************************************************************
    //********************************************************************************************************** */
    private function obtenerStockActualProductoSucursal(int $productoId, int $sucursalId): int
    {
        return (int) InventarioSucuralLote::whereHas('lote', function($query) use ($productoId) {
                $query->where('producto_id', $productoId);
            })
            ->where('sucursal_id', $sucursalId)
            ->sum('cantidad_en_sucursal');
    }
    private function calcularCantidadSugerida($producto)
    {
        if (!empty($this->sucursal_id) && !empty($this->compra->proveedor_id)) {
            try {
                $cantidad = $this->cantidadSugeridaInteligente(
                    $producto->id,
                    $this->sucursal_id,
                    $this->compra->proveedor_id
                );

                $this->sugerenciasInfo[$producto->id] = [
                    'rop' => $this->inventarioService->puntoReorden($producto->id, $this->sucursal_id, $this->compra->proveedor_id),
                    'consumo_diario' => $this->inventarioService->consumoDiario($producto->id, $this->sucursal_id),
                    'tiempo_entrega' => $this->inventarioService->tiempoEntregaPromedio($producto->id, $this->compra->proveedor_id),
                    'stock_actual' => $this->obtenerStockActualProductoSucursal($producto->id, $this->sucursal_id),
                    'stock_minimo' => $producto->stock_minimo,
                ];

                return $cantidad;
            } catch (\Exception $e) {
                Log::warning('Error en cálculo inteligente: ' . $e->getMessage());
            }
        }

        return $producto->stock_minimo > 0 ? $producto->stock_minimo * 2 : 10;
    }
    private function generateCodigoLote($productoNombre, $productoId, $categoriaObjeto)
    {
        $catNombre = $categoriaObjeto->nombre ?? 'GEN';
        $cat = strtoupper(substr($catNombre, 0, 2));
        $prod = strtoupper(substr($productoNombre, 0, 2));
        $fecha = now()->format('ymd');
        $compraId = str_pad($this->compra->id, 3, '0', STR_PAD_LEFT);
        $aleatorio = rand(10, 99);

        return "{$cat}{$prod}-{$fecha}-{$compraId}-{$aleatorio}";
    }
    public function cantidadSugeridaInteligente(int $productoId, int $sucursalId, int $proveedorId): int
    {
        $consumoDiario = $this->inventarioService->consumoDiario($productoId, $sucursalId);
        $tiempoEntrega = $this->inventarioService->tiempoEntregaPromedio($productoId, $proveedorId);
        $stockActual = $this->obtenerStockActualProductoSucursal($productoId, $sucursalId);
        $rop = $this->inventarioService->puntoReorden($productoId, $sucursalId, $proveedorId);

        $faltante = max(0, $rop - $stockActual);

        if ($faltante < $consumoDiario * 7) {
            $cantidad = (int) ceil($consumoDiario * 15);
        } else {
            $cantidad = (int) ceil($faltante * 1.2);
        }

        $producto = Producto::find($productoId);
        $multiplo = $producto->stock_minimo > 0 ? $producto->stock_minimo : 10;

        return max($multiplo, (int) ceil($cantidad / $multiplo) * $multiplo);
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------





    //******************************************************************************************************************** */
    // AGREGAR PRODUCTOS CON STOCK BAJO AL CARRITO DESDE ITEMS-COMPRA******************************************************
    //********************************************************************************************************** */
    //boton para agregar todos los productos sugeridos al carrito
    public function agregarTodosLosProductosSugeridosAlCarrito()
    {
        if ($this->compra->detalles()->count() > 0) {
            return;
        }

        if (empty($this->productos_a_comprar) || !is_array($this->productos_a_comprar)) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'No hay productos pendientes para agregar.',
                'icono' => 'info'
            ]);
            return;
        }

        $contador = 0;
        $errores = [];
        $erroresStockMaximo = [];

        foreach ($this->productos_a_comprar as $index => $producto_data) {
            // 🔴 VALIDACIÓN DE STOCK MÁXIMO ANTES DE AGREGAR CADA PRODUCTO
            $producto = Producto::find($producto_data['id']);
            if ($producto) {
                $stockActual = $this->obtenerStockActualProducto($producto->id);
                $cantidadEnCarrito = $this->obtenerCantidadEnCarrito($producto->id);
                $cantidadTotal = $stockActual + $cantidadEnCarrito + $producto_data['cantidad_sugerida'];

                if ($producto->stock_maximo > 0 && $cantidadTotal > $producto->stock_maximo) {
                    $erroresStockMaximo[] = "{$producto_data['nombre']} (solo se pueden agregar " .
                                            ($producto->stock_maximo - ($stockActual + $cantidadEnCarrito)) . " de {$producto_data['cantidad_sugerida']} sugeridas)";
                    continue;
                }
            }

            // Verificar cada producto antes de agregarlo
            $resultado = $this->verificarLoteExistente(
                $producto_data['id'],
                $producto_data['codigo_lote'],
                $this->compra->proveedor_id
            );

            if ($resultado === 'otro_proveedor') {
                $errores[] = $producto_data['nombre'] . ' (lote de otro proveedor)';
                continue;
            }

            $this->productoId = $producto_data['id'];
            $this->precioCompra = $producto_data['precio_compra'];
            $this->codigoLote = $producto_data['codigo_lote'];
            $this->fechaVencimiento = $producto_data['fecha_vencimiento'];
            $this->cantidad = $producto_data['cantidad_sugerida'];

            $this->agregarAlCarritoDirecto();
            $contador++;
        }

        // Limpiar productos procesados
        $this->productos_a_comprar = [];

        $mensaje = '';
        if ($contador > 0) {
            $mensaje = "✅ Se agregaron {$contador} productos al carrito.";
        }
        if (!empty($errores)) {
            $mensaje .= "\n❌ No se agregaron: " . implode(', ', $errores);
        }
        if (!empty($erroresStockMaximo)) {
            $mensaje .= "\n⚠️ Stock máximo excedido: " . implode(', ', $erroresStockMaximo);
        }

        if ($contador > 0) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => $mensaje,
                'icono' => 'success'
            ]);
        } else {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => "No se pudo agregar ningún producto. " . $mensaje,
                'icono' => 'warning'
            ]);
        }
    }
    //boton para agregar uno por uno cada producto sugerido al carrito
    public function agregarProductoSugeridoAlCarrito($index)
    {
        if ($this->compra->detalles()->count() > 0) {
            return;
        }

        if (!isset($this->productos_a_comprar[$index])) {
            return;
        }

        $producto_data = $this->productos_a_comprar[$index];

        // 🔴 VALIDACIÓN DE STOCK MÁXIMO PARA PRODUCTO SUGERIDO
        $producto = Producto::find($producto_data['id']);
        if ($producto) {
            $stockActual = $this->obtenerStockActualProducto($producto->id);
            $cantidadEnCarrito = $this->obtenerCantidadEnCarrito($producto->id);
            $cantidadTotal = $stockActual + $cantidadEnCarrito + $producto_data['cantidad_sugerida'];

            if ($producto->stock_maximo > 0 && $cantidadTotal > $producto->stock_maximo) {
                $disponible = $producto->stock_maximo - ($stockActual + $cantidadEnCarrito);
                $this->dispatch('mostrar-alerta-stock', [
                    'mensaje' => "⚠️ No se puede agregar '{$producto->nombre}'. " .
                                "Stock actual: {$stockActual} | " .
                                "En carrito: {$cantidadEnCarrito} | " .
                                "Stock máximo: {$producto->stock_maximo}. " .
                                "Solo puedes agregar {$disponible} unidades más.",
                    'icono' => 'warning'
                ]);
                return;
            }
        }

        // VERIFICAR SI EL LOTE YA EXISTE
        $resultado = $this->verificarLoteExistente(
            $producto_data['id'],
            $producto_data['codigo_lote'],
            $this->compra->proveedor_id
        );

        if ($resultado === 'mismo_carrito') {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'Este producto ya está en el carrito.',
                'icono' => 'warning'
            ]);
            return;
        }

        if ($resultado === 'otro_proveedor') {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'El lote ' . $producto_data['codigo_lote'] . ' ya existe para OTRO proveedor. No puede usar este código.',
                'icono' => 'error'
            ]);
            return;
        }

        if ($resultado === 'mismo_proveedor') {
            $this->dispatch('confirmar-lote-duplicado', [
                'producto_id' => $producto_data['id'],
                'cantidad' => $producto_data['cantidad_sugerida'],
                'precio_compra' => $producto_data['precio_compra'],
                'codigo_lote' => $producto_data['codigo_lote'],
                'fecha_vencimiento' => $producto_data['fecha_vencimiento'],
                'marca' => $producto_data['marca'],
                'mensaje' => 'El lote ' . $producto_data['codigo_lote'] . ' ya existe para el MISMO proveedor. ¿Desea continuar?'
            ]);
            return;
        }

        // Si no existe, proceder
        $this->productoId = $producto_data['id'];
        $this->precioCompra = $producto_data['precio_compra'];
        $this->codigoLote = $producto_data['codigo_lote'];
        $this->fechaVencimiento = $producto_data['fecha_vencimiento'];
        $this->cantidad = $producto_data['cantidad_sugerida'];

        $this->agregarAlCarritoDirecto();

        unset($this->productos_a_comprar[$index]);
        $this->productos_a_comprar = array_values($this->productos_a_comprar);
    }
    //boton para eliminar uno por uno cada producto sugerido del carrito
    public function eliminarProductoSugerido($index)
    {
        if (isset($this->productos_a_comprar[$index])) {
            unset($this->productos_a_comprar[$index]);
            $this->productos_a_comprar = array_values($this->productos_a_comprar);

            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'Producto eliminado de la lista',
                'icono' => 'info'
            ]);
        }
    }
    private function agregarAlCarritoDirecto()
    {
        $producto = Producto::find($this->productoId);

        $this->carrito[] = [
            'id' => uniqid(),
            'producto_id' => $producto->id,
            'producto_nombre' => $producto->nombre,
            'producto_codigo' => $producto->codigo,
            'marca' => $producto->marca ?? 'Sin marca',
            'cantidad' => (int)$this->cantidad,
            'precio_unitario' => (float)$this->precioCompra,
            'subtotal' => (float)($this->cantidad * $this->precioCompra),
            'codigo_lote' => $this->codigoLote,
            'fecha_vencimiento' => $this->fechaVencimiento,
        ];

        $this->guardarCarritoEnSesion();
        $this->calcularTotal();

        $this->reset(['productoId', 'precioCompra', 'codigoLote', 'fechaVencimiento']);
        $this->cantidad = 1;
    }
    // ----------------------------------------------------------------------------------------------------------------------
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ----------------------------------------------------------------------------------------------------------------------

    // public function getProductosAComprarProperty()
    // {
    //     return is_array($this->productos_a_comprar) ? $this->productos_a_comprar : [];
    // }
    public function render()
    {
        // Si la compra tiene detalles en DB pero no está recibida (enviado al proveedor)
        if ($this->compra->detalles()->count() > 0 && $this->compra->estado != 'Recibido') {
            $this->cargarDetallesDesdeDB();
            $this->totalCompra = $this->compra->detalles->sum('subtotal');
        } elseif ($this->compra->estado == 'Recibido') {
            $this->compra->load('detalles.producto', 'detalles.lote');
            $this->totalCompra = $this->compra->detalles->sum('subtotal');
            $this->carrito = [];
        }

        return view('livewire.admin.compras.items-compra',[
            'sugerenciasInfo' => $this->sugerenciasInfo
        ]);
    }

}
