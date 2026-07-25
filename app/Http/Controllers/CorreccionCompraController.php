<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\InventarioSucuralLote;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CorreccionCompraController extends Controller
{
    /**
     * Muestra el formulario para corregir una compra
     */
    public function edit($compraId)
    {
        $compra = Compra::with(['detalles.producto', 'detalles.lote', 'proveedor'])->findOrFail($compraId);

        // Verificar que la compra esté recibida (tenga inventario)
        if ($compra->estado !== 'Recibido') {
            return redirect()->route('compras.show', $compraId)
                ->with('mensaje', 'Solo se pueden corregir compras que ya fueron recibidas.')
                ->with('icono', 'warning');
        }

        return view('admin.compras.correccion', compact('compra'));
    }

    /**
     * Procesa la corrección de una compra
     */
    public function update(Request $request, $compraId)
    {
        $request->validate([
            'correcciones' => 'required|array',
            'correcciones.*.detalle_id' => 'required|exists:detalle_compras,id',
            'correcciones.*.cantidad_correcta' => 'required|integer|min:0',
            'correcciones.*.motivo' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $compra = Compra::with('detalles')->findOrFail($compraId);
            $usuario = Auth::user();

            foreach ($request->correcciones as $correccion) {
                $detalle = DetalleCompra::with('lote')->findOrFail($correccion['detalle_id']);
                $lote = $detalle->lote;

                // Calcular la diferencia
                $diferencia = $correccion['cantidad_correcta'] - $detalle->cantidad;

                // Si no hay diferencia, saltar
                if ($diferencia == 0) {
                    continue;
                }

                // Buscar el inventario en todas las sucursales donde existe este lote
                $inventarios = InventarioSucuralLote::where('lote_id', $lote->id)->get();

                if ($inventarios->isEmpty()) {
                    throw new \Exception("No hay inventario para el lote {$lote->codigo_lote}");
                }

                // Para correcciones negativas (registré más de lo que debía)
                if ($diferencia < 0) {
                    $cantidad_a_restar = abs($diferencia);
                    $total_disponible = $inventarios->sum('cantidad_en_sucursal');

                    // Verificar que hay suficiente stock para restar
                    if ($total_disponible < $cantidad_a_restar) {
                        throw new \Exception(
                            "Stock insuficiente para corregir el producto {$detalle->producto->nombre}. " .
                            "Disponible: {$total_disponible}, Requerido: {$cantidad_a_restar}"
                        );
                    }

                    // Restar de las sucursales (empezando por la que más tiene)
                    $inventarios = $inventarios->sortByDesc('cantidad_en_sucursal');
                    $restante = $cantidad_a_restar;

                    foreach ($inventarios as $inventario) {
                        if ($restante <= 0) break;

                        $cantidad_a_quitar = min($inventario->cantidad_en_sucursal, $restante);

                        if ($cantidad_a_quitar > 0) {
                            // Actualizar inventario de sucursal
                            $inventario->cantidad_en_sucursal -= $cantidad_a_quitar;
                            $inventario->save();

                            // Crear movimiento de inventario (SALIDA por corrección)
                            MovimientoInventario::create([
                                'producto_id' => $detalle->producto_id,
                                'lote_id' => $lote->id,
                                'sucursal_id' => $inventario->sucursal_id,
                                'tipo_movimiento' => 'Salida',
                                'cantidad' => $cantidad_a_quitar,
                                'fecha' => now(),
                                'observaciones' => "CORRECCIÓN DE COMPRA #{$compra->id}: Se registraron {$detalle->cantidad} unidades, pero debían ser {$correccion['cantidad_correcta']}. Motivo: {$correccion['motivo']}. Usuario: {$usuario->name}",
                            ]);

                            $restante -= $cantidad_a_quitar;
                        }
                    }

                    // Actualizar cantidad actual del lote
                    $lote->cantidad_actual -= $cantidad_a_restar;

                }
                // Para correcciones positivas (registré menos de lo que debía)
                else {
                    // Aumentar en el lote
                    $lote->cantidad_actual += $diferencia;

                    // Buscar la sucursal donde se recibió originalmente (del primer movimiento)
                    $movimientoOriginal = MovimientoInventario::where('lote_id', $lote->id)
                        ->where('tipo_movimiento', 'Entrada')
                        ->where('observaciones', 'LIKE', "%Compra #{$compra->id}%")
                        ->first();

                    $sucursal_id = $movimientoOriginal ? $movimientoOriginal->sucursal_id : $inventarios->first()->sucursal_id;

                    // Aumentar en inventario de sucursal
                    $inventario = InventarioSucuralLote::firstOrCreate(
                        [
                            'lote_id' => $lote->id,
                            'sucursal_id' => $sucursal_id
                        ],
                        ['cantidad_en_sucursal' => 0]
                    );

                    $inventario->cantidad_en_sucursal += $diferencia;
                    $inventario->save();

                    // Crear movimiento de inventario (ENTRADA por corrección)
                    MovimientoInventario::create([
                        'producto_id' => $detalle->producto_id,
                        'lote_id' => $lote->id,
                        'sucursal_id' => $sucursal_id,
                        'tipo_movimiento' => 'Entrada',
                        'cantidad' => $diferencia,
                        'fecha' => now(),
                        'observaciones' => "CORRECCIÓN DE COMPRA #{$compra->id}: Se registraron {$detalle->cantidad} unidades, pero debían ser {$correccion['cantidad_correcta']}. Motivo: {$correccion['motivo']}. Usuario: {$usuario->name}",
                    ]);
                }

                // Guardar el lote actualizado
                $lote->save();

                // Actualizar el detalle de compra (PERO SIN MODIFICAR FECHAS DE CREACIÓN)
                $detalle->cantidad = $correccion['cantidad_correcta'];
                $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
                $detalle->save();
            }

            // Recalcular total de la compra
            $compra->total = $compra->detalles()->sum('subtotal');
            $compra->save();

            DB::commit();

            return redirect()->route('compras.show', $compraId)
                ->with('mensaje', 'Compra corregida exitosamente. Se han generado los movimientos de inventario necesarios.')
                ->with('icono', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('mensaje', 'Error al corregir la compra: ' . $e->getMessage())
                ->with('icono', 'error')
                ->withInput();
        }
    }

    /**
     * Obtener el stock actual de un lote para validaciones AJAX
     */
    public function getStockLote($loteId)
    {
        $lote = Lote::findOrFail($loteId);
        $stockTotal = InventarioSucuralLote::where('lote_id', $loteId)->sum('cantidad_en_sucursal');

        return response()->json([
            'lote' => $lote->codigo_lote,
            'stock_total' => $stockTotal,
            'stock_por_sucursal' => InventarioSucuralLote::with('sucursal')
                ->where('lote_id', $loteId)
                ->get()
                ->map(function($item) {
                    return [
                        'sucursal' => $item->sucursal->nombre,
                        'cantidad' => $item->cantidad_en_sucursal
                    ];
                })
        ]);
    }
}
