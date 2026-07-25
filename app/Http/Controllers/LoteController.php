<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\InventarioSucuralLote;
use App\Models\Lote;
use App\Models\Sucursal;
use App\Models\Salida;
use App\Models\MovimientoInventario;
// use App\Models\DetalleSalida;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
// use Illuminate\Support\Facades\Log; // 👈 AGREGAR ESTA LÍNEA
// use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoteController extends Controller
{
    public function index(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $search = $request->input('search');

        $query = Lote::with([
            'producto.categoria',
            'proveedor',
            'inventarioSucuralLotes.sucursal'
        ]);

        // FILTRO POR FECHA DE ENTRADA (corregido)
        if ($fecha_desde && $fecha_hasta) {
            // Asegurar que las fechas incluyan todo el día
            $fecha_desde_obj = Carbon::parse($fecha_desde)->startOfDay();
            $fecha_hasta_obj = Carbon::parse($fecha_hasta)->endOfDay();

            // Cambiado de fecha_vencimiento a fecha_entrada
            $query->whereBetween('fecha_entrada', [$fecha_desde_obj, $fecha_hasta_obj]);

        } elseif ($fecha_desde || $fecha_hasta) {
            // Si solo una fecha está presente, devolver error y DETENER ejecución
            if ($request->ajax()) {
                return response()->json([
                    'html' => '<tr><td colspan="14" class="text-center text-danger">Debe seleccionar ambas fechas</td></tr>',
                    'total' => 0
                ]);
            } else {
                return redirect()->back()->with('mensaje', 'Debe seleccionar ambas fechas')->with('icono', 'warning');
            }
        }

        // Obtener todos los lotes
        $lotes = $query->get();

        // Calcular propiedades adicionales
        $lotes->each(function ($lote) {
            if ($lote->fecha_vencimiento) {
                $hoy = Carbon::today();
                $lote->is_expired = $lote->fecha_vencimiento->isPast();
                $lote->day_to_expired = $hoy->diffInDays($lote->fecha_vencimiento, false);
            } else {
                $lote->is_expired = false;
                $lote->day_to_expired = null;
            }

            // Calcular estado como texto
            if ($lote->cantidad_actual <= 0) {
                $lote->estado_texto = 'terminado';
                $lote->estado_original = 'Lote terminado';
            } elseif ($lote->is_expired) {
                $lote->estado_texto = 'vencido';
                $lote->estado_original = 'Vencido';
            } elseif ($lote->day_to_expired !== null && $lote->day_to_expired <= 3) {
                $lote->estado_texto = 'por caducar';
                $lote->estado_original = 'Por caducar';
            } else {
                $lote->estado_texto = 'vigente';
                $lote->estado_original = 'Vigente';
            }

            // 👇 CALCULAR PRECIO DE VENTA BASADO EN EL PORCENTAJE DEL PRODUCTO
            $porcentajeGanancia = $lote->producto->porcentaje_ganancia ?? 30;
            $lote->precio_venta_calculado = $lote->precio_compra * (1 + ($porcentajeGanancia / 100));
        });

        // Aplicar filtro de búsqueda GENERAL (incluyendo estado)
        if ($search && $search !== '') {
            $searchLower = trim(strtolower($search));
            $searchTerms = explode(' ', $searchLower);

            $lotes = $lotes->filter(function($lote) use ($searchLower, $searchTerms) {
                $textoBusqueda = strtolower(
                    $lote->codigo_lote . ' ' .
                    ($lote->producto->nombre ?? '') . ' ' .
                    ($lote->producto->codigo ?? '') . ' ' .
                    ($lote->producto->categoria->nombre ?? '') . ' ' .
                    ($lote->proveedor->nombre ?? '') . ' ' .
                    ($lote->proveedor->empresa ?? '') . ' ' .
                    $lote->estado_texto . ' ' .
                    $lote->estado_original
                );

                if (str_contains($textoBusqueda, $searchLower)) {
                    return true;
                }

                foreach ($searchTerms as $term) {
                    if (strlen($term) > 1 && str_contains($textoBusqueda, $term)) {
                        return true;
                    }
                }

                return false;
            })->values();
        }

        // Si es una petición AJAX, devolver solo el HTML de la tabla
        if ($request->ajax()) {
            $html = view('admin.lotes.partials.tabla', compact('lotes'))->render();
            return response()->json([
                'html' => $html,
                'total' => $lotes->count()
            ]);
        }

        return view('admin.lotes.index', compact('lotes'));
    }
    //controla el BOTON de filtrar lotes vencidos (filtrar lotes vencidos)
    public function vencidos_index()
    {
        $hoy = \Carbon\Carbon::today();

        $productos_vencidos = InventarioSucuralLote::join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->join('sucursals', 'inventario_sucural_lotes.sucursal_id', '=', 'sucursals.id')
            ->whereDate('lotes.fecha_vencimiento', '<=', $hoy)
            ->where('inventario_sucural_lotes.cantidad_en_sucursal', '>', 0)
            ->select(
                'productos.id as producto_id',
                'lotes.id as lote_id',
                'inventario_sucural_lotes.sucursal_id',
                'productos.codigo as codigo_producto',
                'productos.nombre as producto',
                'lotes.codigo_lote as lote',
                'lotes.fecha_vencimiento',
                'inventario_sucural_lotes.cantidad_en_sucursal as cantidad',
                'inventario_sucural_lotes.sucursal_id',
                'sucursals.nombre as sucursal'
            )
            ->orderBy('lotes.fecha_vencimiento', 'asc')
            ->get();

        return view('admin.lotes.vencidos', compact('productos_vencidos'));
    }
//************************************************************************************************************************************** ***********************************+*/
//************************************************************************************************************************************** ***********************************+*/
//************************************************************************************************************************************** ***********************************+*/
//************************************************************************************************************************************** ***********************************+*/
    //controla el BOTON de (enviar a salidas)
    public function vencidos_sucursal($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $hoy = now()->format('Y-m-d');

        // PASO 1: OBTENER TODOS LOS PRODUCTOS VENCIDOS DE ESTA SUCURSAL
        $todos_productos_vencidos = InventarioSucuralLote::join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->join('sucursals', 'inventario_sucural_lotes.sucursal_id', '=', 'sucursals.id')
            ->where('inventario_sucural_lotes.sucursal_id', $id)
            ->whereDate('lotes.fecha_vencimiento', '<=', $hoy)
            ->where('inventario_sucural_lotes.cantidad_en_sucursal', '>', 0)
            ->select(
                'lotes.id as lote_id',
                'productos.id as producto_id',
                'productos.codigo as codigo_producto',
                'productos.nombre as producto',
                'lotes.codigo_lote as codigo_lote',
                'lotes.fecha_vencimiento',
                'inventario_sucural_lotes.cantidad_en_sucursal as cantidad',
                'sucursals.nombre as sucursal',
                'lotes.precio_compra'
            )
            ->get()
            ->keyBy('lote_id');

        // PASO 2: OBTENER CARRITO DE SESSION (SIN AUTO-INICIALIZAR)
        $sessionKey = 'carrito_vencidos_sucursal_' . $id;
        $carrito = session($sessionKey, []); // Comienza VACÍO si no existe

        // PASO 3: PRODUCTOS DISPONIBLES = TOTAL - CARRITO
        $productos_vencidos_transformados = collect();

        foreach ($todos_productos_vencidos as $lote_id => $item) {
            if (!isset($carrito[$lote_id])) {
                $productos_vencidos_transformados->push((object) [
                    'lote_id' => $item->lote_id,
                    'codigo_lote' => $item->codigo_lote,
                    'producto_id' => $item->producto_id,
                    'producto' => $item->producto,
                    'codigo_producto' => $item->codigo_producto,
                    'fecha_vencimiento' => $item->fecha_vencimiento,
                    'cantidad' => $item->cantidad,
                    'precio_compra' => $item->precio_compra ?? 0,
                    'perdida' => $item->cantidad * ($item->precio_compra ?? 0),
                    'sucursal_id' => $id,
                    'sucursal_nombre' => $item->sucursal,
                ]);
            }
        }

        // PASO 4: PROCESAR CARRITO PARA LA VISTA
        $detalles_carrito = collect();
        $total_perdida = 0;

        foreach ($carrito as $lote_id => $item) {
            $perdida = $item['cantidad'] * $item['precio_compra'];
            $total_perdida += $perdida;

            $detalles_carrito->push((object) [
                'id' => 'temp_' . $lote_id,
                'producto' => $item['producto'],
                'codigo_lote' => $item['codigo_lote'],
                'fecha_vencimiento' => $item['fecha_vencimiento'],
                'cantidad' => $item['cantidad'],
                'precio_compra' => $item['precio_compra'],
                'perdida' => $perdida,
                'lote_id' => $lote_id,
            ]);
        }
        return view('admin.lotes.vencidos_sucursal', compact(
            'sucursal',
            'productos_vencidos_transformados',
            'detalles_carrito',
            'total_perdida',
            'sessionKey'
        ));
    }

    //botones AGREGAR PRODUCTOS AL CARRITO//************************************************************
    //Agregar TODOS los productos vencidos a la salida de una sola vez
    public function agregarTodosASalida(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'session_key' => 'required|string',
        ]);

        $sucursal_id = $request->sucursal_id;
        $sessionKey = $request->session_key;
        $hoy = now()->format('Y-m-d');

        // Obtener todos los productos vencidos de esta sucursal
        $productos_vencidos = InventarioSucuralLote::join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('inventario_sucural_lotes.sucursal_id', $sucursal_id)
            ->whereDate('lotes.fecha_vencimiento', '<=', $hoy)
            ->where('inventario_sucural_lotes.cantidad_en_sucursal', '>', 0)
            ->select(
                'lotes.id as lote_id',
                'lotes.producto_id',
                'lotes.codigo_lote',
                'lotes.fecha_vencimiento',
                'lotes.precio_compra',
                'productos.nombre as producto_nombre',
                'productos.codigo as producto_codigo',
                'inventario_sucural_lotes.cantidad_en_sucursal as cantidad'
            )
            ->get();

        if ($productos_vencidos->isEmpty()) {
            return redirect()->back()
                ->with('mensaje', 'No hay productos vencidos para agregar')
                ->with('icono', 'warning');
        }

        // Obtener carrito actual
        $carrito = session($sessionKey, []);
        $contador = 0;
        $ya_existentes = 0;

        foreach ($productos_vencidos as $producto) {
            // Verificar si ya está en el carrito
            if (!isset($carrito[$producto->lote_id])) {
                $carrito[$producto->lote_id] = [
                    'lote_id' => $producto->lote_id,
                    'codigo_lote' => $producto->codigo_lote,
                    'producto_id' => $producto->producto_id,
                    'producto' => $producto->producto_nombre,
                    'codigo_producto' => $producto->producto_codigo,
                    'fecha_vencimiento' => $producto->fecha_vencimiento,
                    'cantidad' => $producto->cantidad,
                    'precio_compra' => $producto->precio_compra ?? 0,
                    'perdida' => $producto->cantidad * ($producto->precio_compra ?? 0),
                    'sucursal_id' => $sucursal_id,
                ];
                $contador++;
            } else {
                $ya_existentes++;
            }
        }

        // Guardar carrito actualizado
        session([$sessionKey => $carrito]);

        $mensaje = "Se agregaron {$contador} productos al carrito";
        if ($ya_existentes > 0) {
            $mensaje .= ". {$ya_existentes} productos ya estaban en el carrito";
        }

        return redirect()->route('lotes.vencidos.sucursal', $sucursal_id)
            ->with('mensaje', $mensaje)
            ->with('icono', 'success');
    }
    //Agregar 1 producto vencido a la salida
    public function agregarASalida(Request $request)
    {
        $request->validate([
            'lote_id' => 'required|exists:lotes,id',
            'sucursal_id' => 'required|exists:sucursals,id',
            'session_key' => 'required|string',
            'cantidad' => 'required|integer|min:1',
        ]);

        // Verificar que el lote realmente pertenezca a la sucursal y tenga stock
        $inventario = InventarioSucuralLote::where('lote_id', $request->lote_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->where('cantidad_en_sucursal', '>=', $request->cantidad)
            ->first();

        if (!$inventario) {
            return redirect()->back()
                ->with('mensaje', 'El producto no tiene suficiente stock en esta sucursal')
                ->with('icono', 'error');
        }

        $sessionKey = $request->session_key;
        $carrito = session($sessionKey, []);

        // Verificar si ya existe en el carrito
        if (isset($carrito[$request->lote_id])) {
            return redirect()->back()
                ->with('mensaje', 'Este producto ya está en el carrito')
                ->with('icono', 'warning');
        }

        // Obtener datos del lote
        $lote = Lote::with('producto')->find($request->lote_id);

        // Agregar al carrito
        $carrito[$request->lote_id] = [
            'lote_id' => $lote->id,
            'codigo_lote' => $lote->codigo_lote,
            'producto_id' => $lote->producto_id,
            'producto' => $lote->producto->nombre,
            'codigo_producto' => $lote->producto->codigo,
            'fecha_vencimiento' => $lote->fecha_vencimiento,
            'cantidad' => $request->cantidad,
            'precio_compra' => $lote->precio_compra ?? 0,
            'perdida' => $request->cantidad * ($lote->precio_compra ?? 0),
            'sucursal_id' => $request->sucursal_id,
        ];

        session([$sessionKey => $carrito]);

        return redirect()->route('lotes.vencidos.sucursal', $request->sucursal_id)
            ->with('mensaje', 'Producto agregado al carrito')
            ->with('icono', 'success');
    }
    //**************************************************************************************************


    //botones VACIAR PRODUCTOS AL CARRITO//************************************************************
    //Vaciar todo el carrito
    public function vaciarCarrito(Request $request)
    {
        $request->validate([
            'session_key' => 'required|string',
            'sucursal_id' => 'required|exists:sucursals,id',
        ]);

        // Eliminar completamente el carrito de la sesión
        session()->forget($request->session_key);

        return redirect()->route('lotes.vencidos.sucursal', $request->sucursal_id)
            ->with('mensaje', 'Carrito vaciado correctamente')
            ->with('icono', 'success');
    }
    //Vaciar un producto del carrito
    public function eliminarDeSalida(Request $request, $lote_id)
    {
        $request->validate([
            'session_key' => 'required|string',
            'sucursal_id' => 'required|exists:sucursals,id',
        ]);

        $sessionKey = $request->session_key;
        $carrito = session($sessionKey, []);

        // Eliminar del carrito
        unset($carrito[$lote_id]);

        session([$sessionKey => $carrito]);

        return redirect()->route('lotes.vencidos.sucursal', $request->sucursal_id)
            ->with('mensaje', 'Producto eliminado del carrito')
            ->with('icono', 'success');
    }
    //**************************************************************************************************


    // BOTON FINALIZAR SALIDA - *************************************************************************
    public function finalizarSalidaVencidos(Request $request)
    {
        $request->validate([
            'session_key' => 'required|string',
            'sucursal_id' => 'required|exists:sucursals,id',
        ]);

        $sessionKey = $request->session_key;
        $carrito = session($sessionKey, []);

        if (empty($carrito)) {
            return redirect()->back()
                ->with('mensaje', 'No hay productos en el carrito')
                ->with('icono', 'error');
        }

        DB::beginTransaction();

        try {
            $sucursal = Sucursal::find($request->sucursal_id);
            $usuario = Auth::user();
            $total_perdida = 0;

            // 1. CREAR LA SALIDA (para registrar la pérdida)
            $salida = new Salida();
            $salida->sucursal_id = $request->sucursal_id;
            $salida->user_id = Auth::id();
            $salida->fecha = now()->format('Y-m-d');
            $salida->motivo = 'Caducidad';
            $salida->observaciones = 'Baja por caducidad - Realizado por: ' . $usuario->name;
            $salida->total = 0; // Esto se actualizará después
            $salida->estado = 'Entregado';
            $salida->save();

            // Procesar cada producto del carrito
            foreach ($carrito as $lote_id => $item) {
                $lote = Lote::findOrFail($lote_id);
                $perdida = $item['cantidad'] * $item['precio_compra'];
                $total_perdida += $perdida;

                $precio_compra = $item['precio_compra'] ?? 0;
                $subtotal = $item['cantidad'] * $precio_compra;

                // 2. CREAR DETALLE DE SALIDA
                $salida->detalles()->create([
                    'producto_id' => $item['producto_id'],
                    'lote_id' => $lote_id,
                    'cantidad' => $item['cantidad'],
                    // 'precio_unitario' => 0, // No hay venta, solo baja
                    'precio_unitario' => $precio_compra, // ✅ Guardar el precio de compra
                    // 'subtotal' => 0,
                    'subtotal' => $subtotal, // ✅ Calcular el subtotal
                ]);

                // 3. RESTAR DEL LOTE
                if ($lote->cantidad_actual >= $item['cantidad']) {
                    $lote->cantidad_actual -= $item['cantidad'];
                    $lote->save();
                }

                // 4. RESTAR DEL INVENTARIO SUCURSAL
                $inventario = InventarioSucuralLote::where('lote_id', $lote_id)
                    ->where('sucursal_id', $request->sucursal_id)
                    ->first();

                if ($inventario) {
                    $inventario->cantidad_en_sucursal -= $item['cantidad'];
                    $inventario->save();
                }

                // 5. MOVIMIENTO DE INVENTARIO (con tipo Salida)
                MovimientoInventario::create([
                    'producto_id' => $item['producto_id'],
                    'lote_id' => $lote_id,
                    'sucursal_id' => $request->sucursal_id,
                    'tipo_movimiento' => 'Salida', // ✅ Seguimos usando Salida
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'observaciones' => 'SALIDA_ID:' . $salida->id . ' - Salida # ' . $salida->id . ' Baja por caducidad - Usuario: ' . $usuario->name .
                                ', Vencimiento: ' . Carbon::parse($item['fecha_vencimiento'])->format('d/m/Y') .
                                ', Pérdida: S/ ' . number_format($perdida, 2),
                ]);
            }

            // 6. ACTUALIZAR TOTAL DE LA SALIDA (con el monto de pérdida)
            $salida->total = $total_perdida; // ✅ Guardamos la pérdida como total
            $salida->save();

            // 7. LIMPIAR CARRITO DE SESIÓN
            session()->forget($sessionKey);

            DB::commit();

            return redirect()->route('lotes.index')
                ->with('mensaje', 'Producto Eliminado por caducidad . Pérdida total: S/ ' . number_format($total_perdida, 2))
                ->with('icono', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('mensaje', 'Error: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }
    //**************************************************************************************************

    public function generarPDF(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $search = $request->input('search');

        $query = Lote::with([
            'producto.categoria',
            'proveedor',
            'inventarioSucuralLotes.sucursal'
        ]);

        // MISMA LÓGICA DE FILTRO DE FECHAS - USANDO fecha_entrada
        if ($fecha_desde && $fecha_hasta) {
            $fecha_desde = Carbon::parse($fecha_desde)->startOfDay();
            $fecha_hasta = Carbon::parse($fecha_hasta)->endOfDay();
            // Cambiado de fecha_vencimiento a fecha_entrada
            $query->whereBetween('fecha_entrada', [$fecha_desde, $fecha_hasta]);
        }

        $lotes = $query->get();

        // MISMA LÓGICA DE CÁLCULO DE ESTADOS
        $lotes->each(function ($lote) {
            if ($lote->fecha_vencimiento) {
                $hoy = Carbon::today();
                $lote->is_expired = $lote->fecha_vencimiento->isPast();
                $lote->day_to_expired = $hoy->diffInDays($lote->fecha_vencimiento, false);
            } else {
                $lote->is_expired = false;
                $lote->day_to_expired = null;
            }

            if ($lote->cantidad_actual <= 0) {
                $lote->estado_texto = 'terminado';
                $lote->estado_original = 'Lote terminado';
            } elseif ($lote->is_expired) {
                $lote->estado_texto = 'vencido';
                $lote->estado_original = 'Vencido';
            } elseif ($lote->day_to_expired !== null && $lote->day_to_expired <= 3) {
                $lote->estado_texto = 'por caducar';
                $lote->estado_original = 'Por caducar';
            } else {
                $lote->estado_texto = 'vigente';
                $lote->estado_original = 'Vigente';
            }
        });

        // MISMA LÓGICA DE FILTRO DE BÚSQUEDA
        if ($search && $search !== '') {
            $searchLower = trim(strtolower($search));
            $searchTerms = explode(' ', $searchLower);

            $lotes = $lotes->filter(function($lote) use ($searchLower, $searchTerms) {
                $textoBusqueda = strtolower(
                    $lote->codigo_lote . ' ' .
                    ($lote->producto->nombre ?? '') . ' ' .
                    ($lote->producto->codigo ?? '') . ' ' .
                    ($lote->producto->categoria->nombre ?? '') . ' ' .
                    ($lote->proveedor->nombre ?? '') . ' ' .
                    ($lote->proveedor->empresa ?? '') . ' ' .
                    $lote->estado_texto . ' ' .
                    $lote->estado_original
                );

                if (str_contains($textoBusqueda, $searchLower)) {
                    return true;
                }

                foreach ($searchTerms as $term) {
                    if (strlen($term) > 1 && str_contains($textoBusqueda, $term)) {
                        return true;
                    }
                }

                return false;
            })->values();
        }

        $total_compras = $lotes->sum(function ($lote) {
            return $lote->precio_compra * $lote->cantidad_inicial;
        });

        $data = [
            'lotes' => $lotes,
            'fecha_desde' => $fecha_desde ? Carbon::parse($fecha_desde)->format('d/m/Y') : 'Todo',
            'fecha_hasta' => $fecha_hasta ? Carbon::parse($fecha_hasta)->format('d/m/Y') : 'Todo',
            'search' => $search ?: 'Sin filtro',
            'total_compras' => $total_compras,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'usuario' => Auth::user()->name ?? 'Sistema',
            'total_lotes' => $lotes->count()
        ];

        $pdf = Pdf::loadView('admin.lotes.pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download('lotes-' . date('Y-m-d') . '.pdf');
    }
    public function alertaLotesPorVencer()
    {
        $diasAlerta = 7; // Configurable: 7 días antes del vencimiento

        // Obtener cantidad de lotes que vencen en los próximos 7 días
        $hoy = Carbon::today();
        $fechaLimite = $hoy->copy()->addDays($diasAlerta);

        $lotesPorVencer = Lote::whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>=', $hoy) // No vencidos aún
            ->where('fecha_vencimiento', '<=', $fechaLimite) // Vencen en los próximos X días
            ->where('cantidad_actual', '>', 0) // Solo con stock disponible
            ->count();

        return response()->json([
            'alerta' => $lotesPorVencer > 0,
            'cantidad' => $lotesPorVencer,
            'dias' => $diasAlerta,
            'tipo' => 'por_vencer'
        ]);
    }
}
