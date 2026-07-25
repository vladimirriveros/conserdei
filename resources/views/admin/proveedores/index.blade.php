@extends('adminlte::page')

@section('title', 'Proveedores')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Proveedores</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Proveedores</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
              <div class="card-header">
                <h3 class="card-title"><b>Proveedores registrados</b></h3>

                {{-- INICIO MODALES--}}
                <div class="card-tools">

                    {{-- MODAL CREAR PROVEEDOR --}}
                  <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ModalCreate">
                        Crear nuevo
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="ModalCreate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header" style="background-color: #1a17b8; color:#fff">
                                <h5 class="modal-title" id="exampleModalLabel">Crear Proveedor</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">

                                {{-- INICIO FORMULARIO  --}}
                                {{-- <form action="{{ url('/admin/proveedor/create') }}" method="POST"> --}}
                                <form action="{{ route('proveedores.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        {{-- EMPRESA --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="empresa"> Empresa <b style="color: red">(*)</b></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('empresa') }}" class="form-control" id="empresa" name="empresa" placeholder="Ingrese el nombre de la empresa" required>
                                                </div>
                                                @error('empresa')
                                                    <small style="color: red">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- DIRECCIÓN --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="direccion"> Dirección <b style="color: red">(*)</b></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('direccion') }}" class="form-control" id="direccion" name="direccion" placeholder="Ingrese la dirección" required>
                                                </div>
                                                @error('direccion')
                                                    <small style="color: red">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- NOMBRE --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="nombre"> Nombre <b style="color: red">(*)</b></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('nombre') }}" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre del proveedor" required>
                                                </div>
                                                @error('nombre')
                                                    <small style="color: red">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- TELEFONO --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="telefono"> Teléfono <b style="color: red">(*)</b></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('telefono') }}" class="form-control" id="telefono" name="telefono" placeholder="Ingrese el teléfono del proveedor" required>
                                                </div>
                                                @error('telefono')
                                                    <small style="color: red">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- EMAIL --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="email"> Email <b style="color: red">(*)</b></label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('email') }}" class="form-control" id="email" name="email" placeholder="Ingrese el email del proveedor" required>
                                                </div>
                                                @error('email')
                                                    <small style="color: red">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- BOTONES --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Registrar</button>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                                {{-- FIN FORMULARIO --}}
                            </div>

                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive" style="display: block;">
                <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Empresa</th>
                            <th>Dirección</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proveedores as $proveedor)
                            <tr>
                                <td style="text-align: center">{{$loop->iteration}}</td>
                                <td>{{ $proveedor->empresa }}</td>
                                <td>{{ $proveedor->direccion }}</td>
                                <td>{{ $proveedor->nombre }}</td>
                                <td>{{ $proveedor->telefono }}</td>
                                <td>{{ $proveedor->email }}</td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic example">

                                        {{-- Mboton modal ver proveedor --}}
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ModalShow{{ $proveedor->id }}">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        {{-- MODAL VER PROVEEDOR --}}
                                        <div class="modal fade" id="ModalShow{{ $proveedor->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="background-color: #177db8; color:#ffffff">
                                                        <h5 class="modal-title" id="exampleModalLabel">Datos Proveedor</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            {{-- EMPRESA --}}
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="empresa"> Empresa </label>
                                                                    <p>{{ $proveedor->empresa }}</p>
                                                                </div>
                                                            </div>

                                                            {{-- DIRECCIÓN --}}
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="direccion"> Dirección </label>
                                                                    <p>{{ $proveedor->direccion }}</p>
                                                                </div>
                                                            </div>

                                                            {{-- NOMBRE --}}
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="nombre"> Nombre </label>
                                                                    <p>{{ $proveedor->nombre }}</p>
                                                                </div>
                                                            </div>

                                                            {{-- TELEFONO --}}
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="telefono"> Teléfono </label>
                                                                    <p>{{ $proveedor->telefono }}</p>
                                                                </div>
                                                            </div>

                                                            {{-- EMAIL --}}
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="email"> Email </label>
                                                                    <p>{{ $proveedor->email }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- BOTONES --}}
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>


                                        {{-- boton modal editar proveedor --}}
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#ModalEdit{{ $proveedor->id }}">
                                            <i class="fas fa-edit"s></i> Editar
                                        </button>
                                        {{-- MODAL EDITAR PROVEEDOR --}}
                                        <div class="modal fade" id="ModalEdit{{ $proveedor->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                <div class="modal-header" style="background-color: #069144; color:#fff">
                                                    <h5 class="modal-title" id="exampleModalLabel">Editar Proveedor</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                    <div class="modal-body">
                                                        {{-- formulario--}}
                                                        {{-- <form action="{{ url('/admin/proveedor/create') }}" method="POST"> --}}
                                                        <form action="{{ route('proveedores.update', $proveedor->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="row">
                                                                {{-- empresa --}}
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="empresa"> Empresa <b style="color: red">(*)</b></label>
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                                                            </div>
                                                                            <input type="text" value="{{ old('empresa', $proveedor->empresa) }}" class="form-control" id="empresa" name="empresa" placeholder="Ingrese el nombre de la empresa" required>
                                                                        </div>
                                                                        @error('empresa')
                                                                            <small style="color: red">{{ $message }}</small>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                {{-- DIRECCIÓN --}}
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="direccion"> Dirección <b style="color: red">(*)</b></label>
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                                            </div>
                                                                            <input type="text" value="{{ old('direccion', $proveedor->direccion) }}" class="form-control" id="direccion" name="direccion" placeholder="Ingrese la dirección" required>
                                                                        </div>
                                                                        @error('direccion')
                                                                            <small style="color: red">{{ $message }}</small>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                {{-- NOMBRE --}}
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="nombre"> Nombre <b style="color: red">(*)</b></label>
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                                            </div>
                                                                            <input type="text" value="{{ old('nombre', $proveedor->nombre) }}" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre del proveedor" required>
                                                                        </div>
                                                                        @error('nombre')
                                                                            <small style="color: red">{{ $message }}</small>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                {{-- TELEFONO --}}
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="telefono"> Teléfono <b style="color: red">(*)</b></label>
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                                            </div>
                                                                            <input type="text" value="{{ old('telefono', $proveedor->telefono) }}" class="form-control" id="telefono" name="telefono" placeholder="Ingrese el teléfono del proveedor" required>
                                                                        </div>
                                                                        @error('telefono')
                                                                            <small style="color: red">{{ $message }}</small>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                {{-- EMAIL --}}
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="email"> Email <b style="color: red">(*)</b></label>
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                                            </div>
                                                                            <input type="text" value="{{ old('email', $proveedor->email) }}" class="form-control" id="email" name="email" placeholder="Ingrese el email del proveedor" required>
                                                                        </div>
                                                                        @error('email')
                                                                            <small style="color: red">{{ $message }}</small>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- BOTONES --}}
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                        <button type="submit" class="btn btn-success">Actualizar</button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </form>
                                                        {{-- FIN FORMULARIO --}}
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <form action="{{ route('proveedores.destroy', $proveedor->id) }}" id="miformulario{{ $proveedor->id }}" method="POST" class="d-line">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="preguntar{{ $proveedor->id }}(event)">
                                                <i class="fas fa-trash-alt"></i>Eliminar</button>
                                        </form>
                                        <script>
                                            function preguntar{{ $proveedor->id }}(event){
                                                event.preventDefault();
                                                Swal.fire({
                                                title: "Desea eliminar {{ $proveedor->nombre }}",
                                                text: "",
                                                icon: "question",
                                                showCancelButton: true,
                                                confirmButtonColor: "#3085d6",
                                                cancelButtonColor: "#d33",
                                                confirmButtonText: "Si, eliminar",
                                                cancelButtonText: "Cancelar"
                                                }).then((result) => {
                                                if (result.isConfirmed) {
                                                    document.getElementById("miformulario{{ $proveedor->id }}").submit();
                                                }
                                                });
                                            }
                                        </script>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
    </div>
