<?php

namespace App\Http\Controllers;

// use App\Models\Compra;
// use App\Models\InventarioSucuralLote;
// use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\Salida;
use App\Models\Sucursal;
// use App\Models\DetalleSalida;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
use Exception;

class SalidaController extends Controller
{

    public function index()
    {
        $salidas = Salida::all();
        return view('admin.salidas.index', compact('salidas'));
    }
    public function create()
    {
        $sucursales = Sucursal::all();

        return view('admin.salidas.create', compact('sucursales'));
    }
    public function store(Request $request)
    {
        // dd(auth()->check(), auth()->id());

        // Validación
        $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'fecha' => 'required|date',
            'motivo' => 'required|string|max:150',
            'observaciones' => 'nullable|string|max:255',
        ]);

        // Crear la salida
        $salida = new Salida();
        $salida->sucursal_id = $request->sucursal_id;
        $salida->user_id = Auth::id();
        $salida->fecha = $request->fecha;
        $salida->motivo = $request->motivo;
        $salida->observaciones = $request->observaciones;

        $salida->total = 0;
        $salida->estado = 'Pendiente';

        $salida->save();

        return redirect()->route('salidas.edit', $salida->id)
            ->with('mensaje', 'Salida creada exitosamente. Ahora puede añadir productos.')
            ->with('icono', 'success');
    }
    public function edit($id)
    {
        $salida = Salida::findOrFail($id);
        $productos = Producto::all();
        $sucursales = Sucursal::all();

        // Cargar carrito desde sesión para mostrarlo en la vista principal
        $carritoKey = 'carrito_salida_' . $salida->id;
        $carritoItems = session($carritoKey, []);
        $totalCarrito = collect($carritoItems)->sum('subtotal');

        return view('admin.salidas.edit', compact('salida', 'productos', 'sucursales', 'carritoItems', 'totalCarrito'));
    }
    public function show($id)
    {

        $salida = Salida::with('usuario', 'detalles.producto')->findOrFail($id);


        $salida = Salida::findOrFail($id);

        $movimientoEntrada = MovimientoInventario::whereHas('lote', function ($query) use ($salida) {
            $query->whereIn('id', $salida->detalles->pluck('lote_id'));
        })->where('tipo_movimiento', 'Entrada')->first();

        $sucursal_destino = null;
        if ($movimientoEntrada) {
            $sucursal_destino = Sucursal::find($movimientoEntrada->sucursal_id);
        }

        return view('admin.salidas.show', compact('salida', 'sucursal_destino'));
    }
    public function destroy($id)
    {
        $salida = Salida::with('detalles')->findOrFail($id);
        // return response()->Json($compra);
        DB::beginTransaction();

        try{

            foreach($salida->detalles as $detalle){
                $lote = $detalle->lote;
                //eliminar el lote asociado al detalle de la compra
                // $lote->delete();
                // $detalle->delete();
            }
            $salida->update([
                    'estado' => 'Salida eliminada',
                    'observaciones' => 'eliminada, no tenia productos asociados, ó fue error al crear salida',
                    'total' => 0
                ]);
            $salida->delete();

            DB::commit();
            return redirect()->route('salidas.index')
                    ->with('mensaje','La salida se elimino exitosamente')
                    ->with('icono','success');

        }catch(\Exception $e){
            DB::rollBack();
            dd('Error al eliminar el pedido, '.$e->getMessage());
        }
    }


    public function notaSalidaPdf($id)//Ver nota salida
    {
        $salida = Salida::with([
            'detalles.producto',
            'detalles.lote',
            'sucursal',
            'usuario'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('admin.salidas.pdf.nota_salida', [
            'salida' => $salida,
            'detalles' => $salida->detalles
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
        ]);

        return $pdf->stream('nota_salida_'.$salida->id.'.pdf');
    }
    public function descargarNotaSalida($id)//descargar pdf
    {
        $salida = Salida::with([
            'detalles.producto',
            'detalles.lote',
            'sucursal',
            'usuario'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('admin.salidas.pdf.nota_salida', [
            'salida' => $salida,
            'detalles' => $salida->detalles
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download('nota_salida_'.$salida->id.'.pdf');
    }
}
