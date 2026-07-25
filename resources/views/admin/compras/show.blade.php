@extends('adminlte::page')

@section('title', 'Compras')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Datos de la Compra nro {{ $compra->id }}</li>
        </ol>
    </nav>
    <hr>

    @if ($compra->estado == 'Recibido')
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="btn-group">
                            <a href="{{ route('compras.nota-pdf', $compra->id) }}" target="_blank" class="btn btn-success">
                                <i class="fas fa-file-pdf"></i> Ver Nota de Compra
                            </a>
                            <a href="{{ route('compras.descargar-nota', $compra->id) }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Descargar Nota
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('content')

    {{-- CARD-BODY CON LOS DATOS DE LA COMPRA CREADA --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>Compra creada</b></h3>
                    <div class="card-tools">
                        @if ($compra->estado == 'Recibido')
                            <a href="{{ route('compras.correccion.edit', $compra->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Corregir Compra
                            </a>
                        @endif
                    </div>
                </div>

                @if ($compra->estado == 'anulado')
                    <div class="alert alert-danger">
                        <i class="fas fa-ban"></i>
                        <strong>Pedido Anulado</strong> - Este pedido fue cancelado.
                    </div>
                @endif

                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-md-12">
                            {{-- Usuario que creó la salida --}}
                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <p>{{ $compra->user->name ?? 'No asignado' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        {{-- PROVEEDOR DE LA COMPRA --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="proveedor_id">Proveedores</label>
                                <p>{{ $compra->proveedor->nombre }}</p>
                            </div>
                        </div>

                        {{-- FECHA DE LA COMPRA --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="fecha">Fecha de la compra</label>
                                <p>{{ $compra->fecha }}</p>
                            </div>
                        </div>

                        {{-- OBSERVACIONES DE LA COMPRA --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <p>{{ $compra->observaciones }}</p>
                            </div>
                        </div>

                        {{-- ESTADO DE LA COMPRA --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="estado">Estado de la compra</label>
                                <p>
                                    @if ($compra->estado == 'Recibido')
                                        <span class="badge badge-success">{{ $compra->estado }}</span>
                                    @elseif($compra->estado == 'pendiente')
                                        <span class="badge badge-warning">{{ $compra->estado }}</span>
                                    @elseif($compra->estado == 'enviado al proveedor')
                                        <span class="badge badge-info">{{ $compra->estado }}</span>
                                    @elseif($compra->estado == 'anulado')
                                        <span class="badge badge-danger">{{ $compra->estado }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $compra->estado }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- TOTAL DE LA COMPRA --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="total_compra">Total de la compra</label>
                                <p>${{ number_format($compra->total, 2) }}</p>
                            </div>
                        </div>

                        {{-- SUCURSAL DESTINO DE LA COMPRA --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="total_compra">Sucursal de destino</label>
                                @if ($sucursal_destino)
                                    <p>{{ $sucursal_destino->nombre }}</p>
                                @else
                                    <p>No se ha seleccionado ninguna sucursal.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD-BODY PARA LOS DETALLES DE LA COMPRA --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Detalle de la compra</b></h3>
                </div>
                <div class="card-body" style="display: block;">

                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th>Producto</th>
                                <th>Marca</th>
                                <th>Lote</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($compra->detalles as $detalle)
                                <tr class="{{ $detalle->trashed() ? 'table-danger text-muted' : '' }}">
                                    <td style="text-align: center">{{ $loop->iteration }}</td>
                                    <td>{{ $detalle->producto->nombre }}</td>
                                    <td>
                                        @if ($detalle->producto->marca)
                                            <span class="badge badge-info">{{ $detalle->producto->marca }}</span>
                                        @else
                                            <span class="badge badge-secondary">Sin marca</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($detalle->lote)
                                            {{ $detalle->lote->codigo_lote }}
                                        @elseif($detalle->trashed())
                                            <span class="badge badge-danger">Eliminado / Anulado</span>
                                        @else
                                            <span class="badge badge-warning">Pendiente de recibir</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        @if ($detalle->trashed())
                                            <span class="badge badge-danger">{{ $detalle->cantidad }} (Cancelado)</span>
                                        @else
                                            {{ $detalle->cantidad }}
                                        @endif
                                    </td>
                                    <td style="text-align: right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                    <td style="text-align: right">${{ number_format($detalle->subtotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay productos registrados en esta compra</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total:</th>
                                <th class="text-right">${{ number_format($compra->total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
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

        .badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .table-danger {
            background-color: #f8d7da !important;
        }

        .text-muted {
            color: #6c757d !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            $("#example1").DataTable({
                "pageLength": 5,
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                    "infoEmpty": "Mostrando 0 a 0 de 0 productos",
                    "infoFiltered": "(Filtrado de _MAX_ total productos)",
                    "lengthMenu": "Mostrar _MENU_ productos",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscador:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Ultimo",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                buttons: [{
                        text: '<i class="fas fa-copy"></i> COPIAR',
                        extend: 'copy',
                        className: 'btn btn-default',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        extend: 'pdfHtml5',
                        className: 'btn btn-danger',
                        orientation: 'portrait',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        },
                        customize: function(doc) {
                            var tableIndex = doc.content.findIndex(item => item.table && item.table
                                .body);
                            if (tableIndex !== -1) {
                                var columnCount = doc.content[tableIndex].table.body[0].length;
                                doc.content[tableIndex].table.widths = Array(columnCount).fill('*');
                                doc.content[tableIndex].table.body.forEach(function(row, rowIndex) {
                                    row.forEach(function(cell) {
                                        cell.alignment = 'center';
                                    });
                                });
                            }
                            doc.content.unshift({
                                text: 'INFORME DE COMPRA',
                                alignment: 'center',
                                fontSize: 16,
                                bold: true,
                                margin: [0, 0, 0, 15]
                            });
                            doc.content.splice(1, 0, {
                                alignment: 'center',
                                table: {
                                    widths: ['50%', '50%'],
                                    body: [
                                        [{
                                            text: 'Compra N°:',
                                            bold: true,
                                            alignment: 'right'
                                        }, {
                                            text: '{{ $compra->id }}',
                                            alignment: 'left'
                                        }],
                                        [{
                                            text: 'Fecha:',
                                            bold: true,
                                            alignment: 'right'
                                        }, {
                                            text: '{{ $compra->fecha }}',
                                            alignment: 'left'
                                        }],
                                        [{
                                            text: 'Sucursal:',
                                            bold: true,
                                            alignment: 'right'
                                        }, {
                                            text: '{{ $sucursal_destino->nombre ?? '---' }}',
                                            alignment: 'left'
                                        }],
                                        [{
                                            text: 'Estado:',
                                            bold: true,
                                            alignment: 'right'
                                        }, {
                                            text: '{{ $compra->estado }}',
                                            alignment: 'left'
                                        }],
                                        [{
                                            text: 'Total:',
                                            bold: true,
                                            alignment: 'right'
                                        }, {
                                            text: '${{ number_format($compra->total, 2) }}',
                                            alignment: 'left'
                                        }],
                                    ]
                                },
                                layout: {
                                    hLineWidth: () => 0,
                                    vLineWidth: () => 0,
                                    paddingTop: () => 4,
                                    paddingBottom: () => 4
                                },
                                margin: [0, 0, 0, 20]
                            });
                            doc.styles.tableHeader = {
                                bold: true,
                                fontSize: 10,
                                color: 'white',
                                fillColor: '#343a40',
                                alignment: 'center'
                            };
                            doc.defaultStyle.fontSize = 9;
                            doc.footer = function(currentPage, pageCount) {
                                return {
                                    columns: [{
                                            text: 'Sistema de Inventario',
                                            alignment: 'left',
                                            margin: [20, 0]
                                        },
                                        {
                                            text: 'Página ' + currentPage + ' de ' +
                                                pageCount,
                                            alignment: 'right',
                                            margin: [0, 0, 20]
                                        }
                                    ],
                                    fontSize: 8
                                };
                            };
                        }
                    },
                    {
                        text: '<i class="fas fa-print"></i> IMPRIMIR',
                        extend: 'print',
                        className: 'btn btn-warning',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        },
                        customize: function(win) {
                            $(win.document.body).css('text-align', 'center');
                            $(win.document.body).find('table').css({
                                marginLeft: 'auto',
                                marginRight: 'auto'
                            });
                            $(win.document.body).find('th, td').css('text-align', 'center');
                            $(win.document.body).prepend(`
                                <div style="text-align:center;">
                                    <h2>INFORME DE COMPRA</h2>
                                    <p><strong>Compra N°:</strong> {{ $compra->id }}</p>
                                    <p><strong>Fecha:</strong> {{ $compra->fecha }}</p>
                                    <p><strong>Proveedor:</strong> {{ $compra->proveedor->nombre }}</p>
                                    <p><strong>Sucursal:</strong> {{ $sucursal_destino->nombre ?? '---' }}</p>
                                    <p><strong>Estado:</strong> {{ $compra->estado }}</p>
                                    <p><strong>Total:</strong> ${{ number_format($compra->total, 2) }}</p>
                                    <hr>
                                </div>
                            `);
                        }
                    }
                ]
            }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
        });
    </script>
@stop
