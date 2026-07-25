<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\DetalleCompra;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Mail\CompraProveedorMail;
// use App\Models\InventarioSucuralLote;
// use App\Models\Lote;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
// o si usas laravel-dompdf: use PDF;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::all();
        // Agregar a cada compra un indicador si tiene detalles
        foreach ($compras as $compra) {
            $compra->tiene_detalles = DetalleCompra::withTrashed()
                ->where('compra_id', $compra->id)
                ->exists();
        }
        return view('admin.compras.index', compact('compras'));
    }
    public function create(Request $request)
    {
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        $sucursales = Sucursal::all();

        $observacion_predefinida = $request->query('obs', '');
        $productos_sugeridos = $request->query('productos', '');

        if (!empty($productos_sugeridos)) {
            session(['productos_sugeridos_temp' => $productos_sugeridos]);
            session(['observacion_compra_temp' => $observacion_predefinida]);
        }

        return view('admin.compras.create', compact('proveedores', 'productos', 'sucursales',
        'observacion_predefinida', 'productos_sugeridos'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedors,id',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $compra = new Compra();
        $compra->proveedor_id = $request->proveedor_id;
        $compra->user_id = Auth::id();  // 👈 AGREGAR ESTA LÍNEA
        $compra->fecha = $request->fecha;
        $compra->observaciones = $request->observaciones;
        $compra->total = 0;
        $compra->estado = 'pendiente';
        $compra->save();

        //********* */ PRODUCTOS SUGERIDOS DESDE STOCK BAJO***********************************************************
        $productos_sugeridos = session('productos_sugeridos_temp', '');

        // Extraer el nombre de la sucursal de la observación (viene del botón de stock bajo)
        $sucursal_nombre = null;
        if (!empty($request->observaciones) && strpos($request->observaciones, 'Reposición:') !== false) {
            $sucursal_nombre = str_replace('Reposición: ', '', $request->observaciones);
            // Guardamos el nombre en sesión para usarlo después
            session(['sucursal_origen_nombre' => $sucursal_nombre]);
        }

        session()->forget(['productos_sugeridos_temp', 'observacion_compra_temp']);

        if (!empty($productos_sugeridos)) {
            return redirect()->route('compras.edit', [
                'id' => $compra->id,
                'productos' => $productos_sugeridos,
                'sucursal' => $sucursal_nombre // Pasamos el nombre por URL
            ])->with('mensaje', 'Compra creada exitosamente. Se cargarán los productos con stock bajo.')
            ->with('icono', 'success');
        }
        //********* */ PRODUCTOS SUGERIDOS DESDE STOCK BAJO***********************************************************

        return redirect()->route('compras.edit', ['id' => $compra->id])
            ->with('mensaje', 'Compra creada exitosamente. Ahora puede añadir productos.')
            ->with('icono', 'success');
    }
    public function edit($id)
    {
        $compra = Compra::findOrFail($id);
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        $sucursales = Sucursal::all();

        // Intentar obtener la sucursal de origen (prioridad: URL > sesión)
        $sucursalDefault = null;
        $sucursalNombre = request('sucursal', session('sucursal_origen_nombre'));

        if ($sucursalNombre) {
            // Buscar la sucursal por nombre (aproximado)
            $sucursal = Sucursal::where('nombre', 'LIKE', '%' . $sucursalNombre . '%')->first();
            $sucursalDefault = $sucursal ? $sucursal->id : null;
        }

        // Limpiar la sesión después de usarla
        session()->forget('sucursal_origen_nombre');

        return view('admin.compras.edit', compact('compra', 'proveedores', 'productos', 'sucursales', 'sucursalDefault'));
    }
    public function show($id)
    {
        // Cargar la compra
        $compra = Compra::with('proveedor')->findOrFail($id);

        // Cargar detalles MANUALMENTE con withTrashed
        $detalles = DetalleCompra::withTrashed()
            ->where('compra_id', $compra->id)
            ->with(['producto', 'lote'])
            ->get();

        // Asignar manualmente los detalles a la compra
        $compra->setRelation('detalles', $detalles);

        $movimientoEntrada = MovimientoInventario::whereHas('lote', function ($query) use ($detalles) {
            $query->whereIn('id', $detalles->pluck('lote_id')->filter());
        })->where('tipo_movimiento', 'Entrada')->first();

        $sucursal_destino = null;
        if ($movimientoEntrada) {
            $sucursal_destino = Sucursal::find($movimientoEntrada->sucursal_id);
        }

        return view('admin.compras.show', compact('compra', 'sucursal_destino'));
    }
    public function destroy($id)
    {
        $compra = Compra::with('detalles')->findOrFail($id);

        // Verificar si la compra tiene detalles (incluyendo soft delete)
        $tieneDetalles = DetalleCompra::withTrashed()
            ->where('compra_id', $compra->id)
            ->exists();

        // CASO 1: La compra tiene detalles (estado 'enviado al proveedor' o 'pendiente' con detalles)
        if ($tieneDetalles) {

            DetalleCompra::where('compra_id',$compra->id)->delete();
            // Solo cambiar estado a anulado, no eliminar nada físicamente
            $compra->update([
                'estado' => 'anulado',
                'observaciones' => 'compra anulada porque el proveedor no tenia stock, ó el pedido no fue recibido',
                'total' => 0
            ]);

            // Limpiar carrito de sesión si existe
            $carritoKey = 'carrito_compra_' . $compra->id;
            if (session()->has($carritoKey)) {
                session()->forget($carritoKey);
            }

            return redirect()->route('compras.index')
                ->with('mensaje', 'La compra tenía productos pendientes. Se ha anulado correctamente. Puede ver los productos en el detalle de la compra.')
                ->with('icono', 'warning');
        }

        // CASO 2: La compra NO tiene detalles (eliminar físicamente)
        DB::beginTransaction();

        try {
            if (!$tieneDetalles) {
                // Si por alguna razón hay detalles (aunque no debería llegar aquí), eliminarlos físicamente
                $compra->update([
                    'estado' => 'Compra eliminada',
                    'observaciones' => 'eliminada porque no tenia productos asociados, ó fue error al crear compra',
                    'total' => 0
                ]);
            }
            $compra->delete();

            DB::commit();

            return redirect()->route('compras.index')
                ->with('mensaje', 'El pedido se eliminó exitosamente')
                ->with('icono', 'success');

        } catch(\Exception $e){
            DB::rollBack();

            return redirect()->back()
                ->with('mensaje', 'Error al eliminar el pedido: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }

    public function enviarCorreo($id)
    {
        $compra = Compra::with('proveedor')->findOrFail($id);

        // Verificar si hay carrito en sesión
        $carritoKey = 'carrito_compra_' . $compra->id;
        $carrito_temporal = session($carritoKey, []);

        if (empty($carrito_temporal)) {
            // Si no hay carrito en sesión, intentar usar los detalles de DB
            $compra->load('detalles.producto');
            $carrito_temporal = $compra->detalles->map(function($detalle) {
                return [
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto->nombre,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal
                ];
            })->toArray();
        } else {
            // Guardar los detalles en DB desde el carrito de sesión
            $this->guardarDetallesDesdeCarrito($compra);
            // Recargar la compra con los detalles guardados
            $compra->load('detalles.producto');
        }

        // Enviar correo
        $proveedorEmail = $compra->proveedor->email;
        Mail::to($proveedorEmail)->send(new CompraProveedorMail($compra, $carrito_temporal));

        return redirect()->back()
            ->with('mensaje', 'Correo enviado al proveedor exitosamente.')
            ->with('icono', 'success');
    }
    public function enviarWhatsappPdf(Compra $compra)
    {
        $proveedor = $compra->proveedor;

        if (!$proveedor || empty($proveedor->telefono)) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor o teléfono no disponible'
            ], 400);
        }

        // Verificar si hay carrito en sesión y guardar en DB si existe
        $carritoKey = 'carrito_compra_' . $compra->id;
        $carrito = session($carritoKey, []);

        if (!empty($carrito)) {
            $this->guardarDetallesDesdeCarrito($compra);
            $compra->load('detalles.producto');
        }

        try {
            $telefono = preg_replace('/\D+/', '', $proveedor->telefono);
            $codigoPais = '591';
            $telefonoFinal = strpos($telefono, $codigoPais) === 0 ? $telefono : $codigoPais . $telefono;

            $mensaje = "*SOLICITUD DE COMPRA CONSERDEI*\n\n";
            $mensaje .= "Estimad@ *{$proveedor->nombre}*, tenemos un pedido para ti.\n\n";
            $mensaje .= "Por favor confirmar la disponibilidad de los productos\n";
            $mensaje .= "a la brevedad posible.\n";
            $mensaje .= "Gracias.";

            $mensajeCodificado = urlencode($mensaje);

            $userAgent = request()->header('User-Agent');
            $isMobile = preg_match('/(android|iphone|ipad|ipod)/i', $userAgent);

            if ($isMobile) {
                $url = "https://wa.me/$telefonoFinal?text=$mensajeCodificado";
            } else {
                $url = "https://web.whatsapp.com/send?phone=$telefonoFinal&text=$mensajeCodificado";
            }

            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Mensaje preparado',
                'pdf_url' => route('compras.descargarPdf', $compra->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function notaCompraPdf($id)//ver nota
    {
        $compra = Compra::with(['detalles.producto', 'detalles.lote', 'proveedor'])->findOrFail($id);

        // Obtener la sucursal de destino del primer movimiento de inventario
        $sucursal = null;
        $primerDetalle = $compra->detalles->first();
        if ($primerDetalle && $primerDetalle->lote) {
            $inventario = $primerDetalle->lote->inventarioSucuralLotes()->first();
            if ($inventario) {
                $sucursal = Sucursal::find($inventario->sucursal_id);
            }
        }

        $pdf = Pdf::loadView('admin.compras.pdf.nota_compra', [
            'compra' => $compra,
            'detalles' => $compra->detalles,
            'sucursal' => $sucursal
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
        ]);

        return $pdf->stream('nota_compra_'.$compra->id.'.pdf');
    }
    public function descargarNotaCompra($id)//descarga pdf
    {
        $compra = Compra::with(['detalles.producto', 'detalles.lote', 'proveedor'])->findOrFail($id);

        $sucursal = null;
        $primerDetalle = $compra->detalles->first();
        if ($primerDetalle && $primerDetalle->lote) {
            $inventario = $primerDetalle->lote->inventarioSucuralLotes()->first();
            if ($inventario) {
                $sucursal = Sucursal::find($inventario->sucursal_id);
            }
        }

        $pdf = Pdf::loadView('admin.compras.pdf.nota_compra', [
            'compra' => $compra,
            'detalles' => $compra->detalles,
            'sucursal' => $sucursal
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download('nota_compra_'.$compra->id.'.pdf');
    }
    //se usa para guardar los detalles de compra desde el carrito de sesion
    private function guardarDetallesDesdeCarrito(Compra $compra)
    {
        $carritoKey = 'carrito_compra_' . $compra->id;
        $carrito = session($carritoKey, []);

        if (empty($carrito)) {
            return;
        }

        $totalCompra = 0;

        foreach ($carrito as $item) {
            // Verificar si ya existe un detalle para este producto
            $detalleExistente = DetalleCompra::where('compra_id', $compra->id)
                ->where('producto_id', $item['producto_id'])
                ->first();

            if ($detalleExistente) {
                // Actualizar cantidad y subtotal del detalle existente
                $nuevaCantidad = $detalleExistente->cantidad + $item['cantidad'];
                $detalleExistente->update([
                    'cantidad' => $nuevaCantidad,
                    'subtotal' => $nuevaCantidad * $detalleExistente->precio_unitario
                ]);
            } else {
                // Crear nuevo detalle (con lote_id = null hasta recibir productos)
                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $item['producto_id'],
                    'lote_id' => null,
                    'precio_unitario' => $item['precio_unitario'],
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $item['subtotal']
                ]);
            }

            $totalCompra += $item['subtotal'];
        }

        // Actualizar total de la compra
        $compra->update([
            'total' => $totalCompra,
            'estado' => 'enviado al proveedor'
        ]);

        // Limpiar carrito de sesión después de guardar
        session()->forget($carritoKey);
    }







    // public function enviarWhatsapp(Compra $compra)
    // {
    //     $proveedor = $compra->proveedor;

    //     if (!$proveedor) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Proveedor no encontrado.'
    //         ], 404);
    //     }

    //     // Verificar si hay carrito en sesión y guardar en DB si existe
    //     $carritoKey = 'carrito_compra_' . $compra->id;
    //     $carrito = session($carritoKey, []);

    //     if (!empty($carrito)) {
    //         $this->guardarDetallesDesdeCarrito($compra);
    //         $compra->load('detalles.producto');
    //     }

    //     // Limpiar teléfono
    //     $telefonoRaw = $proveedor->telefono ?? '';
    //     $telefonoSoloDigitos = preg_replace('/\D+/', '', (string) $telefonoRaw);

    //     if (empty($telefonoSoloDigitos)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Teléfono del proveedor no disponible.'
    //         ], 400);
    //     }

    //     $codigoPais = '591';
    //     $telefonoFinal = strpos($telefonoSoloDigitos, $codigoPais) === 0 ? $telefonoSoloDigitos : $codigoPais . $telefonoSoloDigitos;

    //     // CONSTRUIR MENSAJE usando detalles de DB o carrito
    //     $mensaje = "*SOLICITUD DE COMPRA*\n\n";
    //     $mensaje .= "Estimado proveedor:\n";
    //     $mensaje .= "*{$proveedor->nombre}*\n\n";
    //     $mensaje .= "Detalle de productos:\n\n";

    //     $total = 0;

    //     if ($compra->detalles->isNotEmpty()) {
    //         foreach ($compra->detalles as $detalle) {
    //             $producto = $detalle->producto->nombre ?? 'Producto';
    //             $cantidad = $detalle->cantidad;
    //             $precio = $detalle->precio_unitario ?? 0;
    //             $subtotal = $cantidad * $precio;
    //             $total += $subtotal;

    //             $mensaje .= "• $producto\n";
    //             $mensaje .= "  Cantidad: $cantidad\n";
    //             $mensaje .= "  Precio: Bs " . number_format($precio, 2) . "\n";
    //             $mensaje .= "  Subtotal: Bs " . number_format($subtotal, 2) . "\n\n";
    //         }
    //     } else {
    //         // Fallback: usar carrito temporal (ya debería haberse guardado)
    //         $carrito = session($carritoKey, []);
    //         if (empty($carrito)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No hay productos en el carrito para enviar.'
    //             ], 400);
    //         }

    //         foreach ($carrito as $item) {
    //             $producto = $item['producto_nombre'] ?? 'Producto';
    //             $cantidad = $item['cantidad'] ?? 0;
    //             $precio = $item['precio_unitario'] ?? 0;
    //             $subtotal = $item['subtotal'] ?? ($cantidad * $precio);
    //             $total += $subtotal;

    //             $mensaje .= "• $producto\n";
    //             $mensaje .= "  Cantidad: $cantidad\n";
    //             $mensaje .= "  Precio: Bs " . number_format($precio, 2) . "\n";
    //             $mensaje .= "  Subtotal: Bs " . number_format($subtotal, 2) . "\n\n";
    //         }
    //     }

    //     $mensaje .= "-----------------------\n";
    //     $mensaje .= "*TOTAL: Bs " . number_format($total, 2) . "*\n\n";
    //     $mensaje .= "Por favor confirmar disponibilidad.\n";
    //     $mensaje .= "Gracias.";

    //     $mensajeCodificado = urlencode($mensaje);

    //     $userAgent = request()->header('User-Agent');
    //     $isMobile = preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $userAgent);

    //     if ($isMobile) {
    //         $url = "https://wa.me/$telefonoFinal?text=$mensajeCodificado";
    //     } else {
    //         $url = "https://web.whatsapp.com/send?phone=$telefonoFinal&text=$mensajeCodificado";
    //     }

    //     // El estado ya se actualiza en guardarDetallesDesdeCarrito()

    //     return response()->json([
    //         'success' => true,
    //         'url' => $url,
    //         'message' => 'Redirigiendo a WhatsApp...'
    //     ]);
    // }
    public function generarPdf(Compra $compra)
    {
        $productos = $compra->detalles->isNotEmpty()
            ? $compra->detalles
            : session('carrito_compra_' . $compra->id, []);

        $pdf = Pdf::loadView('admin.compras.pdf.pedido', [
            'compra' => $compra,
            'productos' => $productos
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download('pedido_compra_'.$compra->id.'.pdf');
    }
}
