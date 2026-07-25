<?php
// app/Http/Controllers/AlertasController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\InventarioService;

class AlertasController extends Controller
{
    public function verificarLotesPorVencer(Request $request)
    {
        $dias = 7;
        // $dias = $request->get('dias', 7);

        // Obtener los lotes próximos a vencer
        $lotes = Lote::where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->where('fecha_vencimiento', '>', now())
            ->where('cantidad_actual', '>', 0)
            ->with('producto')
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return response()->json([
            'alerta' => $lotes->count() > 0,
            'total' => $lotes->count(),
            'dias' => $dias,
            'lotes' => $lotes->map(function($lote) {
                return [
                    'id' => $lote->id,
                    'codigo_lote' => $lote->codigo_lote,
                    'producto_nombre' => $lote->producto->nombre ?? 'N/A',
                    'fecha_vencimiento' => Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y'),
                    'cantidad_actual' => $lote->cantidad_actual,
                    'dias_restantes' => now()->diffInDays($lote->fecha_vencimiento, false)
                ];
            })
        ]);
    }

    public function verificarLotesVencidos(Request $request)
    {
        try {
            // Obtener los lotes ya vencidos con stock disponible
            $lotesVencidos = Lote::where('fecha_vencimiento', '<', date('Y-m-d'))
                ->where('cantidad_actual', '>', 0)
                ->with('producto')
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();

            $resultado = [
                'alerta' => $lotesVencidos->count() > 0,
                'total' => $lotesVencidos->count(),
                'lotes' => []
            ];

            foreach ($lotesVencidos as $lote) {
                // Calcular días vencidos
                $fechaVencimiento = strtotime($lote->fecha_vencimiento);
                $hoy = time();
                $diasVencidos = floor(($hoy - $fechaVencimiento) / (60 * 60 * 24));

                $resultado['lotes'][] = [
                    'id' => $lote->id,
                    'codigo_lote' => $lote->codigo_lote,
                    'producto_nombre' => $lote->producto->nombre ?? 'N/A',
                    'fecha_vencimiento' => date('d/m/Y', strtotime($lote->fecha_vencimiento)),
                    'dias_vencidos' => $diasVencidos,
                    'cantidad_actual' => $lote->cantidad_actual
                ];
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            return response()->json([
                'alerta' => false,
                'total' => 0,
                'lotes' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // stock cero
    public function verificarStockBajo(Request $request)
    {
        $sucursalId = $request->get('sucursal_id', 1);

        // Obtener TODOS los productos que NO tienen NINGÚN lote con stock > 0
        $productosSinStock = Producto::where('estado', true)
            ->whereDoesntHave('lotes', function($query) {
                $query->where('cantidad_actual', '>', 0);
            })
            ->get()
            ->map(function($producto) {
                return [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'codigo' => $producto->codigo,
                    'stock_actual' => 0
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'alerta' => count($productosSinStock) > 0,
            'total' => count($productosSinStock),
            'productos' => array_slice($productosSinStock, 0, 10)
        ]);
    }

    public function verificarROP(Request $request)
    {
        $sucursalId = $request->get('sucursal_id', 1);
        $inventarioService = new InventarioService();

        // Obtener TODOS los lotes con stock > 0
        $todosLosLotes = Lote::where('cantidad_actual', '>', 0)
            ->with('producto', 'proveedor')  // ← Agrega 'proveedor'
            ->get();

        // Agrupar por producto y SUMAR el stock
        $productos = $todosLosLotes->groupBy('producto_id')
            ->map(function($lotes, $productoId) use ($sucursalId, $inventarioService) {
                $primerLote = $lotes->first();
                $stockTotal = $lotes->sum('cantidad_actual');
                $producto = $primerLote->producto;

                // Obtener proveedor del primer lote
                $proveedorId = $primerLote->proveedor_id ?? 1;

                // Calcular ROP usando tu servicio
                $rop = $inventarioService->puntoReorden($productoId, $sucursalId, $proveedorId);

                return [
                    'producto_id' => $productoId,
                    'nombre' => $producto->nombre,
                    'stock_actual' => $stockTotal,
                    'stock_minimo' => $producto->stock_minimo,
                    'rop' => $rop
                ];
            })
            ->filter(function($producto) {
                // Solo mantener si stock_actual <= rop
                return $producto['stock_actual'] <= $producto['rop'];
            })
            ->values()
            ->toArray();

        return response()->json([
            'alerta' => count($productos) > 0,
            'total' => count($productos),
            'productos' => array_slice($productos, 0, 10)
        ]);
    }
}
