@extends('adminlte::page')

@section('title', 'Sucursales')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sucursales.index') }}">Sucursales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Sucursales</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
              <div class="card-header">
                <h3 class="card-title"><b>Sucursales registradas</b></h3>

                <div class="card-tools">
                  @can('sucursales.create')
                      <a class="btn btn-primary" href="{{ route('sucursales.create') }}">Crear nuevo</a>
                  @endcan
                </div>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
              <div class="card-body" style="display: block;">
                <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Nombre</th>
                            <th>Direccion</th>
                            <th>Teléfonos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sucursales as $sucursal)
                            <tr>
                                <td style="text-align: center">{{$loop->iteration}}</td>
                                <td>{{ $sucursal->nombre }}</td>
                                <td>{{ $sucursal->direccion }}</td>
                                <td>{{ $sucursal->telefono }}</td>
                                <td style="text-align: center">
                                    @if ($sucursal->activa == '1')
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td style="text-align: center">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        {{-- <a href="{{ url('/admin/sucursales/'.$sucursal->id) }}" class="btn btn-info"><i class="fas fa-eye"></i> Ver</a> --}}
                                        <a href="{{ route('sucursales.show', $sucursal->id) }}" class="btn btn-info"><i class="fas fa-eye"></i> Ver</a>
                                        {{-- <a href="{{ url('/admin/sucursales/'.$sucursal->id).'/edit' }}" class="btn btn-success"><i class="fas fa-edit"></i> Editar</a> --}}
                                        @can('sucursales.edit')
                                            <a href="{{ route('sucursales.edit', $sucursal->id) }}" class="btn btn-success"><i class="fas fa-edit"></i> Editar</a>
                                        @endcan
                                        {{-- <form action="{{ url('/admin/sucursales/'.$sucursal->id) }}" id="miformulario{{ $sucursal->id }}" method="POST" class="d-line"> --}}
                                        @can('sucursales.destroy')
                                            <form action="{{ route('sucursales.destroy', $sucursal->id) }}" id="miformulario{{ $sucursal->id }}" method="POST" class="d-line">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="preguntar{{ $sucursal->id }}(event)">
                                                    <i class="fas fa-trash-alt"></i> Eliminar</button>
                                            </form>
                                        @endcan
                                        <script>
                                            function preguntar{{ $sucursal->id }}(event){
                                                event.preventDefault();
                                                Swal.fire({
                                                title: "Desea eliminar {{ $sucursal->nombre }}",
                                                text: "",
                                                icon: "question",
                                                showCancelButton: true,
                                                confirmButtonColor: "#3085d6",
                                                cancelButtonColor: "#d33",
                                                confirmButtonText: "Si, eliminar",
                                                cancelButtonText: "Cancelar"
                                                }).then((result) => {
                                                if (result.isConfirmed) {
                                                    document.getElementById("miformulario{{ $sucursal->id }}").submit();
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

</style>
@stop

@section('js')
    <script>
        $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay informaciÃ³n",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Sucursales",
                "infoEmpty": "Mostrando 0 a 0 de 0 Sucursales",
                "infoFiltered": "(Filtrado de _MAX_ total Sucursales)",
                "lengthMenu": "Mostrar _MENU_ Sucursales",
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

        }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
    });
    </script>
@stop
