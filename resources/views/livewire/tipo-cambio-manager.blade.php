<div>
    {{-- SECCION DE TARJETA DE INFORMACIÓN PRINCIPAL --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><b><i class="fas fa-info-circle"></i> Información de Tipos de Cambio</b></h3>
        </div>
        {{-- TARJETA DE INFORMACIÓN PRINCIPAL --}}
        <div class="card-body">
            <div class="row">
                {{-- TIPO DE CAMBIO OFICIAL --}}
                <div class="col-md-6">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-bank"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tipo de Cambio OFICIAL</span>
                            <span class="info-box-number">
                                @if ($tipoCambioOficial)
                                    1 USD = {{ number_format($tipoCambioOficial->precio_dolar, 2) }} Bs
                                    <br>
                                    <small>Actualizado:
                                        {{ $tipoCambioOficial->updated_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="text-warning">No definido</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- TIPO DE CAMBIO ACTIVO --}}
                <div class="col-md-6">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tipo de Cambio ACTIVO (visualización)</span>
                            <span class="info-box-number">
                                @if ($tipoCambioActivo)
                                    1 USD = {{ number_format($tipoCambioActivo->precio_dolar, 2) }} Bs
                                    <br>
                                    <small>
                                        @if ($tipoCambioActivo->is_oficial)
                                            <span class="badge badge-primary">OFICIAL</span>
                                        @else
                                            <span class="badge badge-warning">ALTERNATIVO</span>
                                        @endif
                                    </small>
                                @else
                                    <span class="text-warning">Ninguno activo</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE TIPOS DE CAMBIO --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><b>Listado de Tipos de Cambio</b></h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary" wire:click="openCreateModal">
                    <i class="fas fa-plus"></i> Nuevo tipo de cambio
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Tipo de Cambio</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th class="text-center" colspan="4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tiposCambio as $index => $cambio)
                        <tr class="{{ $cambio->estado ? 'table-success' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>
                                    1 USD = {{ number_format($cambio->precio_dolar, 2) }} Bs
                                </strong>
                                @if ($cambio->is_oficial)
                                    <span class="badge badge-primary">OFICIAL</span>
                                @endif
                            </td>
                            <td>{{ $cambio->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($cambio->is_oficial)
                                    <span class="badge badge-primary">Oficial</span>
                                @else
                                    <span class="badge badge-secondary">Alternativo</span>
                                @endif
                            </td>
                            <td>
                                @if ($cambio->estado)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> ACTIVO
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-times-circle"></i> Inactivo
                                    </span>
                                @endif
                            </td>

                            {{-- Botón: Establecer como OFICIAL --}}
                            <td class="text-center" style="width: 10%">
                                @if (!$cambio->is_oficial)
                                    <button type="button" class="btn btn-primary btn-sm btn-block"
                                        wire:click="confirmOficial({{ $cambio->id }})"
                                        <i class="fas fa-star"></i> Asignar como TC Oficial
                                    </button>
                                @else
                                    <span class="badge badge-success">TC oficial</span>
                                @endif
                            </td>

                            {{-- Botón: ACTUALIZAR AL NUEVO TIPO DE CAMBIO PARA EL ALGORITMO --}}
                            <td class="text-center" style="width: 15%">
                                @if (!$tipoCambioOficial)
                                    <button type="button" class="btn btn-secondary btn-sm btn-block" disabled
                                        title="No hay un tipo de cambio oficial definido. Establece uno primero.">
                                        <i class="fas fa-exclamation-triangle"></i> Sin oficial
                                    </button>
                                @else
                                    {{-- Solo mostrar si NO es el tipo de cambio activo actual --}}
                                    @if (!$cambio->estado)
                                        <button type="button" class="btn btn-warning btn-sm btn-block"
                                            wire:click="openUpdatePricesModal({{ $cambio->precio_dolar }}, {{ $cambio->id }})">
                                            <i class="fas fa-calculator"></i> Cambiar a este precio los productos
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success btn-sm btn-block" disabled>
                                            <i class="fas fa-check-circle"></i> Tipo activo actual
                                        </button>
                                    @endif
                                @endif
                            </td>

                            {{-- Botones de editar/eliminar --}}
                            <td class="text-center" style="width: 15%">
                                <div class="btn-group" role="group">
                                    @if (!$cambio->is_oficial && !$cambio->estado)
                                        <button type="button" class="btn btn-warning btn-sm"
                                            wire:click="edit({{ $cambio->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-ban"></i>
                                        </button
                                    @endif

                                    @if (!$cambio->is_oficial && !$cambio->estado)
                                        <button type="button" class="btn btn-danger btn-sm"
                                            wire:click="confirmDelete({{ $cambio->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CREAR/EDITAR --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header {{ $editingId ? 'bg-warning' : 'bg-primary' }}">
                        <h5 class="modal-title">
                            <i class="fas {{ $editingId ? 'fa-edit' : 'fa-plus' }}"></i>
                            {{ $editingId ? 'Editar Tipo de Cambio' : 'Nuevo Tipo de Cambio' }}
                        </h5>
                        {{-- cambia la variable showModal a false para cerrar el modal --}}
                        <button type="button" class="close" wire:click="$set('showModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Precio del Dolar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">1 USD =</span>
                                    </div>
                                    <input type="number" step="0.01" min="0.01"
                                        class="form-control
                                        @error('precio') is-invalid @enderror"
                                        wire:model="precio" placeholder="Ej: 6.96">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Bs</span>
                                    </div>
                                </div>
                                @error('precio')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Los nuevos tipos de cambio se crean como <strong>INACTIVOS y NO OFICIALES</strong>.
                            </div>
                        </div>

                        {{-- botones de cancelar y guardar --}}
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                Cancelar
                            </button>
                            <button type="submit" class="btn {{ $editingId ? 'btn-warning' : 'btn-primary' }}">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ACTUALIZAR PRECIOS --}}
    @if ($showUpdatePricesModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-calculator"></i> Actualizar precios de venta
                        </h5>
                        <button type="button" class="close" wire:click="$set('showUpdatePricesModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Tipo de cambio seleccionado:</strong><br>
                            1 USD = {{ number_format($selectedTipoCambioPrecio, 2) }} Bs
                        </div>

                        <div class="form-group">
                            <label>¿A qué productos aplicar?</label>
                            {{-- select para seleccionar TODOS o SOLO los productos filtrados --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card p-3 text-center">
                                        <label>
                                            <input type="radio" value="todos" wire:model.live="aplicarA">
                                            TODOS los productos
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card p-3 text-center">
                                        <label>
                                            <input type="radio" value="seleccionados" wire:model.live="aplicarA"
                                                checked>
                                            Productos filtrados
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($aplicarA === 'seleccionados')
                            {{-- seleccionar los productos todos o filtrados por categorias y marcas --}}
                            <div class="row">

                                {{-- seleccionar categorias --}}
                                <div class="col-md-6">
                                    <label>Categorías</label>
                                    {{-- selecciona multiples categorias y manda sus ids --}}
                                    <select wire:model.live="selectedCategories" multiple class="form-control" size="8">
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Seleccionadas: {{ count($selectedCategories) }}</small>
                                        @if (count($selectedCategories) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                wire:click="clearCategories">
                                                <i class="fas fa-trash-alt"></i> Limpiar categorías
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- seleccionar marcas --}}
                                <div class="col-md-6">
                                    <label>Marcas</label>
                                    <select wire:model.live="selectedBrands" multiple class="form-control"
                                        size="8">
                                        @foreach ($marcas as $marca)
                                            <option value="{{ $marca }}">{{ $marca }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Seleccionadas: {{ count($selectedBrands) }}</small>
                                        @if (count($selectedBrands) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                wire:click="clearBrands">
                                                <i class="fas fa-trash-alt"></i> Limpiar marcas
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Botón para limpiar TODO --}}
                            @if (count($selectedCategories) > 0 || count($selectedBrands) > 0)
                                <div class="mt-3 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        wire:click="clearAllFilters">
                                        <i class="fas fa-eraser"></i> Limpiar todos los filtros
                                    </button>
                                </div>
                            @endif

                            {{-- Resumen de lo seleccionado --}}
                            @if (count($selectedCategories) > 0 || count($selectedBrands) > 0)
                                <div class="alert alert-success mt-3">
                                    <strong><i class="fas fa-filter"></i> Resumen de filtros:</strong><br>
                                    @if (count($selectedCategories) > 0)
                                        📁 Categorías: {{ count($selectedCategories) }} seleccionada(s)<br>
                                    @endif
                                    @if (count($selectedBrands) > 0)
                                        🏷️ Marcas: {{ count($selectedBrands) }} seleccionada(s)
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showUpdatePricesModal', false)">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="updatePrices">
                            <i class="fas fa-calculator"></i> Actualizar precios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Script para manejar mensajes con SweetAlert --}}
    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('mensaje', (event) => {
                    Swal.fire({
                        title: event.message.includes('✅') ? 'Éxito' : (event.message.includes('❌') ?
                            'Error' : 'Información'),
                        html: event.message,
                        icon: event.icon,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar' // ← AGREGA ESTA LÍNEA
                    });
                });
            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('confirmar-eliminacion', (event) => {
                    Swal.fire({
                        title: '¿Eliminar tipo de cambio?',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('delete', { id: event.id });
                        }
                    });
                });
            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('confirmar-oficial', (event) => {
                    Swal.fire({
                        title: '¿Establecer como tipo de cambio oficial?',
                        text: 'Esta acción actualizará el tipo de cambio oficial.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, establecer como oficial',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('setOficial', { id: event.id });
                        }
                    });
                });
            });
        </script>
    @endpush
</div>
