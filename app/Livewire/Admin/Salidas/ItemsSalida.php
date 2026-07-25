<?php

namespace App\Livewire\Admin\Salidas;

use App\Models\Salida;
use App\Models\DetalleSalida;
use App\Models\Producto;
use App\Models\InventarioSucuralLote;
use App\Models\MovimientoInventario;
use App\Models\Lote;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ItemsSalida extends Component
{
    public Salida $salida;

    public $productoId;
    public $cantidad = 1;
    public $loteSeleccionado;

    public $productos;
    public $totalSalida = 0;

    public $productoSeleccionadoNombre = '';
    public $lotesDisponibles = [];

    // 🔴 NUEVO: Carrito en sesión
    public $carrito = [];

    protected $listeners = [
        'finalizarSalida' => 'finalizarSalida',
    ];

    // ================================================================================================================
    // CARGA INICIAL MOUNT
    // ================================================================================================================
    public function mount(Salida $salida)
    {
        $this->salida = $salida;
        $this->cargarProductosConStock();

        // 🔴 NUEVO: Cargar carrito desde sesión en lugar de BD
        $this->cargarCarritoDesdeSesion();
    }
    public function cargarProductosConStock()
    {
        $productosConStock = InventarioSucuralLote::where('sucursal_id', $this->salida->sucursal_id)
            ->where('cantidad_en_sucursal', '>', 0)
            ->whereHas('lote', function($query) {
                $query->where('estado', true);
            })
            ->with('lote.producto')
            ->get()
            ->pluck('lote.producto.id')
            ->unique()
            ->values()
            ->toArray();

        $this->productos = Producto::whereIn('id', $productosConStock)
            ->orderBy('codigo')
            ->get();
    }
    // 🔴 NUEVO: Cargar carrito desde sesión
    public function cargarCarritoDesdeSesion()
    {
        $carritoKey = 'carrito_salida_' . $this->salida->id;
        $this->carrito = session($carritoKey, []);
        $this->calcularTotal();
    }
    // 🔴 NUEVO: Calcular total del carrito
    public function calcularTotal()
    {
        $this->totalSalida = collect($this->carrito)->sum('subtotal');

        // Actualizar también el campo total en la salida (para mostrar en la vista)
        $this->salida->total = $this->totalSalida;
    }


    // ================================================================================================================
    // CUANDO CAMBIA EL PRODUCTO seleccionado
    // ================================================================================================================
    public function updatedProductoId($value)
    {
        $this->loteSeleccionado = null;
        $this->lotesDisponibles = [];

        if (empty($value)) {
            return;
        }

        $producto = Producto::find($value);
        if (!$producto) {
            return;
        }

        // Obtener los lotes que YA ESTÁN en el carrito (sesión) para este producto
        $lotesEnCarrito = collect($this->carrito)
            ->where('producto_id', $value)
            ->pluck('codigo_lote')
            ->toArray();

        // Obtener todos los lotes del producto con stock en esta sucursal
        $lotesQuery = InventarioSucuralLote::where('sucursal_id', $this->salida->sucursal_id)
            ->where('cantidad_en_sucursal', '>', 0)
            ->whereHas('lote', function($q) use ($value) {
                $q->where('producto_id', $value)
                  ->where('estado', true);
            })
            ->with('lote')
            ->orderBy('lote_id', 'asc')
            ->get();

        if ($lotesQuery->isEmpty()) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'No hay lotes disponibles para este producto en la sucursal',
                'icono' => 'info'
            ]);
            $this->lotesDisponibles = [];
            return;
        }

        $this->lotesDisponibles = $lotesQuery->map(function($inv) use ($lotesEnCarrito) {
            // Calcular cantidad ya reservada en el carrito para este lote
            $yaEnCarrito = collect($this->carrito)
                ->where('lote_id', $inv->lote_id)
                ->sum('cantidad');

            $disponible = $inv->cantidad_en_sucursal - $yaEnCarrito;
            $loteYaEnCarrito = in_array($inv->lote->codigo_lote, $lotesEnCarrito);

            return (object) [
                'lote_id' => $inv->lote_id,
                'codigo_lote' => $inv->lote->codigo_lote,
                'fecha_vencimiento' => $inv->lote->fecha_vencimiento,
                'fecha_vencimiento_timestamp' => $inv->lote->fecha_vencimiento ? strtotime($inv->lote->fecha_vencimiento) : PHP_INT_MAX,
                'stock_total' => $inv->cantidad_en_sucursal,
                'ya_en_carrito' => $yaEnCarrito,
                'stock_disponible' => max(0, $disponible),
                'precio_venta' => $inv->lote->precio_venta,
                'precio_compra' => $inv->lote->precio_compra,
                'lote_ya_en_carrito' => $loteYaEnCarrito,
            ];
        })
        ->filter(function($lote) {
            return $lote->stock_disponible > 0 && !$lote->lote_ya_en_carrito;
        })
        ->values();

        // Si el motivo es "Venta", ordenar por fecha de vencimiento (FIFO)
        if ($this->salida->motivo == 'Venta') {
            if ($this->lotesDisponibles instanceof \Illuminate\Support\Collection) {
                $this->lotesDisponibles = $this->lotesDisponibles
                    ->sortBy(function($lote) {
                        return $lote->fecha_vencimiento_timestamp;
                    })
                    ->values();
            }

            // Si solo hay un lote disponible, seleccionarlo automáticamente
            if (count($this->lotesDisponibles) === 1) {
                $this->loteSeleccionado = $this->lotesDisponibles[0]->lote_id;
            }
        }

        if (count($this->lotesDisponibles) === 0) {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'Todos los lotes disponibles ya están siendo usados en esta salida',
                'icono' => 'info'
            ]);
        }
    }
    // ================================================================================================================
    // VALIDACION
    // ================================================================================================================
    protected function rules()
    {
        $rules = [
            'productoId' => 'required',
            'cantidad' => 'required|numeric|min:1',
        ];

        if ($this->salida->motivo != 'Venta') {
            $rules['loteSeleccionado'] = 'required';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'productoId.required' => 'Debe seleccionar un producto',
            'loteSeleccionado.required' => 'Debe seleccionar un lote',
            'cantidad.required' => 'Debe ingresar una cantidad',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
        ];
    }

    // ================================================================================================================
    // AGREGAR PRODUCTO AL carrito de SALIDA (guarda en SESIÓN, no en BD)
    // ================================================================================================================
    public function agregarItems()
    {
        // Verificar estado de la salida
        if ($this->salida->estado != 'Pendiente') {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'La salida ya fue finalizada o no está pendiente',
                'icono' => 'warning'
            ]);
            return;
        }

        // Validar campos
        $this->validate();

        // Verificar si hay lotes disponibles
        if (empty($this->lotesDisponibles) || count($this->lotesDisponibles) === 0) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'No hay lotes disponibles. Seleccione otro producto.'
            ]);
            return;
        }

        // Para motivo "Venta" (tienda) - Lógica FIFO
        if ($this->salida->motivo == 'Venta') {
            $this->agregarItemsFIFO();
        } else {
            // Para otros motivos - Lógica normal con selección manual de lote
            $this->agregarItemsNormal();
        }
    }

    private function agregarItemsFIFO()
    {
        // Verificar si ya hay un lote seleccionado manualmente
        if (!empty($this->loteSeleccionado)) {
            $loteInfo = collect($this->lotesDisponibles)->firstWhere('lote_id', $this->loteSeleccionado);
            if ($loteInfo) {
                $this->procesarAgregadoItem($loteInfo);
                return;
            }
        }

        // Si no hay lote seleccionado, aplicar FIFO
        $cantidadRestante = $this->cantidad;
        $lotesFIFO = $this->lotesDisponibles;
        $itemsAgregados = 0;

        try {
            foreach ($lotesFIFO as $loteInfo) {
                if ($cantidadRestante <= 0) break;

                $cantidadATomar = min($cantidadRestante, $loteInfo->stock_disponible);

                if ($cantidadATomar > 0) {
                    // Verificar si ya existe este lote en el carrito
                    $itemExistente = collect($this->carrito)->first(function($item) use ($loteInfo) {
                        return $item['lote_id'] == $loteInfo->lote_id;
                    });

                    if ($itemExistente) {
                        // Actualizar cantidad del item existente
                        $index = collect($this->carrito)->search(function($item) use ($loteInfo) {
                            return $item['lote_id'] == $loteInfo->lote_id;
                        });

                        $this->carrito[$index]['cantidad'] += $cantidadATomar;
                        $this->carrito[$index]['subtotal'] = $this->carrito[$index]['cantidad'] * $this->carrito[$index]['precio_unitario'];
                    } else {
                        // Crear nuevo item en carrito
                        $producto = Producto::find($this->productoId);
                        $this->carrito[] = [
                            'id' => uniqid(),
                            'producto_id' => $this->productoId,
                            'producto_nombre' => $producto->nombre,
                            'producto_codigo' => $producto->codigo,
                            'lote_id' => $loteInfo->lote_id,
                            'codigo_lote' => $loteInfo->codigo_lote,
                            'fecha_vencimiento' => $loteInfo->fecha_vencimiento,
                            'cantidad' => $cantidadATomar,
                            'precio_unitario' => $loteInfo->precio_compra ?? 0,
                            'subtotal' => $cantidadATomar * ($loteInfo->precio_compra ?? 0),
                        ];
                    }

                    $cantidadRestante -= $cantidadATomar;
                    $itemsAgregados++;
                }
            }

            if ($cantidadRestante > 0) {
                throw new Exception('No hay suficiente stock en los lotes disponibles');
            }

            // Guardar carrito en sesión
            $this->guardarCarritoEnSesion();

            // Recargar lotes disponibles del producto actual
            if ($this->productoId) {
                $this->updatedProductoId($this->productoId);
            }

            $this->dispatch('mostrar-alerta', [
                'icono' => 'success',
                'mensaje' => '✅ Producto agregado a la salida usando FIFO'
            ]);

            $this->dispatch('producto-agregado');
            $this->resetearFormulario();

        } catch (Exception $e) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    private function agregarItemsNormal()
    {
        if (empty($this->loteSeleccionado)) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'Debe seleccionar un lote'
            ]);
            return;
        }

        $loteInfo = collect($this->lotesDisponibles)->firstWhere('lote_id', $this->loteSeleccionado);

        if (!$loteInfo) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'Lote no válido o no disponible'
            ]);
            return;
        }

        $this->procesarAgregadoItem($loteInfo);
    }

    private function procesarAgregadoItem($loteInfo)
    {
        // Validación de cantidad
        if ($this->cantidad > $loteInfo->stock_disponible) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => '❌ CANTIDAD EXCEDIDA: No puedes agregar ' . $this->cantidad .
                            ' unidades. El stock disponible en el lote ' . $loteInfo->codigo_lote .
                            ' es de ' . $loteInfo->stock_disponible . ' unidades.'
            ]);
            return;
        }

        // Verificar si el MISMO LOTE ya está en el carrito
        $itemExistente = collect($this->carrito)->first(function($item) use ($loteInfo) {
            return $item['lote_id'] == $loteInfo->lote_id;
        });

        if ($itemExistente) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'warning',
                'mensaje' => '❌ El lote "' . $loteInfo->codigo_lote . '" ya está en el carrito. Actualice la cantidad desde la tabla.'
            ]);
            return;
        }

        $producto = Producto::find($this->productoId);

        // Agregar al carrito
        $this->carrito[] = [
            'id' => uniqid(),
            'producto_id' => $this->productoId,
            'producto_nombre' => $producto->nombre,
            'producto_codigo' => $producto->codigo,
            'lote_id' => $loteInfo->lote_id,
            'codigo_lote' => $loteInfo->codigo_lote,
            'fecha_vencimiento' => $loteInfo->fecha_vencimiento,
            'cantidad' => $this->cantidad,
            'precio_unitario' => $loteInfo->precio_compra ?? 0,
            'subtotal' => $this->cantidad * ($loteInfo->precio_compra ?? 0),
        ];

        // Guardar carrito en sesión
        $this->guardarCarritoEnSesion();

        // Recargar lotes disponibles del producto actual
        if ($this->productoId) {
            $this->updatedProductoId($this->productoId);
        }

        $this->dispatch('mostrar-alerta', [
            'icono' => 'success',
            'mensaje' => '✅ Producto agregado a la salida (Lote: ' . $loteInfo->codigo_lote . ', Cantidad: ' . $this->cantidad . ')'
        ]);

        $this->dispatch('producto-agregado');
        $this->resetearFormulario();
    }

    private function resetearFormulario()
    {
        $this->reset(['productoId', 'loteSeleccionado', 'cantidad']);
        $this->lotesDisponibles = [];
        $this->cantidad = 1;

        // Limpiar buscador
        $this->dispatch('limpiar-buscador');
    }



    // ================================================================================================================
    // SESION EN CARRITO METODOS
    // ================================================================================================================
    // BOTON DEL CARRITO (vaciar carrito) VACIAR TODO EL CARRITO
    public function vaciarCarrito()
    {
        if ($this->salida->estado != 'Pendiente') {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'La salida ya fue finalizada',
                'icono' => 'warning'
            ]);
            return;
        }

        $this->carrito = [];
        $this->guardarCarritoEnSesion();

        // Limpiar selecciones
        $this->productoId = null;
        $this->loteSeleccionado = null;
        $this->lotesDisponibles = [];
        $this->cantidad = 1;

        $this->dispatch('mostrar-alerta', [
            'icono' => 'success',
            'mensaje' => 'Carrito vaciado correctamente'
        ]);

        $this->dispatch('producto-eliminado');
        $this->dispatch('limpiar-buscador');
    }
    // ACTUALIZAR CANTIDAD EN CARRITO de cada producto en sesion
    public function actualizarCantidadItem($itemId, $nuevaCantidad)
    {
        if ($this->salida->estado != 'Pendiente') {
            return;
        }

        $nuevaCantidad = intval($nuevaCantidad);

        if ($nuevaCantidad <= 0) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'La cantidad debe ser mayor a 0'
            ]);
            return;
        }

        // Buscar el item en el carrito
        $index = collect($this->carrito)->search(function($item) use ($itemId) {
            return $item['id'] === $itemId;
        });

        if ($index === false) {
            return;
        }

        $item = $this->carrito[$index];

        // Validar stock disponible en el lote
        $inventario = InventarioSucuralLote::where('lote_id', $item['lote_id'])
            ->where('sucursal_id', $this->salida->sucursal_id)
            ->first();

        if (!$inventario) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'No hay inventario para este lote'
            ]);
            return;
        }

        // Calcular cantidad ya reservada en otros items del carrito (excluyendo este)
        $otrosItems = collect($this->carrito)->filter(function($i) use ($itemId, $item) {
            return $i['id'] !== $itemId && $i['lote_id'] === $item['lote_id'];
        })->sum('cantidad');

        $stockDisponible = $inventario->cantidad_en_sucursal - $otrosItems;

        if ($nuevaCantidad > $stockDisponible) {
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'Stock insuficiente. Disponible: ' . $stockDisponible
            ]);
            return;
        }

        // Actualizar cantidad
        $this->carrito[$index]['cantidad'] = $nuevaCantidad;
        $this->carrito[$index]['subtotal'] = $nuevaCantidad * $this->carrito[$index]['precio_unitario'];

        $this->guardarCarritoEnSesion();

        // $this->dispatch('mostrar-alerta', [
        //     'icono' => 'success',
        //     'mensaje' => 'Cantidad actualizada'
        // ]);
    }
    // 🔴Guardar carrito en sesión
    public function guardarCarritoEnSesion()
    {
        $carritoKey = 'carrito_salida_' . $this->salida->id;
        session([$carritoKey => $this->carrito]);
        $this->calcularTotal();
    }
    // ELIMINAR ITEM DEL CARRITO en sesion
    public function borrarItem($itemId)
    {
        if ($this->salida->estado != 'Pendiente') {
            $this->dispatch('mostrar-alerta', [
                'mensaje' => 'La salida ya fue finalizada',
                'icono' => 'warning'
            ]);
            return;
        }

        // Buscar y eliminar el item del carrito
        $index = collect($this->carrito)->search(function($item) use ($itemId) {
            return $item['id'] === $itemId;
        });

        if ($index !== false) {
            unset($this->carrito[$index]);
            $this->carrito = array_values($this->carrito);
            $this->guardarCarritoEnSesion();

            // Recargar lotes disponibles si hay un producto seleccionado
            if ($this->productoId) {
                $this->updatedProductoId($this->productoId);
            }

            $this->dispatch('mostrar-alerta', [
                'icono' => 'success',
                'mensaje' => 'Producto eliminado del carrito'
            ]);

            $this->dispatch('producto-eliminado');
        }
    }
    //halla el maximo permitido para cada producto en el carrito
    private function calcularMaximosPermitidos()
    {
        $maximos = [];

        // Obtener TODOS los lotes que están en el carrito de una sola consulta
        $lotesIds = collect($this->carrito)->pluck('lote_id')->unique()->toArray();

        if (empty($lotesIds)) {
            return $maximos;
        }

        // Una sola consulta para obtener el stock de todos los lotes en esta sucursal
        $inventarios = InventarioSucuralLote::whereIn('lote_id', $lotesIds)
            ->where('sucursal_id', $this->salida->sucursal_id)
            ->get()
            ->keyBy('lote_id');

        // Para cada item del carrito, calcular su máximo permitido
        foreach ($this->carrito as $item) {
            $loteId = $item['lote_id'];
            $itemId = $item['id'];

            $inventario = $inventarios->get($loteId);
            $stockLote = $inventario ? $inventario->cantidad_en_sucursal : 0;

            // Calcular cantidad reservada por otros items del mismo lote
            $otrosItems = collect($this->carrito)
                ->filter(function ($i) use ($itemId, $loteId) {
                    return $i['id'] !== $itemId && $i['lote_id'] === $loteId;
                })
                ->sum('cantidad');

            $maximos[$itemId] = [
                'stock_lote' => $stockLote,
                'otros_items' => $otrosItems,
                'max_permitido' => max(0, $stockLote - $otrosItems)
            ];
        }

        return $maximos;
    }






    // ================================================================================================================
    // BOTON FINALIZAR SALIDA (lee desde SESIÓN y guarda en BD)
    // ================================================================================================================
    public function finalizarSalida()
    {
        try {
            // Verificar estado
            if ($this->salida->estado != 'Pendiente') {
                throw new Exception('La salida ya fue finalizada');
            }

            // Verificar que haya productos en el carrito
            if (empty($this->carrito)) {
                throw new Exception('No hay productos en el carrito');
            }

            DB::beginTransaction();

            // Primero, guardar los detalles en la BD desde el carrito de sesión
            foreach ($this->carrito as $item) {
                // Verificar que el lote existe y tiene stock suficiente
                $lote = Lote::find($item['lote_id']);
                if (!$lote) {
                    throw new Exception('Lote no encontrado: ' . $item['codigo_lote']);
                }

                // Verificar stock en sucursal considerando otros items del carrito
                $inventario = InventarioSucuralLote::where('lote_id', $item['lote_id'])
                    ->where('sucursal_id', $this->salida->sucursal_id)
                    ->first();

                if (!$inventario || $inventario->cantidad_en_sucursal < $item['cantidad']) {
                    throw new Exception('Stock insuficiente para el lote: ' . $item['codigo_lote']);
                }

                // Crear detalle en BD
                DetalleSalida::create([
                    'salida_id' => $this->salida->id,
                    'producto_id' => $item['producto_id'],
                    'lote_id' => $item['lote_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Actualizar lote
                $lote->cantidad_actual -= $item['cantidad'];
                $lote->save();

                // Actualizar inventario en sucursal
                $inventario->cantidad_en_sucursal -= $item['cantidad'];
                $inventario->save();

                // Registrar movimiento
                $observacion = 'SALIDA_ID:' . $this->salida->id . ' - Salida #' . $this->salida->id . ' por ' . strtolower($this->salida->motivo);

                MovimientoInventario::create([
                    'producto_id' => $item['producto_id'],
                    'lote_id' => $item['lote_id'],
                    'sucursal_id' => $this->salida->sucursal_id,
                    'tipo_movimiento' => 'Salida',
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'observaciones' => $observacion,
                ]);
            }

            // Actualizar estado y total de la salida
            $this->salida->estado = 'Entregado';
            $this->salida->total = collect($this->carrito)->sum('subtotal');
            $this->salida->save();

            DB::commit();

            // Limpiar carrito de sesión
            $this->limpiarCarritoSesion();

            // Disparar evento para mostrar nota
            $notaUrl = route('salidas.nota-pdf', $this->salida->id);
            $this->dispatch('salida-finalizada-con-nota',
                salidaId: $this->salida->id,
                notaUrl: $notaUrl,
                descargarUrl: route('salidas.descargar-nota', $this->salida->id)
            );

        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrar-alerta', [
                'icono' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    // 🔴 NUEVO: Limpiar carrito de sesión
    public function limpiarCarritoSesion()
    {
        $carritoKey = 'carrito_salida_' . $this->salida->id;
        session()->forget($carritoKey);
        $this->carrito = [];
        $this->calcularTotal();
    }








    // ================================================================================================================
    // CUANDO CAMBIA EL LOTE SELECCIONADO (motivo != "Venta")
    // ================================================================================================================
    public function updatedLoteSeleccionado($value)
    {
        if (!empty($value) && !empty($this->lotesDisponibles)) {
            $loteInfo = collect($this->lotesDisponibles)->firstWhere('lote_id', $value);
            if ($loteInfo) {
                $this->dispatch('mostrar-alerta', [
                    'icono' => 'info',
                    'mensaje' => 'Lote seleccionado: ' . $loteInfo->codigo_lote .
                                ' - Stock disponible: ' . $loteInfo->stock_disponible . ' unidades'
                ]);
            }
        }
    }




    // ================================================================================================================
    // RENDER
    // ================================================================================================================
    public function render()
    {
        $maximosPermitidos = $this->calcularMaximosPermitidos();

        return view('livewire.admin.salidas.items-salida', [
            'carritoItems' => $this->carrito,
            'totalCarrito' => $this->totalSalida,
            'maximosPermitidos' => $maximosPermitidos, // 👈 Pasar a la vista
        ]);
    }

}