@stop

@section('css')
    <style>
    /* Fondo transparente y sin borde en el contenedor */
    #example1_wrapper .dt-buttons {
        background-color: transparent;
        box-shadow: none;
        border: none;
        display: flex;
        justify-content: center; /* Centrar los botones */
        gap: 10px; /* Espaciado entre botones */
        margin-bottom: 15px; /* Separar botones de la tabla */
    }

    /* Estilo personalizado para los botones */
    #example1_wrapper .btn {
        color: #fff; /* Color del texto en blanco */
        border-radius: 4px; /* Bordes redondeados */
        padding: 5px 15px; /* Espaciado interno */
        font-size: 14px; /* TamaÃ±o de fuente */
    }

    /* Colores por tipo de botÃ³n */
    .btn-danger { background-color: #dc3545; border: none; }
    .btn-success { background-color: #28a745; border: none; }
    .btn-info { background-color: #17a2b8; border: none; }
    .btn-warning { background-color: #ffc107; color: #212529; border: none; }
    .btn-default { background-color: #6e7176; color: #212529; border: none; }
</style>
@stop

@section('js')
    @if ($errors->any())
        <script>
            @if (session('modal_id'))
                var modalId = {{ session('modal_id') }};
                $('#ModalEdit' + modalId).modal('show');
            @else
                $('#ModalCreate').modal('show');
            @endif
        </script>

    @endif

    <script>
        $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "scrollX": true,
                "emptyTable": "No hay informaciÃ³n",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Proveedores",
                "infoEmpty": "Mostrando 0 a 0 de 0 Proveedores",
                "infoFiltered": "(Filtrado de _MAX_ total Proveedores)",
                "lengthMenu": "Mostrar _MENU_ Proveedores",
                "loadingRecords": "Cargando...",
                "processing": "Procesando",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ãšltimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            buttons: [
                { text: '<i class="fas fa-copy"></i> COPIAR', extend: 'copy', className: 'btn btn-default',
                    exportOptions: {columns: [0, 1, 2] }},
                { text: '<i class="fas fa-file-pdf"></i> PDF', extend: 'pdf', className: 'btn btn-danger',
                    exportOptions: {columns: [0, 1, 2] }},
                { text: '<i class="fas fa-file-csv"></i> CSV', extend: 'csv', className: 'btn btn-info',
                    exportOptions: {columns: [0, 1, 2] }},
                { text: '<i class="fas fa-file-excel"></i> EXCEL', extend: 'excel', className: 'btn btn-success',
                    exportOptions: {columns: [0, 1, 2] }},
                { text: '<i class="fas fa-print"></i> IMPRIMIR', extend: 'print', className: 'btn btn-warning',
                    exportOptions: {columns: [0, 1, 2] }},
            ]
        }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
    });
    </script>

@stop
