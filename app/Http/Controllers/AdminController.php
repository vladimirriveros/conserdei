<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Categoria;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\InventarioSucuralLote;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Salida;
use App\Models\DetalleSalida; // Asegúrate de importar este modelo
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // Tus variables existentes
        $total_sucursales = Sucursal::count();
        $total_categorias = Categoria::count();
        $total_productos = Producto::count();
        $total_proveedores = Proveedor::count();
        $total_compras = Compra::count();

        // LOTES VENCIDOS
        $total_lotes_vencidos = Lote::where('fecha_vencimiento', '<', now())
            ->whereHas('inventarioSucuralLotes', function($q) {
                $q->where('cantidad_en_sucursal', '>', 0);
            })
            ->count();

        // Total inversión en lotes
        // $total_compras_lotes = Lote::sum(DB::raw('precio_compra * cantidad_inicial'));
        $total_compras_lotes = DetalleCompra::join('compras', 'detalle_compras.compra_id', '=', 'compras.id')
        ->where('compras.estado', 'Recibido')
        ->sum(DB::raw('detalle_compras.cantidad * detalle_compras.precio_unitario'));

        // Montos de compras y salidas
        $total_compras_monto = Compra::sum('total');
        $total_salidas_monto = Salida::sum('total');

        // Estados de compras
        $compras_count = Compra::count();
        $compras_pendientes = Compra::where('estado', 'pendiente')->count();
        $compras_enviadas = Compra::where('estado', 'enviado al proveedor')->count();
        $compras_recibidas = Compra::where('estado', 'Recibido')->count();

        // Estados de salidas
        $salidas_count = Salida::count();
        $salidas_pendientes = Salida::where('estado', 'pendiente')->count();
        $salidas_proceso = Salida::where('estado', 'en proceso')->count();
        $salidas_entregadas = Salida::where('estado', 'Entregado')->count();

        // Productos en inventario
        $total_productos_inventario = Producto::where('estado', true)
            ->whereHas('lotes.inventarioSucuralLotes', function($q) {
                $q->where('cantidad_en_sucursal', '>', 0);
            })
            ->count();

        // INVENTARIO POR SUCURSAL
        $inventario_por_sucursal = [];
        $sucursales = Sucursal::all();
        foreach ($sucursales as $sucursal) {
            $productos_con_stock = InventarioSucuralLote::where('sucursal_id', $sucursal->id)
                ->join('lotes', 'inventario_sucural_lotes.lote_id', '=', 'lotes.id')
                ->join('productos', 'lotes.producto_id', '=', 'productos.id')
                ->where('productos.estado', true)
                ->where('inventario_sucural_lotes.cantidad_en_sucursal', '>', 0)
                ->distinct('productos.id')
                ->count('productos.id');

            $total_unidades = InventarioSucuralLote::where('sucursal_id', $sucursal->id)
                ->where('cantidad_en_sucursal', '>', 0)
                ->sum('cantidad_en_sucursal');

            $inventario_por_sucursal[$sucursal->id] = [
                'nombre' => $sucursal->nombre,
                'total_productos' => $productos_con_stock,
                'total_unidades' => $total_unidades
            ];
        }

        // ============================================
        // NUEVAS CONSULTAS PARA PRODUCTOS MÁS Y MENOS VENDIDOS
        // ============================================

        // PRODUCTOS CON MÁS SALIDAS (para tienda)
        $productos_mas_salidas = DetalleSalida::select(
                'productos.id',
                'productos.nombre as producto',
                'productos.codigo',
                DB::raw('SUM(detalle_salidas.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_salidas.subtotal) as total_monto')
            )
            ->join('salidas', 'detalle_salidas.salida_id', '=', 'salidas.id')
            ->join('productos', 'detalle_salidas.producto_id', '=', 'productos.id')
            ->where('salidas.motivo', 'Venta') // FILTRO POR MOTIVO "tienda"
            ->where('salidas.estado', 'Entregado') // Solo salidas entregadas
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get();

        $productos_menos_salidas = DB::table('productos')
            ->leftJoin('detalle_salidas', 'productos.id', '=', 'detalle_salidas.producto_id')
            ->leftJoin('salidas', function($join) {
                $join->on('detalle_salidas.salida_id', '=', 'salidas.id')
                    ->where('salidas.motivo', 'tienda')
                    ->where('salidas.estado', 'Entregado');
            })
            ->where('productos.estado', true)
            ->select(
                'productos.id',
                'productos.nombre as producto',
                'productos.codigo',
                DB::raw('COALESCE(SUM(detalle_salidas.subtotal), 0) as total_monto')
                // No incluimos cantidad si no la necesitas
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByRaw('
                CASE
                    WHEN COALESCE(SUM(detalle_salidas.subtotal), 0) = 0 THEN 0
                    ELSE 1
                END ASC
            ')
            ->orderBy('total_monto', 'asc')
            ->limit(5)
            ->get();

        // Para el conteo total de productos con cero ventas (opcional)
        $productos_con_cero_ventas = DB::table('productos')
            ->leftJoin('detalle_salidas', 'productos.id', '=', 'detalle_salidas.producto_id')
            ->leftJoin('salidas', function($join) {
                $join->on('detalle_salidas.salida_id', '=', 'salidas.id')
                    ->where('salidas.motivo', 'tienda')
                    ->where('salidas.estado', 'Entregado');
            })
            ->where('productos.estado', true)
            ->groupBy('productos.id')
            ->havingRaw('COALESCE(SUM(detalle_salidas.cantidad), 0) = 0')
            ->select('productos.id')
            ->count();

        // TOTAL DE VENTAS EN TIENDA
        $total_ventas_tienda = Salida::where('motivo', 'Venta')
            ->where('estado', 'Entregado')
            ->sum('total');

        return view('admin.index', compact(
            'total_sucursales',
            'total_categorias',
            'total_productos',
            'total_proveedores',
            'total_compras',
            'total_lotes_vencidos',
            'total_compras_monto',
            'total_compras_lotes',
            'total_salidas_monto',
            'compras_count',
            'compras_pendientes',
            'compras_enviadas',
            'compras_recibidas',
            'salidas_count',
            'salidas_pendientes',
            'salidas_proceso',
            'salidas_entregadas',
            'total_productos_inventario',
            'inventario_por_sucursal',
            // NUEVAS VARIABLES
            'productos_mas_salidas',
            'productos_menos_salidas',
            'productos_con_cero_ventas',
            'total_ventas_tienda'
        ));
    }
}
