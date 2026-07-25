<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Lote;
use App\Models\InventarioSucuralLote;
use App\Models\Sucursal;
use App\Models\Compra;  // 👈 Agregar
use App\Models\Salida;  // 👈 Agregar
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $search = $request->input('search');

        // Obtener la PRIMERA sucursal (asumimos que solo hay una)
        $sucursal = Sucursal::first();

        if (!$sucursal) {
            return redirect()->back()->with('mensaje', 'No hay sucursales registradas')
                ->with('icono', 'error');
        }

        // Obtener movimientos
        $movimientos = MovimientoInventario::with('producto', 'lote', 'sucursal');

        // Filtro por fechas
        if ($fecha_desde && $fecha_hasta) {
            $fecha_desde_obj = Carbon::parse($fecha_desde)->startOfDay();
            $fecha_hasta_obj = Carbon::parse($fecha_hasta)->endOfDay();
            $movimientos = $movimientos->whereBetween('fecha', [$fecha_desde_obj, $fecha_hasta_obj]);

            Log::info('Aplicando filtro de fechas de ENTRADA:', [
                'desde' => $fecha_desde_obj->format('Y-m-d H:i:s'),
                'hasta' => $fecha_hasta_obj->format('Y-m-d H:i:s')
            ]);
        }

        // Filtro por búsqueda general
        if ($search) {
            $movimientos = $movimientos->where(function($query) use ($search) {
                $query->where('tipo_movimiento', 'LIKE', "%{$search}%")
                    ->orWhere('observaciones', 'LIKE', "%{$search}%")
                    ->orWhereHas('producto', function($q) use ($search) {
                        $q->where('nombre', 'LIKE', "%{$search}%")
                            ->orWhere('codigo', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('lote', function($q) use ($search) {
                        $q->where('codigo_lote', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('sucursal', function($q) use ($search) {
                        $q->where('nombre', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Ordenar por fecha descendente
        $movimientos = $movimientos->orderBy('fecha', 'desc')->get();

        // 👇 CARGAR USUARIOS RELACIONADOS DESDE COMPRAS Y SALIDAS
        $this->cargarUsuariosRelacionados($movimientos);

        // Si es una petición AJAX, devolver solo el HTML de la tabla
        if ($request->ajax()) {
            $html = view('admin.inventario.movimientos.partials.tabla', compact('movimientos'))->render();
            return response()->json([
                'html' => $html,
                'total' => $movimientos->count()
            ]);
        }

        return view('admin.inventario.movimientos.index', compact('movimientos'));
    }

    /**
     * Cargar los usuarios relacionados desde compras y salidas
     */
    private function cargarUsuariosRelacionados($movimientos)
    {
        // Colecciones para almacenar IDs únicos
        $compraIds = [];
        $salidaIds = [];

        // Extraer IDs de las observaciones
        foreach ($movimientos as $movimiento) {
            // Buscar formato COMPRA_ID:123
            if (preg_match('/COMPRA_ID:(\d+)/', $movimiento->observaciones, $matches)) {
                $compraIds[] = (int)$matches[1];
            }
            // Buscar formato SALIDA_ID:456
            elseif (preg_match('/SALIDA_ID:(\d+)/', $movimiento->observaciones, $matches)) {
                $salidaIds[] = (int)$matches[1];
            }
        }

        // Cargar compras con sus usuarios (solo una consulta)
        $compras = [];
        if (!empty($compraIds)) {
            $compras = Compra::with('user')
                ->whereIn('id', array_unique($compraIds))
                ->get()
                ->keyBy('id');
            Log::info('Compras cargadas para usuarios:', ['cantidad' => $compras->count()]);
        }

        // Cargar salidas con sus usuarios (solo una consulta)
        $salidas = [];
        if (!empty($salidaIds)) {
            $salidas = Salida::with('user')
                ->whereIn('id', array_unique($salidaIds))
                ->get()
                ->keyBy('id');
            Log::info('Salidas cargadas para usuarios:', ['cantidad' => $salidas->count()]);
        }

        // Asignar el usuario a cada movimiento
        foreach ($movimientos as $movimiento) {
            $movimiento->usuario_relacionado = null;
            $movimiento->nombre_usuario = 'N/A';
            $movimiento->tipo_referencia = null;
            $movimiento->referencia_id = null;

            // Buscar si es una compra
            if (preg_match('/COMPRA_ID:(\d+)/', $movimiento->observaciones, $matches)) {
                $compraId = (int)$matches[1];
                if (isset($compras[$compraId])) {
                    $movimiento->usuario_relacionado = $compras[$compraId]->user;
                    $movimiento->nombre_usuario = $compras[$compraId]->user?->name ?? 'N/A';
                    $movimiento->tipo_referencia = 'compra';
                    $movimiento->referencia_id = $compraId;
                }
            }
            // Buscar si es una salida
            elseif (preg_match('/SALIDA_ID:(\d+)/', $movimiento->observaciones, $matches)) {
                $salidaId = (int)$matches[1];
                if (isset($salidas[$salidaId])) {
                    $movimiento->usuario_relacionado = $salidas[$salidaId]->user;
                    $movimiento->nombre_usuario = $salidas[$salidaId]->user?->name ?? 'N/A';
                    $movimiento->tipo_referencia = 'salida';
                    $movimiento->referencia_id = $salidaId;
                }
            }
            // Fallback: Intentar extraer de observaciones antiguas que ya tenían el nombre
            elseif (preg_match('/Usuario: ([^-]+)/', $movimiento->observaciones, $matches)) {
                $movimiento->nombre_usuario = trim($matches[1]);
            }
        }
    }

    /**
     * Sincronizar inventario y movimientos para la única sucursal
     */
    private function sincronizarInventarioYMovimientos($sucursalId)
    {
        // Obtener todos los lotes
        $lotes = Lote::with('producto')->get();

        foreach ($lotes as $lote) {
            // PASO 1: Verificar/Crear inventario en sucursal
            $inventario = InventarioSucuralLote::firstOrCreate(
                [
                    'lote_id' => $lote->id,
                    'sucursal_id' => $sucursalId
                ],
                [
                    'cantidad_en_sucursal' => $lote->cantidad_actual > 0 ? $lote->cantidad_actual : $lote->cantidad_inicial
                ]
            );

            // PASO 2: Verificar/Crear movimiento de entrada
            $movimientoEntrada = MovimientoInventario::where('lote_id', $lote->id)
                ->where('tipo_movimiento', 'Entrada')
                ->first();

            if (!$movimientoEntrada) {
                MovimientoInventario::create([
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'sucursal_id' => $sucursalId,
                    'tipo_movimiento' => 'Entrada',
                    'cantidad' => $lote->cantidad_inicial ?: $lote->cantidad_actual,
                    'fecha' => $lote->fecha_entrada ?? now(),
                    'observaciones' => 'Entrada inicial por creación de lote'
                ]);
            }
        }
    }

    /**
     * Método para forzar sincronización manual
     */
    public function sincronizarManual()
    {
        $sucursal = Sucursal::first();

        if (!$sucursal) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sucursales registradas'
            ]);
        }

        try {
            $this->sincronizarInventarioYMovimientos($sucursal->id);

            return response()->json([
                'success' => true,
                'message' => 'Inventario y movimientos sincronizados correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar PDF del Kardex (movimientos de inventario) con filtros aplicados
     */
    public function generarPDF(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $search = $request->input('search');

        Log::info('Generando PDF con filtros:', [
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'search' => $search
        ]);

        // Obtener movimientos con los mismos filtros que el index
        $movimientos = MovimientoInventario::with('producto', 'lote', 'sucursal');

        // Filtro por fechas
        if ($fecha_desde && $fecha_hasta) {
            $fecha_desde_obj = Carbon::parse($fecha_desde)->startOfDay();
            $fecha_hasta_obj = Carbon::parse($fecha_hasta)->endOfDay();
            $movimientos = $movimientos->whereBetween('fecha', [$fecha_desde_obj, $fecha_hasta_obj]);
        }

        // Filtro por búsqueda general
        if ($search && $search !== '') {
            $movimientos = $movimientos->where(function($query) use ($search) {
                $query->where('tipo_movimiento', 'LIKE', "%{$search}%")
                    ->orWhere('observaciones', 'LIKE', "%{$search}%")
                    ->orWhereHas('producto', function($q) use ($search) {
                        $q->where('nombre', 'LIKE', "%{$search}%")
                            ->orWhere('codigo', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('lote', function($q) use ($search) {
                        $q->where('codigo_lote', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('sucursal', function($q) use ($search) {
                        $q->where('nombre', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Ordenar por fecha descendente
        $movimientos = $movimientos->orderBy('fecha', 'desc')->get();

        // 👇 CARGAR USUARIOS TAMBIÉN PARA EL PDF
        $this->cargarUsuariosRelacionados($movimientos);

        // Calcular estadísticas
        $total_entradas = $movimientos->where('tipo_movimiento', 'Entrada')->sum('cantidad');
        $total_salidas = $movimientos->where('tipo_movimiento', 'Salida')->sum('cantidad');
        $total_movimientos = $movimientos->count();

        $data = [
            'movimientos' => $movimientos,
            'fecha_desde' => $fecha_desde ? Carbon::parse($fecha_desde)->format('d/m/Y') : 'Todo',
            'fecha_hasta' => $fecha_hasta ? Carbon::parse($fecha_hasta)->format('d/m/Y') : 'Todo',
            'search' => $search ?: 'Sin filtro',
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'total_entradas' => $total_entradas,
            'total_salidas' => $total_salidas,
            'total_movimientos' => $total_movimientos,
            'usuario' => Auth::user()->name ?? 'Sistema'
        ];

        $pdf = Pdf::loadView('admin.inventario.movimientos.pdf', $data);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true
        ]);

        $nombre_archivo = 'kardex-' . date('Y-m-d') . '.pdf';

        return $pdf->download($nombre_archivo);
    }
}
