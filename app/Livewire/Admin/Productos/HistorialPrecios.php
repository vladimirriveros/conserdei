<?php

namespace App\Livewire\Admin\Productos;

use App\Models\HistorialPrecio;
use App\Models\Producto;
use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class HistorialPrecios extends Component
{
    use WithPagination;

    public $productoId;
    public $fechaInicio;
    public $fechaFin;
    public $search = '';

    protected $queryString = ['fechaInicio', 'fechaFin', 'search'];

    public function mount($productoId)
    {
        $this->productoId = $productoId;
    }

    public function getProductoProperty()
    {
        return Producto::find($this->productoId);
    }

    public function render()
    {
        $producto = $this->producto;

        if (!$producto) {
            return view('livewire.admin.productos.historial-precios', [
                'historial' => collect([]),
                'producto' => null
            ]);
        }

        $historial = HistorialPrecio::where('producto_id', $this->productoId)
            ->with(['user', 'compra'])
            ->when($this->fechaInicio, function ($query) {
                return $query->whereDate('created_at', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($query) {
                return $query->whereDate('created_at', '<=', $this->fechaFin);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.productos.historial-precios', [
            'historial' => $historial,
            'producto' => $producto
        ]);
    }

    public function limpiarFiltros()
    {
        $this->fechaInicio = null;
        $this->fechaFin = null;
        $this->search = '';
        $this->resetPage();
    }

    public function generarPDF()
    {
        $producto = $this->producto;

        if (!$producto) {
            session()->flash('error', 'Producto no encontrado');
            return;
        }

        // Obtener todos los registros sin paginación para el PDF
        $historial = HistorialPrecio::where('producto_id', $this->productoId)
            ->with(['user', 'compra'])
            ->when($this->fechaInicio, function ($query) {
                return $query->whereDate('created_at', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($query) {
                return $query->whereDate('created_at', '<=', $this->fechaFin);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'producto' => $producto,
            'historial' => $historial,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'fechaGeneracion' => now(),
            'totalRegistros' => $historial->count(),
            'usuarioGenerador' => Auth::user()->name ?? 'Sistema'
        ];

        $pdf = PDF::loadView('pdf.historial-precios', $data);
        $pdf->setPaper('A4', 'landscape');

        $nombreArchivo = 'historial_precios_' . $producto->codigo . '_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }
}
