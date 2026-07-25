@php
    use Spatie\Permission\Models\Permission;
@endphp
@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Permisos para: <strong>{{ $rol->name }}</strong></li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Información del Rol -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt"></i>
                        <b>Gestión de Permisos - Rol: {{ $rol->name }}</b>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">

                    @if($rol->name === 'admin')
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Rol Protegido</h5>
                            El rol <strong>"Administrador"</strong> tiene todos los permisos del sistema por defecto y no pueden ser modificados.
                        </div>
                    @else
                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-info-circle"></i> Información</h5>
                            Selecciona los permisos que deseas asignar al rol <strong>{{ $rol->name }}</strong>.
                            Puedes usar los botones de selección rápida para agilizar el proceso.
                        </div>

                        <!-- Botones de acción rápida (solo para roles no admin) -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success" id="seleccionar-todos">
                                        <i class="fas fa-check-double"></i> Seleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="deseleccionar-todos">
                                        <i class="fas fa-times"></i> Deseleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="expandir-todos">
                                        <i class="fas fa-expand-arrows-alt"></i> Expandir Todos
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" id="colapsar-todos">
                                        <i class="fas fa-compress-arrows-alt"></i> Colapsar Todos
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ url('/admin/roles/' . $rol->id) }}" method="POST" id="form-permisos">
                        @csrf

                        <!-- Tarjetas de permisos por módulo -->
                        <div class="row">
                            @php
                                $iconosModulos = [
                                    'Categorías' => 'fa-tags',
                                    'Sucursales' => 'fa-store',
                                    'Productos' => 'fa-boxes',
                                    'Proveedores' => 'fa-truck',
                                    'Compras' => 'fa-shopping-cart',
                                    'Inventario' => 'fa-warehouse',
                                    'Tipo de Cambio' => 'fa-exchange-alt',
                                    'Roles' => 'fa-user-tag',
                                    'Usuarios' => 'fa-users',
                                    'Salidas' => 'fa-sign-out-alt',
                                    'Lotes' => 'fa-layer-group',
                                    'Otros Permisos' => 'fa-question-circle',
                                ];
                            @endphp

                            @foreach ($permisos as $modulo => $grupoPermisos)
                                <div class="col-md-4 col-sm-6 mb-4">
                                    <div class="card card-outline card-{{ $rol->name === 'admin' ? 'secondary' : 'primary' }} h-100 modulo-card"
                                        id="modulo-{{ Str::slug($modulo) }}">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas {{ $iconosModulos[$modulo] ?? 'fa-folder' }} text-primary mr-2"></i>
                                                    <b>{{ $modulo }}</b>
                                                </h5>
                                                <div>
                                                    <span class="badge badge-primary" id="count-{{ Str::slug($modulo) }}">
                                                        {{ $grupoPermisos->count() }} permisos
                                                    </span>
                                                    <button type="button"
                                                        class="btn btn-xs btn-outline-secondary ml-2 toggle-modulo"
                                                        data-target="#modulo-{{ Str::slug($modulo) }}-content">
                                                        <i class="fas fa-chevron-up"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body" id="modulo-{{ Str::slug($modulo) }}-content">
                                            @if($rol->name !== 'admin')
                                                <!-- Botones de selección rápida por módulo (solo para roles no admin) -->
                                                <div class="btn-group btn-group-sm mb-3 w-100" role="group">
                                                    <button type="button" class="btn btn-outline-success seleccionar-modulo"
                                                        data-modulo="{{ Str::slug($modulo) }}">
                                                        <i class="fas fa-check"></i> Todo
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger deseleccionar-modulo"
                                                        data-modulo="{{ Str::slug($modulo) }}">
                                                        <i class="fas fa-times"></i> Ninguno
                                                    </button>
                                                </div>
                                            @endif

                                            <!-- Lista de permisos -->
                                            <div class="permisos-lista" style="max-height: 300px; overflow-y: auto;">
                                                @foreach ($grupoPermisos as $permiso)
                                                    @php
                                                        if ($modulo == 'Otros Permisos') {
                                                            $nombreFormateado = str_replace('_', ' ', $permiso->name);
                                                            $nombreFormateado = ucwords(str_replace('.', ' - ', $nombreFormateado));
                                                            $iconoAccion = 'fa-cog';
                                                            $nombreAmigable = $nombreFormateado;
                                                        } else {
                                                            $traduccionesPermisos = [
                                                                'categorias.index' => 'Ver categorías',
                                                                'categorias.create' => 'Crear categoría',
                                                                'categorias.store' => 'Guardar categoría',
                                                                'categorias.show' => 'Ver detalle de categoría',
                                                                'categorias.edit' => 'Editar categoría',
                                                                'categorias.update' => 'Actualizar categoría',
                                                                'categorias.destroy' => 'Eliminar categoría',
                                                                'productos.index' => 'Ver productos',
                                                                'productos.create' => 'Crear producto',
                                                                'productos.store' => 'Guardar producto',
                                                                'productos.show' => 'Ver detalle de producto',
                                                                'productos.edit' => 'Editar producto',
                                                                'productos.update' => 'Actualizar producto',
                                                                'productos.destroy' => 'Eliminar producto',
                                                                'sucursales.index' => 'Ver sucursales',
                                                                'sucursales.create' => 'Crear sucursal',
                                                                'sucursales.store' => 'Guardar sucursal',
                                                                'sucursales.show' => 'Ver detalle de sucursal',
                                                                'sucursales.edit' => 'Editar sucursal',
                                                                'sucursales.update' => 'Actualizar sucursal',
                                                                'sucursales.destroy' => 'Eliminar sucursal',
                                                                'lotes.vencidos.sucursal' => 'Ver lotes vencidos por sucursal',
                                                                'proveedores.index' => 'Ver proveedores',
                                                                'proveedores.create' => 'Crear proveedor',
                                                                'proveedores.store' => 'Guardar proveedor',
                                                                'proveedores.show' => 'Ver detalle de proveedor',
                                                                'proveedores.edit' => 'Editar proveedor',
                                                                'proveedores.update' => 'Actualizar proveedor',
                                                                'proveedores.destroy' => 'Eliminar proveedor',
                                                                'compras.index' => 'Ver compras',
                                                                'compras.create' => 'Crear compra',
                                                                'compras.store' => 'Guardar compra',
                                                                'compras.show' => 'Ver detalle de compra',
                                                                'compras.edit' => 'Editar compra',
                                                                'compras.update' => 'Actualizar compra',
                                                                'compras.destroy' => 'Eliminar compra',
                                                                'inventario.index' => 'Ver inventario',
                                                                'inventario.create' => 'Crear registro',
                                                                'inventario.store' => 'Guardar registro',
                                                                'inventario.show' => 'Ver detalle',
                                                                'inventario.edit' => 'Editar registro',
                                                                'inventario.update' => 'Actualizar registro',
                                                                'inventario.destroy' => 'Eliminar registro',
                                                                'tipo_cambio.index' => 'Ver tipo de cambio',
                                                                'tipo_cambio.create' => 'Crear tipo de cambio',
                                                                'tipo_cambio.store' => 'Guardar tipo de cambio',
                                                                'tipo_cambio.show' => 'Ver detalle',
                                                                'tipo_cambio.edit' => 'Editar tipo de cambio',
                                                                'tipo_cambio.update' => 'Actualizar tipo de cambio',
                                                                'tipo_cambio.destroy' => 'Eliminar tipo de cambio',
                                                                'roles.index' => 'Ver roles',
                                                                'roles.create' => 'Crear rol',
                                                                'roles.store' => 'Guardar rol',
                                                                'roles.show' => 'Ver detalle de rol',
                                                                'roles.edit' => 'Editar rol',
                                                                'roles.update' => 'Actualizar rol',
                                                                'roles.destroy' => 'Eliminar rol',
                                                                'user.index' => 'Ver usuarios',
                                                                'user.create' => 'Crear usuario',
                                                                'user.store' => 'Guardar usuario',
                                                                'user.show' => 'Ver detalle de usuario',
                                                                'user.edit' => 'Editar usuario',
                                                                'user.update' => 'Actualizar usuario',
                                                                'user.destroy' => 'Eliminar usuario',
                                                                'salidas.index' => 'Ver salidas',
                                                                'salidas.create' => 'Crear salida',
                                                                'salidas.store' => 'Guardar salida',
                                                                'salidas.show' => 'Ver detalle de salida',
                                                                'salidas.edit' => 'Editar salida',
                                                                'salidas.update' => 'Actualizar salida',
                                                                'salidas.destroy' => 'Eliminar salida',
                                                                'lotes.index' => 'Ver lotes',
                                                                'lotes.create' => 'Crear lote',
                                                                'lotes.store' => 'Guardar lote',
                                                                'lotes.show' => 'Ver detalle de lote',
                                                                'lotes.edit' => 'Editar lote',
                                                                'lotes.update' => 'Actualizar lote',
                                                                'lotes.destroy' => 'Eliminar lote',
                                                            ];

                                                            $partes = explode('.', $permiso->name);
                                                            $accion = $partes[1] ?? '';

                                                            $iconosAccion = [
                                                                'index' => 'fa-list',
                                                                'create' => 'fa-plus-circle',
                                                                'store' => 'fa-save',
                                                                'show' => 'fa-eye',
                                                                'edit' => 'fa-edit',
                                                                'update' => 'fa-sync',
                                                                'destroy' => 'fa-trash',
                                                            ];

                                                            $iconoAccion = $iconosAccion[$accion] ?? 'fa-circle';
                                                            $nombreAmigable = $traduccionesPermisos[$permiso->name] ?? $permiso->name;
                                                        }
                                                    @endphp

                                                    <div class="permiso-item" data-modulo="{{ Str::slug($modulo) }}"
                                                        id="item-{{ $permiso->id }}">
                                                        <label class="custom-checkbox-right @if($rol->name === 'admin') disabled-label @endif"
                                                            for="permiso-{{ $permiso->id }}">
                                                            <span class="checkbox-label">
                                                                <i class="fas {{ $iconoAccion }} text-secondary mr-2"></i>
                                                                {{ $nombreAmigable }}
                                                            </span>
                                                            <input type="checkbox" class="permiso-checkbox"
                                                                name="permisos[]" value="{{ $permiso->id }}"
                                                                id="permiso-{{ $permiso->id }}"
                                                                {{ $rol->name === 'admin' ? 'checked disabled' : ($rol->hasPermissionTo($permiso->name) ? 'checked' : '') }}>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @if($rol->name !== 'admin')
                                        <div class="card-footer text-muted small">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span id="selected-{{ Str::slug($modulo) }}">0</span> de
                                            {{ $grupoPermisos->count() }} seleccionados
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($rol->name !== 'admin')
                            <hr>

                            <!-- Resumen de selección (solo para roles no admin) -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-secondary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-clipboard-list mr-2"></i>
                                                <strong>Resumen de selección:</strong>
                                                <span id="total-seleccionados">0</span> permisos seleccionados de <span
                                                    id="total-permisos">{{ Permission::count() }}</span> totales
                                            </div>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                data-target="#resumenModal">
                                                <i class="fas fa-eye"></i> Ver resumen
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Botones de acción -->
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </a>
                                @if($rol->name !== 'admin')
                                    <button type="submit" class="btn btn-primary" id="btn-guardar">
                                        <i class="fas fa-save"></i> Guardar Permisos
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary" disabled>
                                        <i class="fas fa-lock"></i> Rol Protegido - No modificable
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($rol->name !== 'admin')
    <!-- Modal de Resumen (solo para roles no admin) -->
    <div class="modal fade" id="resumenModal" tabindex="-1" role="dialog" aria-labelledby="resumenModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resumenModalLabel">
                        <i class="fas fa-clipboard-list"></i> Resumen de Permisos Seleccionados
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="resumen-contenido"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .permiso-item {
            transition: all 0.2s;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 6px;
            border: 1px solid transparent;
        }

        .permiso-item:hover {
            background-color: #f0f7ff;
            border-color: #cce5ff;
        }

        .permiso-item.seleccionado {
            background-color: #e8f5e9;
            border-color: #a5d6a7;
        }

        .custom-checkbox-right {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            width: 100%;
        }

        .custom-checkbox-right input[type="checkbox"] {
            position: relative;
            width: 18px;
            height: 18px;
            cursor: pointer;
            order: 2;
            margin-left: 10px;
            accent-color: #28a745;
        }

        .custom-checkbox-right input[type="checkbox"]:disabled {
            accent-color: #6c757d;
            cursor: not-allowed;
            opacity: 0.65;
        }

        .custom-checkbox-right.disabled-label {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .custom-checkbox-right .checkbox-label {
            display: flex;
            align-items: center;
            flex: 1;
            order: 1;
            cursor: pointer;
        }

        .custom-checkbox-right.disabled-label .checkbox-label {
            cursor: not-allowed;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .permisos-lista::-webkit-scrollbar {
            width: 6px;
        }

        .permisos-lista::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .permisos-lista::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .permisos-lista::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .permiso-item.seleccionado .checkbox-label {
            color: #2e7d32;
            font-weight: 500;
        }

        .permiso-item.seleccionado i {
            color: #2e7d32 !important;
        }

        .card.card-outline.card-secondary .card-header {
            background-color: #f8f9fa;
            border-bottom-color: #6c757d;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Solo ejecutar scripts interactivos si NO es admin
            @if($rol->name !== 'admin')
                // Marcar items inicialmente seleccionados
                $('.permiso-checkbox:checked').each(function() {
                    $(this).closest('.permiso-item').addClass('seleccionado');
                });

                // Actualizar contadores iniciales
                actualizarTodosLosContadores();
                actualizarTotalSeleccionados();

                // Función para actualizar contador de un módulo específico
                function actualizarContadorModulo(modulo) {
                    var total = $('.permiso-item[data-modulo="' + modulo + '"]').length;
                    var seleccionados = $('.permiso-item[data-modulo="' + modulo + '"] .permiso-checkbox:checked')
                        .length;
                    $('#selected-' + modulo).text(seleccionados);
                }

                // Función para actualizar todos los contadores
                function actualizarTodosLosContadores() {
                    $('.modulo-card').each(function() {
                        var id = $(this).attr('id');
                        if (id) {
                            var modulo = id.replace('modulo-', '');
                            actualizarContadorModulo(modulo);
                        }
                    });
                }

                // Función para actualizar total de seleccionados
                function actualizarTotalSeleccionados() {
                    var total = $('.permiso-checkbox:checked').length;
                    $('#total-seleccionados').text(total);
                }

                // Evento cuando se marca/desmarca un checkbox
                $(document).on('change', '.permiso-checkbox', function() {
                    var item = $(this).closest('.permiso-item');
                    if ($(this).is(':checked')) {
                        item.addClass('seleccionado');
                    } else {
                        item.removeClass('seleccionado');
                    }

                    var modulo = item.data('modulo');
                    actualizarContadorModulo(modulo);
                    actualizarTotalSeleccionados();
                });

                // Seleccionar todos los permisos de un módulo
                $('.seleccionar-modulo').click(function() {
                    var modulo = $(this).data('modulo');
                    $('.permiso-item[data-modulo="' + modulo + '"] .permiso-checkbox').prop('checked', true)
                        .trigger('change');
                });

                // Deseleccionar todos los permisos de un módulo
                $('.deseleccionar-modulo').click(function() {
                    var modulo = $(this).data('modulo');
                    $('.permiso-item[data-modulo="' + modulo + '"] .permiso-checkbox').prop('checked', false)
                        .trigger('change');
                });

                // Seleccionar todos los permisos
                $('#seleccionar-todos').click(function() {
                    $('.permiso-checkbox').prop('checked', true).trigger('change');
                });

                // Deseleccionar todos los permisos
                $('#deseleccionar-todos').click(function() {
                    $('.permiso-checkbox').prop('checked', false).trigger('change');
                });

                // Expandir/colapsar módulo
                $('.toggle-modulo').click(function() {
                    var target = $(this).data('target');
                    $(target).slideToggle();
                    $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
                });

                // Expandir todos los módulos
                $('#expandir-todos').click(function() {
                    $('[id$="-content"]').slideDown();
                    $('.toggle-modulo i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                });

                // Colapsar todos los módulos
                $('#colapsar-todos').click(function() {
                    $('[id$="-content"]').slideUp();
                    $('.toggle-modulo i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                });

                // Generar resumen
                $('#resumenModal').on('show.bs.modal', function() {
                    var html = '<div class="row">';
                    $('.modulo-card').each(function() {
                        var moduloNombre = $(this).find('.card-title b').text();
                        var moduloId = $(this).attr('id').replace('modulo-', '');
                        var seleccionados = $('.permiso-item[data-modulo="' + moduloId +
                            '"] .permiso-checkbox:checked').length;

                        if (seleccionados > 0) {
                            html += '<div class="col-md-6 mb-3">';
                            html += '<div class="card card-outline card-success">';
                            html += '<div class="card-header"><h6><b>' + moduloNombre + '</b> (' +
                                seleccionados + ' permisos)</h6></div>';
                            html += '<div class="card-body p-2">';
                            html += '<ul class="list-unstyled">';

                            $('.permiso-item[data-modulo="' + moduloId + '"] .permiso-checkbox:checked')
                                .each(function() {
                                    var label = $(this).closest('.permiso-item').find(
                                        '.checkbox-label').text().trim();
                                    html +=
                                        '<li><i class="fas fa-check-circle text-success mr-2"></i>' +
                                        label + '</li>';
                                });

                            html += '</ul>';
                            html += '</div></div></div>';
                        }
                    });
                    html += '</div>';

                    if ($('.permiso-checkbox:checked').length === 0) {
                        html = '<div class="alert alert-warning">No hay permisos seleccionados</div>';
                    }

                    $('#resumen-contenido').html(html);
                });

                // Confirmación antes de guardar
                $('#btn-guardar').click(function(e) {
                    if ($('.permiso-checkbox:checked').length === 0) {
                        if (!confirm('¿Estás seguro de guardar sin ningún permiso seleccionado?')) {
                            e.preventDefault();
                        }
                    }
                });
            @endif
        });
    </script>
@stop
