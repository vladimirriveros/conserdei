<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\TipoCambio;
use App\Models\HistorialPrecioVenta;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;  // ← Agrega esto en la parte superior, después de namespace

class TipoCambioManager extends Component
{
    // Propiedades para el listado
    public $tiposCambio;
    public $tipoCambioOficial;
    public $tipoCambioActivo;
    public $categorias;
    public $marcas;

    // Propiedades para el modal de crear/editar
    public $showModal = false;
    public $editingId = null;
    public $precio = '';

    // Propiedades para el modal de actualizar precios
    public $showUpdatePricesModal = false;
    public $selectedTipoCambioId = null;
    public $selectedTipoCambioPrecio = null;
    public $aplicarA = 'seleccionados';
    public $selectedCategories = [];
    public $selectedBrands = [];

    protected $rules = [
        'precio' => 'required|numeric|min:0.01',
    ];

    public function mount()
    {
        $this->loadData();
    }
    public function loadData()
    {
        $this->tiposCambio = TipoCambio::orderBy('created_at', 'desc')->get();
        $this->tipoCambioOficial = TipoCambio::getOficial();
        $this->tipoCambioActivo = TipoCambio::getActivo();
        $this->categorias = Categoria::orderBy('nombre')->get();
        $this->marcas = Producto::select('marca')
            ->distinct()
            ->whereNotNull('marca')
            ->where('marca', '!=', '')
            ->orderBy('marca')
            ->pluck('marca')
            ->toArray();
    }

    // ========== MODAL CREAR/EDITAR ==========// ==========// ==========
    public function openCreateModal()
    {
        $this->reset(['editingId', 'precio']);
        $this->showModal = true;
    }
    public function edit($id)
    {
        $tipoCambio = TipoCambio::findOrFail($id);
        $this->editingId = $id;
        $this->precio = $tipoCambio->precio_dolar;
        $this->showModal = true;
    }
    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            if ($this->editingId) {
                $tipoCambio = TipoCambio::findOrFail($this->editingId);
                $tipoCambio->update(['precio_dolar' => $this->precio]);
                $mensaje = 'Tipo de cambio actualizado exitosamente.';
            } else {
                TipoCambio::create([
                    'precio_dolar' => $this->precio,
                    'fecha' => now(),
                    'estado' => false,
                    'is_oficial' => false,
                ]);
                $mensaje = 'Tipo de cambio alternativo creado exitosamente.';
            }

            DB::commit();
            $this->showModal = false;

            // Recargar los datos actualizados
            $this->tiposCambio = TipoCambio::orderBy('created_at', 'desc')->get();
            $this->tipoCambioOficial = TipoCambio::getOficial();
            $this->tipoCambioActivo = TipoCambio::getActivo();

