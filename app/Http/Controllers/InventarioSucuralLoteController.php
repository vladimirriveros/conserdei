<?php

namespace App\Http\Controllers;

use App\Models\InventarioSucuralLote;
use App\Services\InventarioService;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
// use App\Models\Producto;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventarioSucuralLoteController extends Controller
{
    public function index()
    {
        // Mantener el withCount como en el original
        $sucursales = Sucursal::withCount('inventarioSucuralLotes')->get();

        foreach ($sucursales as $sucursal) {
            $inventario = InventarioSucuralLote::where('sucursal_id', $sucursal->id)
                ->join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
                ->join('productos', 'lotes.producto_id', '=', 'productos.id')
                ->where('productos.estado', true)
                ->select(
                    'productos.id',
                    'productos.stock_minimo',
                    DB::raw('SUM(inventario_sucural_lotes.cantidad_en_sucursal) as total_por_producto'),
                    DB::raw('COUNT(DISTINCT lotes.id) as total_lotes')
                )
                ->groupBy('productos.id', 'productos.stock_minimo')
                ->get();

            // ✅ CORRECCIÓN: Asignar a total_inventario (lo que usa la vista)
            $sucursal->total_inventario = $inventario->sum('total_por_producto');

            // Opcional: también puedes mantener el calculado si lo necesitas
            $sucursal->total_inventario_calculado = $inventario->sum('total_por_producto');

            // Verificar stock bajo
            $productos_bajo_stock = 0;
            foreach ($inventario as $item) {
                if ($item->total_por_producto <= $item->stock_minimo) {
                    $productos_bajo_stock++;
                }
            }

            $sucursal->tiene_stock_bajo = $productos_bajo_stock > 0;
            $sucursal->stock_bajo_count = $productos_bajo_stock;
        }

        return view('admin.inventario.sucursales_por_lotes.index', compact('sucursales'));
    }
    public function mostrar_inventario_por_sucursal($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        // Obtener inventario agrupado por producto
        $inventario_sucursal_por_lotes = InventarioSucuralLote::where('sucursal_id', $id)
            ->join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('productos.estado', true)
            ->select(
                'productos.id as producto_id',
                'productos.codigo as codigo_producto',
                'productos.nombre as producto',
                'productos.stock_minimo',
                'productos.stock_maximo',
                'productos.estado',
                DB::raw('SUM(inventario_sucural_lotes.cantidad_en_sucursal) as cantidad')
            )
            ->groupBy(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'productos.stock_minimo',
                'productos.stock_maximo',
                'productos.estado'
            )
            ->get();

        // 🔥 Instanciar el Service
        $inventarioService = new InventarioService();

        foreach ($inventario_sucursal_por_lotes as $item) {
            // Obtener el proveedor más usado para este producto
            $proveedorPrincipal = DB::table('compras')
                ->join('detalle_compras', 'compras.id', '=', 'detalle_compras.compra_id')
                ->where('detalle_compras.producto_id', $item->producto_id)
                ->select('compras.proveedor_id', DB::raw('COUNT(*) as total_compras'))
                ->groupBy('compras.proveedor_id')
                ->orderBy('total_compras', 'desc')
                ->first();

            $proveedorId = $proveedorPrincipal->proveedor_id ?? 1;

            // 🔥 Usar el Service para calcular
            $consumoDiario = $inventarioService->consumoDiario($item->producto_id, $sucursal->id);
            $tiempoEntrega = $inventarioService->tiempoEntregaPromedio($item->producto_id, $proveedorId);
            $rop = $inventarioService->puntoReorden($item->producto_id, $sucursal->id, $proveedorId);

            $item->consumo_diario = (int) round($consumoDiario);
            $item->tiempo_entrega = (int) round($tiempoEntrega);
            $item->rop = $rop;
            $item->necesita_reorden = $item->cantidad <= $rop;
            $item->faltante = max(0, $rop - $item->cantidad);
        }

        return view('admin.inventario.sucursales_por_lotes.mostrar_inventario_por_sucursal', compact(
            'sucursal',
            'inventario_sucursal_por_lotes'
        ));
    }


    //**********************************************STOCK BAJO POR SUCURSAL ********************************************* */
    public function stock_bajo_por_sucursal($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $inventarioService = new InventarioService(); // 👈 Instanciar el Service

        // 1. Obtener TODO el inventario agrupado por producto
        $inventario_completo = InventarioSucuralLote::where('inventario_sucural_lotes.sucursal_id', $id)
            ->join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('productos.estado', true)
            ->select(
                'productos.id as producto_id',
                'productos.codigo as codigo_producto',
                'productos.nombre as producto',
                'productos.stock_minimo',
                'productos.stock_maximo',
                DB::raw('SUM(inventario_sucural_lotes.cantidad_en_sucursal) as cantidad')
            )
            ->groupBy(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'productos.stock_minimo',
                'productos.stock_maximo'
            )
            ->get();

        $productos_stock_bajo = [];

        foreach ($inventario_completo as $item) {
            // Obtener el proveedor más usado para este producto
            $proveedorPrincipal = DB::table('compras')
                ->join('detalle_compras', 'compras.id', '=', 'detalle_compras.compra_id')
                ->where('detalle_compras.producto_id', $item->producto_id)
                ->select('compras.proveedor_id', DB::raw('COUNT(*) as total_compras'))
                ->groupBy('compras.proveedor_id')
                ->orderBy('total_compras', 'desc')
                ->first();

            $proveedorId = $proveedorPrincipal->proveedor_id ?? 1;

            // 🔥 USAR EL SERVICE (como ya haces en mostrar_inventario_por_sucursal)
            $consumoDiario = $inventarioService->consumoDiario($item->producto_id, $sucursal->id);
            $tiempoEntrega = $inventarioService->tiempoEntregaPromedio($item->producto_id, $proveedorId);
            $rop = $inventarioService->puntoReorden($item->producto_id, $sucursal->id, $proveedorId);

            $esStockBajo = ($item->cantidad <= $item->stock_minimo) || ($item->cantidad <= $rop);

            if ($esStockBajo) {
                $item->rop = $rop;
                $item->consumo_diario = (int) round($consumoDiario);
                $item->tiempo_entrega = (int) round($tiempoEntrega);
                $item->tipo_alerta = ($item->cantidad <= $item->stock_minimo) ? 'Stock mínimo' : 'ROP';
                $item->faltante = max(0, $rop - $item->cantidad);
                $productos_stock_bajo[] = $item;
            }
        }

        $productos_stock_bajo = collect($productos_stock_bajo);

        return view('admin.inventario.sucursales_por_lotes.stock_bajo', compact('sucursal', 'productos_stock_bajo'));
    }

    //ALERTA PARA JAVASCRIPT BAJO STOCK
    // public function alertaStock()
    // {
    //     // Contar todos los productos con stock bajo en todas las sucursales
    //     $total_stock_bajo = InventarioSucuralLote::join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
    //         ->join('productos', 'lotes.producto_id', '=', 'productos.id')
    //         ->where('productos.estado', true)
    //         ->groupBy('productos.id', 'productos.stock_minimo')
    //         ->havingRaw('SUM(inventario_sucural_lotes.cantidad_en_sucursal) <= productos.stock_minimo')
    //         ->select('productos.id')
    //         ->get()
    //         ->count();

    //     return response()->json([
    //         'alerta' => $total_stock_bajo > 0 ? true : false,
    //         'cantidad' => $total_stock_bajo
    //     ]);
    // }


    // public function migrarLotesASucursal(Request $request)
    // {
    //     $sucursal_id = $request->input('sucursal_id');

    //     if (!$sucursal_id) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Debe especificar una sucursal'
    //         ]);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         $lotes = Lote::with('producto')->get();
    //         $contador = 0;

    //         foreach ($lotes as $lote) {
    //             // Verificar si ya existe inventario para este lote en la sucursal
    //             $inventario = InventarioSucuralLote::where([
    //                 'lote_id' => $lote->id,
    //                 'sucursal_id' => $sucursal_id
    //             ])->first();

    //             if (!$inventario) {
    //                 // Crear registro en inventario_sucural_lotes
    //                 InventarioSucuralLote::create([
    //                     'lote_id' => $lote->id,
    //                     'sucursal_id' => $sucursal_id,
    //                     'cantidad_en_sucursal' => $lote->cantidad_actual
    //                 ]);

    //                 // Crear movimiento de entrada
    //                 MovimientoInventario::create([
    //                     'producto_id' => $lote->producto_id,
    //                     'lote_id' => $lote->id,
    //                     'sucursal_id' => $sucursal_id,
    //                     'tipo_movimiento' => 'Entrada',
    //                     'cantidad' => $lote->cantidad_actual,
    //                     'fecha' => now(),
    //                     'observaciones' => 'Migración inicial a sucursal'
    //                 ]);

    //                 $contador++;
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => "Se migraron {$contador} lotes a la sucursal"
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error: ' . $e->getMessage()
    //         ]);
    //     }
    // }

    public function generarPDF($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        // Obtener inventario agrupado por producto
        $inventario = InventarioSucuralLote::where('sucursal_id', $id)
            ->join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('productos.estado', true)
            ->select(
                'productos.id as producto_id',
                'productos.codigo as codigo_producto',
                'productos.nombre as producto',
                'productos.stock_minimo',
                DB::raw('SUM(inventario_sucural_lotes.cantidad_en_sucursal) as cantidad')
            )
            ->groupBy(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'productos.stock_minimo'
            )
            ->orderBy('productos.nombre')
            ->get();

        // Agregar estadísticas de movimientos para cada producto
        foreach ($inventario as $item) {
            // Total de entradas para este producto en la sucursal
            $item->total_entradas = MovimientoInventario::where('producto_id', $item->producto_id)
                ->where('sucursal_id', $id)
                ->where('tipo_movimiento', 'Entrada')
                ->sum('cantidad');

            // Total de salidas para este producto en la sucursal
            $item->total_salidas = MovimientoInventario::where('producto_id', $item->producto_id)
                ->where('sucursal_id', $id)
                ->where('tipo_movimiento', 'Salida')
                ->sum('cantidad');

            // Último movimiento
            $ultimo = MovimientoInventario::where('producto_id', $item->producto_id)
                ->where('sucursal_id', $id)
                ->orderBy('fecha', 'desc')
                ->first();

            $item->ultimo_movimiento = $ultimo ? $ultimo->fecha : null;
        }

        // Calcular estadísticas generales
        $total_productos = $inventario->count();
        $total_items = $inventario->sum('cantidad');

        $productos_stock_bajo = $inventario->filter(function($item) {
            return $item->cantidad <= $item->stock_minimo && $item->cantidad > 0;
        })->count();

        $productos_sin_stock = $inventario->filter(function($item) {
            return $item->cantidad == 0;
        })->count();

        $productos_con_stock = $inventario->filter(function($item) {
            return $item->cantidad > 0;
        })->count();

        // Totales de movimientos
        $total_entradas = MovimientoInventario::where('sucursal_id', $id)
            ->where('tipo_movimiento', 'Entrada')
            ->sum('cantidad');

        $total_salidas = MovimientoInventario::where('sucursal_id', $id)
            ->where('tipo_movimiento', 'Salida')
            ->sum('cantidad');

        // Valor total del inventario (usando precio de compra)
        $valor_total_inventario = InventarioSucuralLote::where('sucursal_id', $id)
            ->join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->sum(DB::raw('inventario_sucural_lotes.cantidad_en_sucursal * lotes.precio_compra'));

        $data = [
            'sucursal' => $sucursal,
            'inventario' => $inventario,
            'total_productos' => $total_productos,
            'total_items' => $total_items,
            'productos_stock_bajo' => $productos_stock_bajo,
            'productos_sin_stock' => $productos_sin_stock,
            'productos_con_stock' => $productos_con_stock,
            'total_entradas' => $total_entradas,
            'total_salidas' => $total_salidas,
            'valor_total_inventario' => $valor_total_inventario,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'observaciones' => 'Reporte generado desde el sistema'
        ];

        $pdf = Pdf::loadView('admin.inventario.sucursales_por_lotes.pdf', $data);

        // Configuración del PDF
        // $pdf->setPaper('A4', 'landscape'); // Horizontal para mejor visualización
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true
        ]);

        $nombre_archivo = 'inventario-' . str_replace(' ', '-', $sucursal->nombre) . '-' . date('Y-m-d') . '.pdf';

        return $pdf->download($nombre_archivo);
        // return $pdf->stream($nombre_archivo); // Para ver en navegador
    }

    public function generarPDFStockBajo($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $productos_stock_bajo = InventarioSucuralLote::where('inventario_sucural_lotes.sucursal_id', $id)
            ->join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('productos.estado', true)
            ->groupBy(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'productos.stock_minimo'
            )
            ->havingRaw('SUM(inventario_sucural_lotes.cantidad_en_sucursal) <= productos.stock_minimo')
            ->select(
                'productos.id as producto_id',
                'productos.codigo as codigo_producto',
                'productos.nombre as producto',
                'productos.stock_minimo',
                DB::raw('SUM(inventario_sucural_lotes.cantidad_en_sucursal) as cantidad')
            )
            ->orderBy('productos.nombre')
            ->get();

        // Calcular estadísticas
        $total_productos = $productos_stock_bajo->count();
        $total_unidades_faltantes = $productos_stock_bajo->sum(function($item) {
            return max(0, $item->stock_minimo - $item->cantidad);
        });
        $total_unidades_actuales = $productos_stock_bajo->sum('cantidad');
        $valor_reposicion = $productos_stock_bajo->sum(function($item) {
            // Obtener precio promedio del producto
            $precio_promedio = Lote::where('producto_id', $item->producto_id)
                ->where('estado', true)
                ->avg('precio_compra') ?? 0;
            return max(0, ($item->stock_minimo - $item->cantidad)) * $precio_promedio;
        });

        $data = [
            'sucursal' => $sucursal,
            'productos' => $productos_stock_bajo,
            'total_productos' => $total_productos,
            'total_unidades_faltantes' => $total_unidades_faltantes,
            'total_unidades_actuales' => $total_unidades_actuales,
            'valor_reposicion' => $valor_reposicion,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'observaciones' => 'Reporte de productos con stock bajo - Requiere reposición urgente'
        ];

        $pdf = Pdf::loadView('admin.inventario.sucursales_por_lotes.stock_bajo_pdf', $data);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        $nombre_archivo = 'stock-bajo-' . str_replace(' ', '-', $sucursal->nombre) . '-' . date('Y-m-d') . '.pdf';

        return $pdf->download($nombre_archivo);
    }

    //detalle de ROP para cada producto (consumo diario y tiempo de entrega)
    public function detalleROP(Request $request, $productoId)
    {
        $producto = Producto::findOrFail($productoId);
        $sucursalId = $request->get('sucursal_id', 1);

        // 🔥 Instanciar el servicio (si no lo tienes)
        $inventarioService = new InventarioService();

        // =====================================================
        // SECCIÓN A: Demanda diaria usando el servicio
        // =====================================================
        $fecha_desde = $request->get('fecha_desde');
        $fecha_hasta = $request->get('fecha_hasta');

        if ($fecha_desde && $fecha_hasta) {
            // Calcular consumo en el rango específico
            $dias = \Carbon\Carbon::parse($fecha_desde)->diffInDays(\Carbon\Carbon::parse($fecha_hasta));
            $consumoDiario = $inventarioService->consumoDiario($productoId, $sucursalId, $dias);

            // Obtener detalles por día para la tabla
            $demandaDiaria = DB::table('movimiento_inventarios')
                ->where('producto_id', $productoId)
                ->where('sucursal_id', $sucursalId)
                ->where('tipo_movimiento', 'salida')
                ->whereDate('fecha', '>=', $fecha_desde)
                ->whereDate('fecha', '<=', $fecha_hasta)
                ->select(DB::raw('DATE(fecha) as fecha'), DB::raw('SUM(cantidad) as total_vendido'))
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'DESC')
                ->get();

            $totalVendido = $demandaDiaria->sum('total_vendido');
            $diasTotales = $demandaDiaria->count();

        } else {
            // Usar el servicio con los 30 días por defecto
            $consumoDiario = $inventarioService->consumoDiario($productoId, $sucursalId, 30);

            $demandaDiaria = DB::table('movimiento_inventarios')
                ->where('producto_id', $productoId)
                ->where('sucursal_id', $sucursalId)
                ->where('tipo_movimiento', 'salida')
                ->whereDate('fecha', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(fecha) as fecha'), DB::raw('SUM(cantidad) as total_vendido'))
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'DESC')
                ->get();

            $totalVendido = $demandaDiaria->sum('total_vendido');
            $diasTotales = $demandaDiaria->count();
            $fecha_desde = now()->subDays(30)->format('Y-m-d');
            $fecha_hasta = now()->format('Y-m-d');
        }

        // =====================================================
        // SECCIÓN B: Tiempo de entrega usando el servicio
        // =====================================================
        $rangoCompras = $request->get('rango_compras', 'todas');

        // Obtener el proveedor más usado para este producto
        $proveedorPrincipal = DB::table('compras')
            ->join('detalle_compras', 'compras.id', '=', 'detalle_compras.compra_id')
            ->where('detalle_compras.producto_id', $productoId)
            ->select('compras.proveedor_id', DB::raw('COUNT(*) as total_compras'))
            ->groupBy('compras.proveedor_id')
            ->orderBy('total_compras', 'desc')
            ->first();

        $proveedorId = $proveedorPrincipal->proveedor_id ?? 1;

        // Usar el servicio para calcular el promedio
        $promedioTiempo = $inventarioService->tiempoEntregaPromedio($productoId, $proveedorId);

        // Para la tabla detallada, consultar directamente (el servicio no da detalles)
        $limite = ($rangoCompras == 'todas') ? null : (int)$rangoCompras;

        $queryTiempos = DB::table('compras')
            ->join('detalle_compras', 'compras.id', '=', 'detalle_compras.compra_id')
            ->join('lotes', 'detalle_compras.lote_id', '=', 'lotes.id')
            ->where('detalle_compras.producto_id', $productoId)
            ->whereNotNull('lotes.fecha_entrada')
            ->select(
                'compras.fecha as fecha_pedido',
                'lotes.fecha_entrada',
                DB::raw('DATEDIFF(lotes.fecha_entrada, compras.fecha) as dias_entrega')
            )
            ->orderBy('compras.fecha', 'DESC');

        if ($limite) {
            $queryTiempos->limit($limite);
        }

        $tiemposEntrega = $queryTiempos->get();
        $totalCompras = $tiemposEntrega->count();

        return response()->json([
            'success' => true,
            'producto' => $producto->nombre,
            'demanda' => [
                'data' => $demandaDiaria,
                'promedio' => round($consumoDiario, 2),
                'total_vendido' => $totalVendido,
                'dias_totales' => $diasTotales,
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta
            ],
            'tiempos_entrega' => [
                'data' => $tiemposEntrega,
                'promedio' => round($promedioTiempo, 1),
                'total_compras' => $totalCompras,
                'rango_seleccionado' => $rangoCompras
            ]
        ]);
    }

}
