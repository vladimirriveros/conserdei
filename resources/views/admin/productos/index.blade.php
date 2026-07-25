@extends('adminlte::page')

@section('title', 'Productos')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Productos</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Productos registrados</b></h3>

                    <div class="card-tools">
                        @can('productos.create')
                            <a class="btn btn-primary" href="{{ route('productos.create') }}">Crear nuevo</a>
                        @endcan

                        {{-- <a href="{{ route('productos.historial', $producto->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-history"></i> Ver Historial
                        </a> --}}
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive" style="display: block;">
                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Categoria</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Descripción</th>
                                <th>Imagen</th>
                                <th>Precio Compra</th>
                                <th>Precio Venta</th>
                                <th>Porcentaje Ganancia</th>
                                <th>Stock Mínimo</th>
                                <th>Stock Máximo</th>
                                {{-- <th>Unidad Medida</th> --}}
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $producto)
                                <tr>
                                    <td style="text-align: center">{{ $loop->iteration }}</td>
                                    <td>{{ $producto->categoria->nombre }}</td>
                                    <td>{{ $producto->codigo }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>{{ $producto->marca }}</td>
                                    <td>{!! $producto->descripcion !!}</td>
                                    {{-- <td>{{ $producto->imagen}}</td> --}}
                                    <td style="text-align: center; padding: 0;">
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" loading="lazy"
                                            alt="{{ $producto->nombre }}"
                                            style="width: 100px; display: block; height: auto; margin: 0 auto;">
                                    </td>
                                    <td>{{ $producto->precio_compra }}</td>
                                    <td>{{ $producto->precio_venta }}</td>
                                    <td>{{ number_format($producto->porcentaje_ganancia, 2) }}%</td>
                                    <td>{{ $producto->stock_minimo}}</td>
                                    <td>{{ $producto->stock_maximo}}</td>
                                    {{-- <td>{{ $producto->unidad_medida}}</td> --}}
                                    <td style="text-align: center">
                                        @if ($producto->estado == '1')
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-info"><i
                                                    class="fas fa-eye"></i>Ver</a>
                                            @can('productos.edit')
                                                <a href="{{ route('productos.edit', $producto->id) }}"
                                                    class="btn btn-success"><i class="fas fa-edit"></i>Editar</a>
                                            @endcan
                                            @can('productos.destroy')
                                                <form action="{{ route('productos.destroy', $producto->id) }}"
                                                    id="miformulario{{ $producto->id }}" method="POST" class="d-line">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="preguntar{{ $producto->id }}(event)">
                                                        <i class="fas fa-trash-alt"></i>Eliminar</button>
                                                </form>
                                            @endcan
                                            <script>
                                                function preguntar{{ $producto->id }}(event) {
                                                    event.preventDefault();
                                                    Swal.fire({
                                                        title: "Desea eliminar {{ $producto->nombre }}",
                                                        text: "",
                                                        icon: "question",
                                                        showCancelButton: true,
                                                        confirmButtonColor: "#3085d6",
                                                        cancelButtonColor: "#d33",
                                                        confirmButtonText: "Si, eliminar",
                                                        cancelButtonText: "Cancelar"
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            document.getElementById("miformulario{{ $producto->id }}").submit();
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
            justify-content: center;
            /* Centrar los botones */
            gap: 10px;
            /* Espaciado entre botones */
            margin-bottom: 15px;
            /* Separar botones de la tabla */
        }

        /* Estilo personalizado para los botones */
        #example1_wrapper .btn {
            color: #fff;
            /* Color del texto en blanco */
            border-radius: 4px;
            /* Bordes redondeados */
            padding: 5px 15px;
            /* Espaciado interno */
            font-size: 14px;
            /* TamaÃ±o de fuente */
        }

        /* Colores por tipo de botÃ³n */
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
    </style>
@stop

@section('js')
    <script>
        $(function() {
            var currentUser = "{{ auth()->user()->name }}"; // Obtén el nombre del usuario actual

            $("#example1").DataTable({
                "pageLength": 10,
                "language": {
                    "scrollX": true,
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Productos",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Productos",
                    "infoFiltered": "(Filtrado de _MAX_ total Productos)",
                    "lengthMenu": "Mostrar _MENU_ Productos",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando",
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
                @if (auth()->user()->hasRole('admin')) //restriccion solo para administradores
                    buttons: [{
                            text: '<i class="fas fa-copy"></i> COPIAR',
                            extend: 'copy',
                            className: 'btn btn-default',
                            exportOptions: {
                                columns: [0, 1, 2]
                            }
                        },
                        {
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            extend: 'pdf',
                            className: 'btn btn-danger',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 7, 8]
                            },
                            title: 'CONSERDEI\n\nUsuario : ' + currentUser,
                            messageBottom: 'Detallade de los productos.',
                            messageTop: 'Fecha: ' + new Date()
                        .toLocaleDateString(), // Fecha del reporte
                        },
                        {
                            text: '<i class="fas fa-file-csv"></i> CSV',
                            extend: 'csv',
                            className: 'btn btn-info',
                            exportOptions: {
                                columns: [0, 1, 2]
                            },
                            title: 'Global Spice\n\nReporte elaborado por: ' + currentUser,
                        },
                        {
                            text: '<i class="fas fa-file-excel"></i> EXCEL',
                            extend: 'excel',
                            className: 'btn btn-success',
                            exportOptions: {
                                columns: [0, 1, 2]
                            },
                            title: 'Global Spice\n\nReporte elaborado por: ' + currentUser,
                        },
                        {
                            text: '<i class="fas fa-print"></i> IMPRIMIR',
                            extend: 'print',
                            className: 'btn btn-warning',
                            exportOptions: {
                                columns: [0, 1, 2,3 ,4]
                            },
                            title: 'CONSERDEI<br><br>Usuario: ' + currentUser,
                        },
                    ]
                @endif
            }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
        });
    </script>
@stop
