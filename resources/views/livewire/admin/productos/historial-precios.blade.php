<div>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Historial de Precios - {{ $producto?->nombre ?? 'Producto no encontrado' }}
            </h3>
            <div class="card-tools">
                <button class="btn btn-sm btn-success" wire:click="limpiarFiltros">
                    <i class="fas fa-undo"></i> Limpiar Filtros
                </button>
                <button class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button class="btn btn-sm btn-danger" wire:click="generarPDF" wire:loading.attr="disabled">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

        <div class="card-body" id="contenido-imprimir">
            @if(!$producto)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Producto no encontrado
                </div>
            @else
                {{-- Filtros --}}
                <div class="row mb-3 no-print">
                    <div class="col-md-4">
                        <label>Fecha Inicio</label>
                        <input type="date" wire:model.live="fechaInicio" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label>Fecha Fin</label>
                        <input type="date" wire:model.live="fechaFin" class="form-control form-control-sm">
                    </div>
                </div>

                {{-- Encabezado para impresión --}}
                <div class="print-header text-center mb-4" style="display: none;">
                    <h2>Historial de Precios</h2>
                    <h4>{{ $producto->nombre }} ({{ $producto->codigo }})</h4>
                    <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
                    @if($fechaInicio || $fechaFin)
                        <p>Período: {{ $fechaInicio ?? 'Inicio' }} al {{ $fechaFin ?? 'Actual' }}</p>
                    @endif
                    <hr>
                </div>

                {{-- Información del producto --}}
                <div class="alert alert-info mb-3">
                    <strong>Código:</strong> {{ $producto->codigo }} |
                    <strong>Precio actual:</strong> Bs. {{ number_format($producto->precio_compra, 2) }}
                </div>

                {{-- Tabla de historial --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="bg-light">
                             <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Compra #</th>
                                <th>Precio Anterior</th>
                                <th>Precio Nuevo</th>
                                <th>Diferencia</th>
                                <th>% Cambio</th>
                                <th>Motivo</th>
                             </tr>
                        </thead>
                        <tbody>
                            @forelse($historial as $registro)
                                <tr>
                                    <td>{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $registro->user->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($registro->compra)
                                            #{{ $registro->compra_id }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-right text-muted">
                                        <del>Bs. {{ number_format($registro->precio_anterior, 2) }}</del>
                                    </td>
                                    <td class="text-right text-success font-weight-bold">
                                        Bs. {{ number_format($registro->precio_nuevo, 2) }}
                                    </td>
                                    <td class="text-right {{ $registro->diferencia >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $registro->diferencia >= 0 ? '+' : '' }}Bs. {{ number_format($registro->diferencia, 2) }}
                                    </td>
                                    <td class="text-center {{ $registro->porcentaje_cambio >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $registro->porcentaje_cambio >= 0 ? '+' : '' }}{{ number_format($registro->porcentaje_cambio, 2) }}%
                                    </td>
                                    <td>{{ $registro->motivo }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <i class="fas fa-info-circle"></i>
                                        No hay registros de cambios de precio para este producto.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="mt-3 no-print">
                    {{ $historial->links() }}
                </div>

                {{-- Pie de página para impresión --}}
                <div class="print-footer text-center mt-4" style="display: none;">
                    <hr>
                    <small>Documento generado por Sistema de Gestión</small>
                </div>
            @endif
        </div>
    </div>

    {{-- Estilos CSS para impresión --}}
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .print-header, .print-footer {
                display: block !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .card-header {
                background-color: white !important;
                color: black !important;
                border-bottom: 2px solid #000 !important;
            }

            .card-header .card-tools {
                display: none !important;
            }

            table {
                font-size: 12px;
            }

            th {
                background-color: #f2f2f2 !important;
            }

            .alert-info {
                border: 1px solid #ddd;
                background-color: #f9f9f9 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .text-success {
                color: #28a745 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .text-danger {
                color: #dc3545 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 2cm;
            }

            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</div>
