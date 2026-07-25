<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\TipoCambio;
use App\Models\HistorialPrecioVenta;
// use Illuminate\Container\Attributes\Storage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\HistorialStockProducto;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Producto::all();
        return view('admin.productos.index', compact('productos'));
        // return response()->json($productos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();

        // Generar el siguiente código automático
        $ultimoProducto = Producto::orderBy('id', 'desc')->first();

        if ($ultimoProducto) {
            // Extraer el número del último código (ej: PROD0001 -> 1)
            $ultimoCodigo = $ultimoProducto->codigo;
            $numero = intval(substr($ultimoCodigo, 4)); // Quitar "PROD" y convertir a número
            $nuevoNumero = $numero + 1;
        } else {
            $nuevoNumero = 1;
        }

        // Formatear con ceros a la izquierda (PROD0001, PROD0002, etc.)
        $nuevoCodigo = 'PROD' . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);

        return view('admin.productos.create', compact('categorias', 'nuevoCodigo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'codigo' => 'required|string|max:50|unique:productos,codigo',
            'nombre' => 'required|string|max:100',
            'marca' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'porcentaje' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'unidad_medida' => 'required|string|max:20',
            'norma' => 'nullable|string|max:100',
            'presion' => 'nullable|string|max:50',
            'diametro' => 'nullable|string|max:50',
        ]);

        $producto = new Producto();
        $producto->categoria_id = $request->categoria_id;
        $producto->codigo = $request->codigo;

        // Convertir a mayúsculas
        $producto->nombre = mb_strtoupper($request->nombre, 'utf-8');
        $producto->marca = mb_strtoupper($request->marca ?? '', 'utf-8');
        $producto->descripcion = mb_strtoupper($request->descripcion ?? '', 'utf-8');

        // Asignar campos de plomería (también en mayúsculas)
        $producto->norma = mb_strtoupper($request->norma ?? '', 'utf-8');
        $producto->presion = mb_strtoupper($request->presion ?? '', 'utf-8');
        $producto->diametro = mb_strtoupper($request->diametro ?? '', 'utf-8');

        // Imagen
        if ($request->hasFile('imagen')) {
            $producto->imagen = $request->file('imagen')->store('images/productos', 'public');
        } else {
            $producto->imagen = 'images/productos/conserdei.png';
        }

        $producto->precio_compra = $request->precio_compra;
        $producto->precio_venta = $request->precio_venta;
        $producto->porcentaje_ganancia = $request->porcentaje;
        $producto->stock_minimo = $request->stock_minimo;
        $producto->stock_maximo = $request->stock_maximo;
        $producto->unidad_medida = $request->unidad_medida;
        $producto->estado = false;

        $producto->save();

        return redirect()->route('productos.index')
            ->with('mensaje', 'Producto creado exitosamente.')
            ->with('icono', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // echo "Mostrar producto con ID: " . $id;
        $producto = Producto::findOrFail($id);
        return view('admin.productos.show', compact('producto'));
    }

    /**
     * S    how the form for editing the specified resource.
     */
    public function edit($id)
    {
        // echo "Editar producto con ID: " . $id;
        $producto = Producto::findOrFail($id);
        $categorias = Categoria::all();
        return view('admin.productos.edit', compact('producto', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'codigo' => 'required|string|max:50|unique:productos,codigo,' . $id,
            'nombre' => 'required|string|max:100',
            'marca' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'porcentaje' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'unidad_medida' => 'required|string|max:20',
            'estado' => 'required|boolean',
            'norma' => 'nullable|string|max:100',
            'presion' => 'nullable|string|max:50',
            'diametro' => 'nullable|string|max:50',
        ]);

        // Validación adicional para plomería
        $categoria = Categoria::find($request->categoria_id);
        if ($categoria && $categoria->nombre === 'PLOMERIA') {
            $request->validate([
                'norma' => 'required|string|max:100'
            ]);
        }

        $producto = Producto::findOrFail($id);

        // === NUEVO: DETECTAR CAMBIO EN PRECIO VENTA ===
        $precioVentaAnterior = $producto->precio_venta;
        $precioVentaNuevo = $request->precio_venta;
        $cambioPrecioVenta = ($precioVentaAnterior != $precioVentaNuevo);

        // === INICIO: DETECTAR CAMBIOS EN STOCK MINIMO Y MAXIMO ===
        $stockMinimoAnterior = $producto->stock_minimo;
        $stockMaximoAnterior = $producto->stock_maximo;
        $stockMinimoNuevo = $request->stock_minimo;
        $stockMaximoNuevo = $request->stock_maximo;

        $cambioStockMinimo = ($stockMinimoAnterior != $stockMinimoNuevo);
        $cambioStockMaximo = ($stockMaximoAnterior != $stockMaximoNuevo);

        // Solo si hubo algún cambio en los stocks
        if ($cambioStockMinimo || $cambioStockMaximo) {
            // Validar que se haya proporcionado motivo si quieres obligatorio
            // (opcional, actualmente no es obligatorio)

            // Crear registro en historial
            HistorialStockProducto::create([
                'producto_id' => $producto->id,
                'stock_minimo_anterior' => $stockMinimoAnterior,
                'stock_minimo_nuevo' => $stockMinimoNuevo,
                'stock_maximo_anterior' => $stockMaximoAnterior,
                'stock_maximo_nuevo' => $stockMaximoNuevo,
                'user_id' => auth()->id(),
                'motivo' => $request->motivo_stock,
                'observaciones' => $request->observaciones_stock,
            ]);
        }
        // === FIN: DETECTAR CAMBIOS ===

        $producto->categoria_id = $request->categoria_id;
        $producto->codigo = $request->codigo;

        // Convertir a mayúsculas
        $producto->nombre = mb_strtoupper($request->nombre, 'utf-8');
        $producto->marca = mb_strtoupper($request->marca ?? '', 'utf-8');
        $producto->descripcion = mb_strtoupper($request->descripcion ?? '', 'utf-8');

        // Asignar campos de plomería (también en mayúsculas)
        $producto->norma = mb_strtoupper($request->norma ?? '', 'utf-8');
        $producto->presion = mb_strtoupper($request->presion ?? '', 'utf-8');
        $producto->diametro = mb_strtoupper($request->diametro ?? '', 'utf-8');

        // Asignar todos los campos (tu código existente)
        $producto->categoria_id = $request->categoria_id;
        $producto->codigo = $request->codigo;
        $producto->nombre = mb_strtoupper($request->nombre, 'utf-8');
        $producto->marca = mb_strtoupper($request->marca ?? '', 'utf-8');
        $producto->descripcion = mb_strtoupper($request->descripcion ?? '', 'utf-8');
        $producto->norma = mb_strtoupper($request->norma ?? '', 'utf-8');
        $producto->presion = mb_strtoupper($request->presion ?? '', 'utf-8');
        $producto->diametro = mb_strtoupper($request->diametro ?? '', 'utf-8');

        // Imagen
        if ($request->hasFile('imagen')) {
            if ($producto->imagen && $producto->imagen !== 'images/productos/conserdei.png') {
                Storage::disk('public')->delete($producto->imagen);
            }
            $producto->imagen = $request->file('imagen')->store('images/productos', 'public');
        }

        $producto->precio_compra = $request->precio_compra;
        $producto->precio_venta = $request->precio_venta;
        $producto->porcentaje_ganancia = $request->porcentaje;
        $producto->stock_minimo = $request->stock_minimo;
        $producto->stock_maximo = $request->stock_maximo;
        $producto->unidad_medida = $request->unidad_medida;
        $producto->estado = $request->estado;

        $producto->save();

        // === NUEVO: GUARDAR HISTORIAL SI CAMBIÓ PRECIO VENTA ===
        if ($cambioPrecioVenta) {
            $tipoCambioActivo = TipoCambio::getActivo();

            if ($tipoCambioActivo) {
                $tcOficial = TipoCambio::getOficial();
                $tcOficialValor = $tcOficial ? $tcOficial->precio_dolar : 6.96; // Fallback seguro

                $tcAplicado = TipoCambio::calcularTipoCambioAplicado(
                    $precioVentaNuevo,
                    $producto->precio_compra,
                    $producto->porcentaje_ganancia,
                    $tcOficialValor
                );

                HistorialPrecioVenta::create([
                    'producto_id' => $producto->id,
                    'precio_venta_anterior' => $precioVentaAnterior,
                    'precio_venta_nuevo' => $precioVentaNuevo,
                    'tipo_cambio_aplicado' => $tcAplicado,
                    'user_id' => auth()->id(),
                    'motivo' => 'Edición manual de precio de venta',
                ]);
            }
        }

        return redirect()->route('productos.index')
            ->with('mensaje', 'Producto actualizado exitosamente.')
            ->with('icono', 'success');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // echo "Eliminar producto con ID: " . $id;
        $producto = Producto::findOrFail($id);

        // Verificar si la imagen no es la por defecto
        if ($producto->imagen && $producto->imagen !== 'images/productos/conserdei.png') {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return redirect()->route('productos.index')
        ->with('mensaje', 'Producto eliminado exitosamente.')
        ->with('icono', 'success');
    }

    // DESACTIVAR PRODUCTO DESDE INVENTARIO
    public function desactivar(Producto $producto)
    {
        $producto->estado = false;
        $producto->save();

        return back()
        ->with('mensaje', 'Producto desactivado correctamente')
        ->with('icono', 'success');;
    }
    public function verificarCodigo(Request $request)
    {
        $codigo = $request->input('codigo');
        $id = $request->input('id'); // Para edición, excluir el producto actual

        $query = Producto::where('codigo', $codigo);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        $existe = $query->exists();

        return response()->json(['existe' => $existe]);
    }

    public function ultimoCodigo()
    {
        $ultimoProducto = Producto::orderBy('id', 'desc')->first();

        if ($ultimoProducto) {
            $ultimoCodigo = $ultimoProducto->codigo;
            $numero = intval(substr($ultimoCodigo, 4));
        } else {
            $numero = 0;
        }

        return response()->json(['ultimo_numero' => $numero]);
    }

    public function historialPrecios(Producto $producto)
    {
        return view('admin.productos.historial-precios', [
            'producto' => $producto
        ]);
    }
}
