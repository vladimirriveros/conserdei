<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    //En los últimos 30 días calcula el promedio de consumo por día.
    public function consumoDiario(int $productoId, int $sucursalId, int $dias = 30): float
    {
        $fechaInicio = now()->subDays($dias);

        //agrupa las fechas y suma las cantidades del producto por día, solo para salidas
        $ventasPorDia = MovimientoInventario::where('producto_id', $productoId)
            ->where('sucursal_id', $sucursalId)
            ->where('tipo_movimiento', 'salida')
            ->where('fecha', '>=', $fechaInicio)
            ->select(DB::raw('DATE(fecha) as fecha'), DB::raw('SUM(cantidad) as total_dia'))
            ->groupBy(DB::raw('DATE(fecha)'))
            ->get();

        $diasActivos = $ventasPorDia->count();//Cuenta cuántos días tuvieron al menos una venta
        $totalSalidas = $ventasPorDia->sum('total_dia');//Suma todas las cantidades vendidas en el período

        if ($diasActivos === 0 || $totalSalidas === 0) {
            return 0;
        }

        if ($diasActivos >= 5) {
            $cantidades = $ventasPorDia->pluck('total_dia')->toArray();
            sort($cantidades);
            $eliminados = [
                'menor' => array_shift($cantidades),
                'mayor' => array_pop($cantidades)
            ];

            if (count($cantidades) > 0) {
                $resultado = round(array_sum($cantidades) / count($cantidades), 2);
                return $resultado;
            }
        }

        return round($totalSalidas / $diasActivos, 2);
    }
    // 🔹 TIEMPO PROMEDIO DE ENTREGA (EN DÍAS)
    public function tiempoEntregaPromedio(int $productoId, int $proveedorId, int $ultimasCompras = 5): float
    {
        $tiempos = DB::table('compras')
            ->join('detalle_compras', 'compras.id', '=', 'detalle_compras.compra_id')
            ->join('lotes', 'detalle_compras.lote_id', '=', 'lotes.id')
            ->where('detalle_compras.producto_id', $productoId)
            ->whereNotNull('lotes.fecha_entrada')//solo compras que ya fueron recibidas
            ->orderBy('compras.fecha', 'desc')
            ->limit($ultimasCompras)
            ->select(DB::raw('DATEDIFF(lotes.fecha_entrada, compras.fecha) as dias_entrega'))//días entre que se pidió y se recibió
            ->pluck('dias_entrega')
            ->toArray();

        if (count($tiempos) > 0) {
            // Eliminar extremos si hay más de 3 valores
            if (count($tiempos) > 3) {
                sort($tiempos);
                array_pop($tiempos);
                array_shift($tiempos);
            }
            return round(array_sum($tiempos) / count($tiempos), 1);
        }

        return 7;
    }

    // 🔹 PUNTO DE REORDEN (ROP)
    public function puntoReorden(int $productoId, int $sucursalId, int $proveedorId): int
    {
        $consumoDiario = $this->consumoDiario($productoId, $sucursalId);
        $tiempoEntrega = $this->tiempoEntregaPromedio($productoId, $proveedorId);

        $producto = Producto::find($productoId);
        $stockMinimo = $producto->stock_minimo ?? 10;

        if ($consumoDiario == 0 || $tiempoEntrega == 0) {
            return $stockMinimo * 2;
        }

        $consumoRedondeado = (int) round($consumoDiario);
        $tiempoRedondeado = (int) round($tiempoEntrega);

        $rop = ($consumoRedondeado * $tiempoRedondeado) + $stockMinimo;

        return max($stockMinimo, $rop);
    }
}
