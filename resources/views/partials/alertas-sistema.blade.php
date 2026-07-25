{{-- resources/views/partials/alertas-sistema.blade.php --}}

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cola de alertas
            let colaAlertas = [];
            let mostrandoAlerta = false;

            function mostrarSiguienteAlerta() {
                if (colaAlertas.length === 0) {
                    mostrandoAlerta = false;
                    return;
                }

                mostrandoAlerta = true;
                const alerta = colaAlertas.shift();

                Swal.fire(alerta.opciones).then((result) => {
                    if (alerta.callback) {
                        alerta.callback(result);
                    }
                    mostrarSiguienteAlerta();
                });
            }

            window.agregarAlerta = function(opciones, callback) {
                colaAlertas.push({
                    opciones,
                    callback
                });
                if (!mostrandoAlerta) {
                    mostrarSiguienteAlerta();
                }
            }
            // ============================================
            // ALERTA DE PRODUCTOS SIN STOCK (SOLO stock = 0)
            // ============================================
            function verificarStockBajo() {
                if (!sessionStorage.getItem('stockAceptado')) {
                    fetch("{{ route('alertas.stock-bajo') }}")
                        .then(response => response.json())
                        .then(data => {
                            if (data.alerta && data.productos && data.productos.length > 0) {
                                // Construir lista HTML de productos sin stock
                                let productosHtml =
                                    '<ul style="text-align: left; max-height: 200px; overflow-y: auto; margin: 10px 0; padding-left: 20px;">';
                                data.productos.forEach(p => {
                                    productosHtml += `
                                        <li style="margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                                            <strong>${p.nombre}</strong><br>
                                            <small style="color: red;">❌ Código: ${p.codigo} | Stock: 0 unidades</small>
                                        </li>
                                        `;
                                });
                                productosHtml += '</ul>';

                                const sucursalId = {{ auth()->user()->sucursal_id ?? 1 }};
                                const idsProductos = data.productos.map(p => p.producto_id).join(',');

                                agregarAlerta({
                                    title: "❌ Productos sin stock",
                                    html: `
                                        <p>Hay <b style="color: red;">${data.total}</b> productos que no tienen stock disponible:</p>
                                        ${productosHtml}
                                        <p class="text-danger small mt-2">⚠️ Estos productos no pueden venderse. Debes gestionar una compra o darles de baja.</p>
                                    `,
                                    icon: "error",
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    showConfirmButton: true,
                                    confirmButtonText: "🛒 Gestionar compra",
                                    denyButtonText: "📦 Ver inventario",
                                    cancelButtonText: "✅ No mostrar más",
                                    confirmButtonColor: '#28a745',
                                    denyButtonColor: '#3085d6',
                                    cancelButtonColor: '#ff8c00',
                                    reverseButtons: true
                                }, (result) => {
                                    if (result.isConfirmed) {
                                        // Gestionar compra - Ir a crear compra con estos productos
                                        window.location.href =
                                            "{{ route('compras.create') }}?productos=" + idsProductos;
                                    } else if (result.isDenied) {
                                        // Ver inventario - Ir a inventario de sucursal
                                        window.location.href =
                                            "{{ route('mostrar_inventario_por_sucursal.show', ['id' => '__ID__']) }}"
                                            .replace('__ID__', sucursalId);
                                    } else {
                                        // No mostrar más esta alerta
                                        sessionStorage.setItem('stockAceptado', 'true');
                                    }
                                });
                            }
                        })
                        .catch(error => console.error('Error verificando productos sin stock:', error));
                }
            }

            // ============================================
            // ALERTA DE LOTES POR VENCER
            // ============================================
            function verificarLotesPorVencer() {
                if (!sessionStorage.getItem('lotesPorVencerAceptado')) {
                    fetch("{{ route('alertas.lotes-por-vencer') }}")
                        .then(response => response.json())
                        .then(data => {
                            if (data.alerta && data.lotes && data.lotes.length > 0) {
                                // Construir lista HTML de lotes
                                let lotesHtml =
                                    '<ul style="text-align: left; max-height: 200px; overflow-y: auto; margin: 10px 0; padding-left: 20px;">';
                                data.lotes.forEach(lote => {
                                    lotesHtml += `
                            <li style="margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                                <strong>${lote.producto_nombre}</strong><br>
                                <small>📦 Lote: ${lote.codigo_lote} | Stock: ${lote.cantidad_actual} | Vence: ${lote.fecha_vencimiento}</small>
                            </li>
                        `;
                                });
                                lotesHtml += '</ul>';

                                agregarAlerta({
                                    title: "📅 Lotes próximos a vencer",
                                    html: `
                            <p>Hay <b>${data.total}</b> lotes que vencen en menos de <b> 7 </b> días:</p>
                            ${lotesHtml}
                            <p class="text-muted small mt-2">Se recomienda revisar los lotes para gestionar su salida antes del vencimiento.</p>
                        `,
                                    icon: "warning",
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    showConfirmButton: true,
                                    confirmButtonText: "🔍 Ir a lotes",
                                    denyButtonText: "✅ No mostrar más",
                                    cancelButtonText: "Recordar Después",
                                    confirmButtonColor: '#3085d6',
                                    denyButtonColor: '#ff8c00',
                                    cancelButtonColor: '#6c757d',
                                    reverseButtons: true
                                }, (result) => {
                                    if (result.isConfirmed) {
                                        // Ir a la página de lotes
                                        window.location.href = "{{ route('lotes.index') }}";
                                    } else if (result.isDenied) {
                                        // No mostrar más esta alerta
                                        sessionStorage.setItem('lotesPorVencerAceptado', 'true');
                                    }
                                    // Si es cancel (Aceptar), no hace nada y la alerta volverá a mostrarse
                                });
                            }
                        })
                        .catch(error => console.error('Error verificando lotes:', error));
                }
            }

            // ============================================
            // ALERTA DE LOTES VENCIDOS
            // ============================================
            function verificarLotesVencidos() {
                console.log('=== verificarLotesVencidos() fue llamada ===');
                if (!sessionStorage.getItem('lotesVencidosAceptado')) {
                    console.log('No está aceptada, haciendo fetch...');
                    fetch("{{ route('alertas.lotes-vencidos') }}")
                        .then(response => response.json())
                        .then(data => {
                            console.log('Respuesta recibida:', data);
                            if (data.alerta && data.lotes && data.lotes.length > 0) {
                                console.log('Mostrando alerta de lotes vencidos');
                                // Construir lista HTML de lotes vencidos
                                let lotesHtml =
                                    '<ul style="text-align: left; max-height: 200px; overflow-y: auto; margin: 10px 0; padding-left: 20px;">';
                                data.lotes.forEach(lote => {
                                    let diasVencidosTexto = lote.dias_vencidos === 1 ? '1 día' :
                                        `${lote.dias_vencidos} días`;
                                    lotesHtml += `
                                        <li style="margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                                            <strong>${lote.producto_nombre}</strong><br>
                                            <small>⚠️ Lote: ${lote.codigo_lote} | Stock: ${lote.cantidad_actual} | Vencido hace: ${diasVencidosTexto}</small>
                                        </li>
                                    `;
                                });
                                lotesHtml += '</ul>';

                                agregarAlerta({
                                    title: "⚠️ ¡Atención! Lotes vencidos",
                                    html: `
                                        <p>Hay <b style="color: red;">${data.total}</b> lotes que ya están <b>VENCIDOS</b> y aún tienen stock disponible:</p>
                                        ${lotesHtml}
                                        <p class="text-danger small mt-2">⚠️ Estos productos NO deben venderse. Debes gestionar su salida como pérdida.</p>
                                    `,
                                    icon: "error",
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    showConfirmButton: true,
                                    confirmButtonText: "🚛 Gestionar salidas",
                                    denyButtonText: "❌ No mostrar más",
                                    cancelButtonText: "Recordar después",
                                    confirmButtonColor: '#dc3545',
                                    denyButtonColor: '#ff8c00',
                                    cancelButtonColor: '#6c757d',
                                    reverseButtons: true
                                }, (result) => {
                                    if (result.isConfirmed) {
                                        // Ir a gestionar salidas de lotes vencidos
                                        const sucursalId = {{ auth()->user()->sucursal_id ?? 1 }};
                                        window.location.href =
                                            "{{ route('lotes.vencidos.sucursal', ['id' => '__ID__']) }}"
                                            .replace('__ID__', sucursalId);
                                    } else if (result.isDenied) {
                                        // No mostrar más esta alerta
                                        sessionStorage.setItem('lotesVencidosAceptado', 'true');
                                    }
                                    // Si es cancel (Recordar después), no hace nada y la alerta volverá a mostrarse
                                });
                            } else {
                                console.log('No hay lotes vencidos con stock disponible');
                            }
                        })
                        .catch(error => console.error('Error verificando lotes vencidos:', error));
                } else {
                    console.log('lotesVencidosAceptado = true, no se muestra');
                }
            }


            // ============================================
            // ALERTA DE PRODUCTOS POR REORDENAR (ROP)
            // ============================================
            function verificarROP() {
                if (!sessionStorage.getItem('ropAceptado')) {//entra si presiono "No mostrar más" o si nunca se mostró antes
                    const sucursalId = {{ auth()->user()->sucursal_id ?? 1 }};

                    // CAMBIADO: antes era 'alerta.rop'
                    fetch("{{ route('alertas.rop') }}?sucursal_id=" + sucursalId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.alerta && data.productos && data.productos.length > 0) {//entra si hay datos del controller
                                let productosHtml =
                                    '<ul style="text-align: left; max-height: 200px; overflow-y: auto;">';
                                data.productos.slice(0, 5).forEach(p => {//Toma los primeros 5
                                    productosHtml +=
                                        `<li><strong>${p.nombre}</strong>: Stock ${p.stock_actual} / Mínimo ${p.stock_minimo} / ROP ${p.rop}</li>`;
                                });
                                if (data.productos.length > 5) {
                                    productosHtml += `<li><em>... y ${data.productos.length - 5} más</em></li>`;
                                }
                                productosHtml += '</ul>';

                                agregarAlerta({
                                    title: "⚠️ Productos con stock Bajo",
                                    html: `Hay <b>${data.total}</b> productos que necesitan reorden:<br>${productosHtml}`,
                                    icon: "warning",
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonText: "🛒 Ir a comprar",
                                    denyButtonText: "✅ No mostrar más",
                                    cancelButtonText: "⏰ Recordar después",
                                    confirmButtonColor: '#28a745',
                                    denyButtonColor: '#ff8c00',
                                    cancelButtonColor: '#6c757d',
                                    reverseButtons: true
                                }, (result) => {
                                    if (result.isConfirmed) {
                                        const ids = data.productos.map(p => p.producto_id).join(',');
                                        window.location.href =
                                            "{{ route('compras.create') }}?productos=" + ids;
                                    } else if (result.isDenied) {
                                        sessionStorage.setItem('ropAceptado', 'true');
                                    }
                                });
                            }
                        })
                        .catch(error => console.error('Error verificando ROP:', error));
                }
            }

            // Ejecutar todas las verificaciones
            verificarStockBajo();
            verificarLotesPorVencer();
            verificarLotesVencidos();
            verificarROP();
        });
    </script>
@endpush
