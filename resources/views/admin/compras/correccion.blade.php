@extends('adminlte::page')

@section('title', 'Corregir Compra')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.show', $compra->id) }}">Compra #{{ $compra->id }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Corregir</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><b>⚠️ Corregir Compra #{{ $compra->id }}</b></h3>
                <div class="card-tools">
                    <span class="badge badge-info">Proveedor: {{ $compra->proveedor->nombre }}</span>
                    <span class="badge badge-secondary">Fecha: {{ $compra->fecha }}</span>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Importante:</strong> Al corregir una compra, NO se modifican los registros originales.
                    Se generarán movimientos de inventario (entradas o salidas) para ajustar el stock real.
                    Esta acción queda registrada en el historial de movimientos.
                </div>

                <form action="{{ route('compras.correccion.update', $compra->id) }}" method="POST" id="formCorreccion">
                    @csrf

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>Cantidad Registrada</th>
                                <th>Cantidad Correcta</th>
                                <th>Diferencia</th>
                                <th>Stock Actual (Total)</th>
                                <th>Motivo de Corrección</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compra->detalles as $detalle)
                            <tr>
                                <td>
                                    {{ $detalle->producto->nombre }}
                                    <input type="hidden" name="correcciones[{{ $loop->index }}][detalle_id]" value="{{ $detalle->id }}">
                                </td>
                                <td>{{ $detalle->lote->codigo_lote }}</td>
                                <td class="text-center">{{ $detalle->cantidad }}</td>
                                <td>
                                    <input type="number"
                                           name="correcciones[{{ $loop->index }}][cantidad_correcta]"
                                           class="form-control cantidad-correcta"
                                           data-original="{{ $detalle->cantidad }}"
                                           data-row="{{ $loop->index }}"
                                           value="{{ $detalle->cantidad }}"
                                           min="0"
                                           required>
                                </td>
                                <td class="text-center diferencia" id="diferencia-{{ $loop->index }}">0</td>
                                <td class="text-center">
                                    <span class="badge badge-info stock-info"
                                          data-lote="{{ $detalle->lote_id }}"
                                          onclick="verStock({{ $detalle->lote_id }})"
                                          style="cursor: pointer">
                                        Ver stock
                                    </span>
                                </td>
                                <td>
                                    <input type="text"
                                           name="correcciones[{{ $loop->index }}][motivo]"
                                           class="form-control"
                                           placeholder="Ej: Error de tipeo"
                                           required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-warning" id="alerta-stock" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span id="mensaje-alerta"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar Corrección
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle de stock -->
<div class="modal fade" id="modalStock" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stock del Lote</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalStockBody">
                Cargando...
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function verStock(loteId) {
    $('#modalStockBody').html('Cargando...');
    $('#modalStock').modal('show');

    $.get('/admin/lotes/' + loteId + '/stock', function(data) {
        let html = '<p><strong>Lote:</strong> ' + data.lote + '</p>';
        html += '<p><strong>Stock Total:</strong> ' + data.stock_total + ' unidades</p>';
        html += '<hr><h6>Distribución por sucursal:</h6><ul>';

        data.stock_por_sucursal.forEach(function(item) {
            html += '<li>' + item.sucursal + ': ' + item.cantidad + ' unidades</li>';
        });

        html += '</ul>';
        $('#modalStockBody').html(html);
    }).fail(function() {
        $('#modalStockBody').html('Error al cargar el stock');
    });
}

$(document).ready(function() {
    // Calcular diferencias en tiempo real
    $('.cantidad-correcta').on('input', function() {
        let row = $(this).data('row');
        let original = parseInt($(this).data('original'));
        let nuevo = parseInt($(this).val()) || 0;
        let diferencia = nuevo - original;

        $('#diferencia-' + row).text(diferencia);

        if (diferencia > 0) {
            $('#diferencia-' + row).removeClass('text-danger').addClass('text-success');
        } else if (diferencia < 0) {
            $('#diferencia-' + row).removeClass('text-success').addClass('text-danger');
        } else {
            $('#diferencia-' + row).removeClass('text-success text-danger');
        }

        validarStock();
    });

    function validarStock() {
        let hayError = false;
        let mensajes = [];

        $('.cantidad-correcta').each(function() {
            let original = parseInt($(this).data('original'));
            let nuevo = parseInt($(this).val()) || 0;
            let diferencia = nuevo - original;

            // Si es una reducción, verificar que no sea mayor al stock (esto se valida en backend)
            if (diferencia < 0) {
                // Esta validación sería ideal hacerla con AJAX consultando el stock real
                // Por ahora solo una advertencia
                hayError = true;
                mensajes.push('⚠️ Estás reduciendo stock. Asegúrate de tener suficiente inventario.');
            }
        });

        if (hayError) {
            $('#alerta-stock').show();
            $('#mensaje-alerta').html(mensajes.join('<br>'));
        } else {
            $('#alerta-stock').hide();
        }
    }

    // Confirmación antes de enviar
    $('#formCorreccion').on('submit', function(e) {
        let tieneCorrecciones = false;

        $('.cantidad-correcta').each(function() {
            let original = parseInt($(this).data('original'));
            let nuevo = parseInt($(this).val()) || 0;
            if (original !== nuevo) {
                tieneCorrecciones = true;
            }
        });

        if (!tieneCorrecciones) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Sin cambios',
                text: 'No has realizado ninguna corrección'
            });
            return;
        }

        if (!confirm('¿Estás seguro de aplicar estas correcciones? Se generarán movimientos de inventario.')) {
            e.preventDefault();
        }
    });
});
</script>
@stop