            $this->dispatch('mensaje', message: $mensaje, icon: 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mensaje', message: 'Error: ' . $e->getMessage(), icon: 'error');
        }
    }
    // ==========// ==========// ==========// ==========// ==========


    // ========== ELIMINAR ==========// ==========// ==========// ==========
    public function confirmDelete($id)
    {
        $this->dispatch('confirmar-eliminacion', id: $id);
    }
    #[On('delete')]
    public function delete($id)
    {
        $tipoCambio = TipoCambio::findOrFail($id);

        if ($tipoCambio->is_oficial) {
            $this->dispatch('mensaje', message: '❌ No se puede eliminar el tipo de cambio oficial. Debes establecer otro como oficial primero.', icon: 'error');
            return;
        }

        if ($tipoCambio->estado) {
            $this->dispatch('mensaje', message: '❌ No se puede eliminar el tipo de cambio activo.', icon: 'warning');
            return;
        }

        try {
            $tipoCambio->delete();
            $this->loadData();
            $this->dispatch('mensaje', message: '✅ Tipo de cambio eliminado exitosamente.', icon: 'success');
        } catch (\Exception $e) {
            $this->dispatch('mensaje', message: 'Error: ' . $e->getMessage(), icon: 'error');
        }
    }
    // ==========// ==========// ==========// ==========// ==========//


    // ========== BOTON TC OFICIAL ==========// ==========// ==========//==========
    public function confirmOficial($id)
    {
        $this->dispatch('confirmar-oficial', id: $id);
    }
    #[On('setOficial')]
    public function setOficial($id)
    {
        DB::beginTransaction();
        try {
            TipoCambio::where('is_oficial', true)->update(['is_oficial' => false]);
            $tipoCambio = TipoCambio::findOrFail($id);
            $tipoCambio->is_oficial = true;
            $tipoCambio->save();

            DB::commit();
            $this->loadData();
            $this->dispatch('mensaje', message: '✅ Tipo de cambio oficial actualizado a 1 USD = ' . number_format($tipoCambio->precio_dolar, 2) . ' Bs', icon: 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mensaje', message: 'Error: ' . $e->getMessage(), icon: 'error');
        }
    }
    // ==========// ==========// ==========// ==========// ==========


    // ==========BOTON MODAL ACTUALIZAR PRECIOS selecciona todos o cateorias y marcas===============
    public function openUpdatePricesModal($precio, $id)
    {
        $this->selectedTipoCambioId = $id;
        $this->selectedTipoCambioPrecio = $precio;
        $this->aplicarA = 'seleccionados';
        $this->selectedCategories = [];
        $this->selectedBrands = [];
        $this->showUpdatePricesModal = true;
    }
    // Método CLAVE - actualiza los precios según filtros

    public function updatePrices()
    {
        // Validar que si es "seleccionados", haya al menos un filtro
        if ($this->aplicarA === 'seleccionados' && empty($this->selectedCategories) && empty($this->selectedBrands)) {
            $this->dispatch('mensaje', message: 'Debes seleccionar al menos una categoría o una marca', icon: 'error');
            return;
        }

        DB::beginTransaction();

        try {
            $tipoCambioOficial = TipoCambio::getOficial();

            if (!$tipoCambioOficial) {
                $this->dispatch('mensaje', message: '❌ No hay un tipo de cambio oficial definido.', icon: 'error');
                return;
            }

            $nuevoTipoCambio = TipoCambio::findOrFail($this->selectedTipoCambioId);
            $factor = $nuevoTipoCambio->precio_dolar / $tipoCambioOficial->precio_dolar;

            // ============================================
            // 1. OBTENER PRODUCTOS SEGÚN FILTROS
            // ============================================
            $productosQuery = Producto::query();

            if ($this->aplicarA === 'seleccionados') {
                if (!empty($this->selectedCategories)) {
                    $productosQuery->whereIn('categoria_id', $this->selectedCategories);
                }
                if (!empty($this->selectedBrands)) {
                    $productosQuery->whereIn('marca', $this->selectedBrands);
                }
            }

            $productos = $productosQuery->get();

            $productosActualizados = 0;
            $productosOmitidos = 0;
            $userId = auth()->id();

            foreach ($productos as $producto) {
                // Calcular precio esperado con el nuevo tipo de cambio
                //COSTO AJUSTADO
                $precioBase = $producto->precio_compra * $factor;
                //MARGEN DE GANANCIA * COSTO AJUSTADO
                $precioEsperado = round($precioBase * (1 + $producto->porcentaje_ganancia / 100), 2);
                $precioActual = $producto->precio_venta;

                // Comparar con tolerancia 0.01 (si ya está actualizado, saltar)
                if (abs($precioActual - $precioEsperado) < 0.01) {
                    $productosOmitidos++;
                    continue; // Producto ya actualizado, no hacer nada
                }

                // Guardar el precio anterior ANTES de actualizar
                $precioAnterior = $producto->precio_venta;

                // Actualizar producto
                $producto->precio_venta = $precioEsperado;
                $producto->save();

                // Calcular tipo de cambio aplicado (usando el nuevo precio)
                $tcAplicado = TipoCambio::calcularTipoCambioAplicado(
                    $precioEsperado,
                    $producto->precio_compra,
                    $producto->porcentaje_ganancia,
                    // $nuevoTipoCambio->precio_dolar
                    $tipoCambioOficial->precio_dolar
                );

                // Guardar historial
                HistorialPrecioVenta::create([
                    'producto_id' => $producto->id,
                    'precio_venta_anterior' => $precioAnterior,
                    'precio_venta_nuevo' => $precioEsperado,
                    'tipo_cambio_aplicado' => $tcAplicado,
                    'user_id' => $userId,
                    'motivo' => 'Actualización masiva por TC: 1 USD = ' . number_format($nuevoTipoCambio->precio_dolar, 2) . ' Bs',
                ]);

                $productosActualizados++;
            }

            // ============================================
            // 2. ACTIVAR TIPO DE CAMBIO
            // ============================================
            TipoCambio::where('estado', true)->update(['estado' => false]);
            $nuevoTipoCambio->estado = true;
            $nuevoTipoCambio->save();

            DB::commit();

            $this->showUpdatePricesModal = false;
            $this->loadData();

            $mensaje = "✅ ACTUALIZACIÓN COMPLETA<br>";
            $mensaje .= "💰 Tipo de cambio: 1 USD = " . number_format($nuevoTipoCambio->precio_dolar, 2) . " Bs<br>";
            $mensaje .= "📦 Productos actualizados: {$productosActualizados}<br>";
            if ($productosOmitidos > 0) {
                $mensaje .= "⏭️ Productos omitidos (ya actualizados): {$productosOmitidos}<br>";
            }

            $this->dispatch('mensaje', message: $mensaje, icon: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mensaje', message: '❌ Error: ' . $e->getMessage(), icon: 'error');
        }
    }
    // ================================================================================
    // ========== LIMPIAR FILTROS ================================================================
    public function clearCategories()
    {
        $this->selectedCategories = [];
    }
    public function clearBrands()
    {
        $this->selectedBrands = [];
    }
    public function clearAllFilters()
    {
        $this->selectedCategories = [];
        $this->selectedBrands = [];
    }
    // ==========================================================================================

    public function updatedAplicarA()//todos o seleccionados
    {
        if ($this->aplicarA === 'todos') {
            $this->selectedCategories = [];
            $this->selectedBrands = [];
        }
    }

    public function render()
    {
        return view('livewire.tipo-cambio-manager');
    }
}
