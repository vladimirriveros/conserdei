@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Usuarios</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Usuarios registrados</b></h3>
                    <div class="card-tools">
                        <a class="btn btn-primary" href="{{ route('user.create') }}">
                            <i class="fas fa-plus"></i> Crear nuevo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="mostrar-todos">
                                    <i class="fas fa-users"></i> Todos
                                </button>
                                <button type="button" class="btn btn-outline-success" id="mostrar-activos">
                                    <i class="fas fa-user-check"></i> Activos
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="mostrar-eliminados">
                                    <i class="fas fa-user-slash"></i> Eliminados
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="mostrar-protegidos">
                                    <i class="fas fa-shield-alt"></i> Protegidos
                                </button>
                            </div>
                        </div>
                    </div>

                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr
                                    class="{{ $user->trashed() ? 'table-danger' : ($user->is_protected ? 'table-primary' : '') }}">
                                    <td style="text-align: center">{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $user->name }}
                                        @if ($user->is_protected)
                                            <span class="badge badge-danger" data-toggle="tooltip"
                                                title="Administrador protegido - No se puede eliminar">
                                                <i class="fas fa-shield-alt"></i> PROTEGIDO
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->roles->isEmpty())
                                            <span class="badge badge-warning">Sin roles</span>
                                        @else
                                            @foreach ($user->roles as $role)
                                                <span class="badge badge-primary">{{ $role->name }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        @if ($user->trashed())
                                            <span class="badge badge-danger">
                                                <i class="fas fa-user-slash"></i> Eliminado
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                <i class="fas fa-user-check"></i> Activo
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        <div class="btn-group" role="group">
                                            @if ($user->trashed())
                                                <!-- Botones para usuarios eliminados -->
                                                <form action="{{ route('user.restaurar', $user->id) }}" method="GET"
                                                    class="d-inline">
                                                    <button type="submit" class="btn btn-success"
                                                        onclick="confirmarRestauracion(event, this)">
                                                        <i class="fas fa-trash-restore"></i>
                                                    </button>
                                                </form>

                                                @if (!$user->is_protected)
                                                    <form action="{{ route('user.forceDelete', $user->id) }}" method="POST"
                                                        class="d-inline" id="form-force-delete-{{ $user->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger"
                                                            onclick="confirmarEliminacionPermanente({{ $user->id }})"
                                                            @if ($user->is_protected) disabled @endif>
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-secondary" disabled
                                                        data-toggle="tooltip"
                                                        title="No se puede eliminar un administrador protegido">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <!-- Botones para usuarios activos -->
                                                <a href="{{ route('user.edit', $user->id) }}" class="btn btn-success">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if (!$user->is_protected && $user->id !== auth()->id())
                                                    <form action="{{ url('/admin/users/' . $user->id) }}" method="POST"
                                                        class="d-inline" id="form-eliminar-{{ $user->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger"
                                                            onclick="confirmarEliminacion({{ $user->id }})">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @elseif($user->is_protected)
                                                    <button type="button" class="btn btn-secondary" disabled
                                                        data-toggle="tooltip"
                                                        title="Administrador protegido - No se puede eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @elseif($user->id === auth()->id())
                                                    <button type="button" class="btn btn-secondary" disabled
                                                        data-toggle="tooltip" title="No puedes eliminar tu propio usuario">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        #example1_wrapper .dt-buttons {
            background-color: transparent;
            box-shadow: none;
            border: none;
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        #example1_wrapper .btn {
            color: #fff;
            border-radius: 4px;
            padding: 5px 15px;
            font-size: 14px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-info {
            background-color: #17a2b8;
            border: none;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
            border: none;
        }

        .btn-default {
            background-color: #6e7176;
            color: #212529;
            border: none;
        }

        .table-danger {
            background-color: #f8d7da !important;
        }

        .table-primary {
            background-color: #cfe2ff !important;
        }

        .table-danger td {
            color: #721c24;
        }

        .table-primary td {
            color: #084298;
        }

        .badge {
            font-size: 0.8rem;
            padding: 5px 8px;
        }

        .badge.badge-danger {
            background-color: #dc3545;
            color: white;
        }

        /* Tooltips personalizados */
        .btn[disabled] {
            cursor: not-allowed;
            opacity: 0.65;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Inicializar DataTable
            $("#example1").DataTable({
                "pageLength": 5,
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Usuarios",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Usuarios",
                    "infoFiltered": "(Filtrado de _MAX_ total Usuarios)",
                    "lengthMenu": "Mostrar _MENU_ Usuarios",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscador:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "order": [
                    [0, 'asc']
                ],
                "columnDefs": [{
                        "orderable": false,
                        "targets": 5
                    } // Deshabilitar orden en columna de acciones
                ]
            });
        });

        // Función para confirmar eliminación (soft delete)
        function confirmarEliminacion(userId) {
            Swal.fire({
                title: "¿Desea eliminar este usuario?",
                text: "El usuario se moverá a la papelera y podrá ser restaurado después",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("form-eliminar-" + userId).submit();
                }
            });
        }

        // Función para confirmar restauración
        function confirmarRestauracion(event, element) {
            event.preventDefault();
            Swal.fire({
                title: "¿Desea restaurar este usuario?",
                text: "El usuario volverá a estar activo en el sistema",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, restaurar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    element.closest('form').submit();
                }
            });
        }

        // Función para confirmar eliminación permanente
        function confirmarEliminacionPermanente(userId) {
            Swal.fire({
                title: "¿Eliminar permanentemente?",
                text: "Esta acción no se puede deshacer. El usuario se eliminará definitivamente",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar permanentemente",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("form-force-delete-" + userId).submit();
                }
            });
        }

        let filtroActual = {
            estado: '',
            protegido: ''
        };

        // Filtros de la tabla
        // Filtros de la tabla mejorados
        $('#mostrar-todos').click(function() {
            var table = $('#example1').DataTable();
            // Limpiar TODOS los filtros
            table.column(4).search('').draw(); // Limpiar filtro de estado
            table.column(1).search('').draw(); // Limpiar filtro de protegidos
            filtroActual.estado = '';
            filtroActual.protegido = '';
        });

        $('#mostrar-activos').click(function() {
            var table = $('#example1').DataTable();
            // Aplicar filtro de activos y mantener el de protegidos si existe
            table.column(4).search('Activo').draw();
            filtroActual.estado = 'Activo';
        });

        $('#mostrar-eliminados').click(function() {
            var table = $('#example1').DataTable();
            // Aplicar filtro de eliminados y mantener el de protegidos si existe
            table.column(4).search('Eliminado').draw();
            filtroActual.estado = 'Eliminado';
        });

        // Nuevo filtro para usuarios protegidos
        // Filtro para usuarios protegidos (MEJORADO)
        $('#mostrar-protegidos').click(function() {
            var table = $('#example1').DataTable();

            // Verificar si ya estamos filtrando por protegidos
            if (filtroActual.protegido === 'PROTEGIDO') {
                // Si ya está activo, quitamos el filtro de protegidos
                table.column(1).search('').draw();
                filtroActual.protegido = '';
            } else {
                // Aplicamos filtro de protegidos
                table.column(1).search('PROTEGIDO', true, false).draw(); // true = regex, false = case sensitive
                filtroActual.protegido = 'PROTEGIDO';
            }
        });

        // Botón para limpiar todos los filtros
        $('#limpiar-filtros').click(function() {
            var table = $('#example1').DataTable();
            table.column(4).search('').draw();
            table.column(1).search('').draw();
            table.search('').draw(); // Limpiar también el buscador global
            filtroActual.estado = '';
            filtroActual.protegido = '';
        });

        // Mensaje de error si viene de una excepción
        @if (session('mensaje') && session('icono') == 'error')
            Swal.fire({
                title: "Error",
                text: "{{ session('mensaje') }}",
                icon: "error",
                confirmButtonColor: "#3085d6"
            });
        @endif

        // Mensaje de éxito
        @if (session('mensaje') && session('icono') == 'success')
            Swal.fire({
                title: "Éxito",
                text: "{{ session('mensaje') }}",
                icon: "success",
                confirmButtonColor: "#3085d6",
                timer: 3000
            });
        @endif
    </script>
@stop
