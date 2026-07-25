@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Roles</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Roles registrados</b></h3>
                    <div class="card-tools">
                        <a class="btn btn-primary" href="{{ route('roles.create') }}">
                            <i class="fas fa-plus"></i> Crear nuevo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Nombre del Rol</th>
                                <th>Usuarios</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $rol)
                                <tr class="{{ $rol->name === 'admin' ? 'table-primary' : '' }}">
                                    <td style="text-align: center">{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $rol->name }}
                                        @if($rol->name === 'admin')
                                            <span class="badge badge-danger" data-toggle="tooltip"
                                                  title="Rol protegido - No se puede eliminar">
                                                <i class="fas fa-shield-alt"></i> PROTEGIDO
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        <span class="badge badge-info">
                                            {{ $rol->users()->count() }} usuarios
                                        </span>
                                    </td>
                                    <td style="text-align: center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('roles.permisos', $rol->id) }}"
                                               class="btn btn-warning btn-sm"
                                               title="Gestionar permisos">
                                                <i class="fas fa-check"></i> Asignar Permisos
                                            </a>

                                            <a href="{{ route('roles.edit', $rol->id) }}"
                                               class="btn btn-success btn-sm"
                                               title="Editar rol">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>

                                            @if($rol->name !== 'admin')
                                                <form action="{{ route('roles.destroy', $rol->id) }}"
                                                      method="POST"
                                                      id="form-eliminar-{{ $rol->id }}"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="confirmarEliminacion({{ $rol->id }}, '{{ $rol->name }}')"
                                                            title="Eliminar rol">
                                                        <i class="fas fa-trash-alt"></i> Eliminar
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button"
                                                        class="btn btn-secondary btn-sm"
                                                        disabled
                                                        data-toggle="tooltip"
                                                        title="Rol protegido - No se puede eliminar">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
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

        .btn-danger { background-color: #dc3545; border: none; }
        .btn-success { background-color: #28a745; border: none; }
        .btn-info { background-color: #17a2b8; border: none; }
        .btn-warning { background-color: #ffc107; color: #212529; border: none; }
        .btn-default { background-color: #6e7176; color: #212529; border: none; }

        .table-primary {
            background-color: #cfe2ff !important;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            margin: 0 2px;
        }

        .btn-group {
            gap: 3px;
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Inicializar DataTable
            $("#example1").DataTable({
                "pageLength": 5,
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Roles",
                    "infoFiltered": "(Filtrado de _MAX_ total Roles)",
                    "lengthMenu": "Mostrar _MENU_ Roles",
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
                "columnDefs": [
                    { "orderable": false, "targets": [2, 3] } // Deshabilitar orden en columnas de usuarios y acciones
                ]
            });
        });

        function confirmarEliminacion(rolId, rolName) {
            Swal.fire({
                title: "¿Eliminar rol?",
                html: `¿Estás seguro de eliminar el rol <strong>${rolName}</strong>?`,
                text: "Esta acción no se puede deshacer",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("form-eliminar-" + rolId).submit();
                }
            });
        }

        // Mostrar mensajes de sesión
        @if(session('mensaje'))
            Swal.fire({
                title: session('icono') == 'success' ? 'Éxito' : 'Error',
                text: "{{ session('mensaje') }}",
                icon: "{{ session('icono') }}",
                confirmButtonColor: "#3085d6",
                timer: session('icono') == 'success' ? 3000 : undefined
            });
        @endif
    </script>
@stop
