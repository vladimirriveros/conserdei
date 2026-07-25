<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra #{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .header-empresa {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            padding: 25px 30px;
        }

        .header-empresa h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .header-empresa h1 small {
            font-size: 14px;
            font-weight: normal;
            display: block;
            color: #ffd700;
            margin-top: 5px;
        }

        .info-grid {
            display: flex;
            padding: 20px 25px;
            background: #f8f9fc;
            border-bottom: 2px solid #1a237e;
            gap: 20px;
        }

        .empresa-block {
            flex: 1;
        }

        .empresa-block strong {
            color: #1a237e;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 10px;
        }

        .empresa-block p {
            margin: 5px 0;
            color: #333;
            font-size: 13px;
        }

        .proveedor-block {
            flex: 1;
            background: #e8eaf6;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #1a237e;
        }

        .proveedor-block strong {
            color: #1a237e;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 10px;
        }

        .proveedor-block p {
            margin: 5px 0;
            font-size: 13px;
        }

        .badge {
            background: #ffd700;
            color: #1a237e;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .numero-orden {
            background: #1a237e;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .section-title {
            color: #1a237e;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 25px 15px 25px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1a237e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table {
            width: calc(100% - 50px);
            margin: 0 25px 20px 25px;
            border-collapse: collapse;
            font-size: 13px;
            border: 1px solid #ddd;
        }

        th {
            background: #1a237e;
            color: white;
            padding: 10px 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 12px;
            border: 1px solid #1a237e;
        }

        td {
            padding: 8px 6px;
            border: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f8f9fc;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-box {
            margin: 20px 25px;
            padding: 15px 20px;
            background: #e8f5e9;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            font-weight: bold;
            color: #1a237e;
        }

        .total-box .amount {
            font-size: 20px;
            color: #28a745;
        }

        .observaciones {
            margin: 0 25px 20px 25px;
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            font-size: 13px;
            border: 1px solid #ffeeba;
        }

        .observaciones strong {
            color: #856404;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .mensaje-proveedor {
            margin: 20px 25px;
            padding: 20px;
            background: #d4edda;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            color: #155724;
        }

        .footer {
            background: #1a237e;
            color: white;
            padding: 15px 25px;
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
        }

        .footer-contact {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 10px;
        }

        .marca-badge {
            background: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }

        .alert {
            margin: 20px 25px;
            padding: 15px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            color: #721c24;
        }

        @media only screen and (max-width: 600px) {
            .info-grid {
                flex-direction: column;
            }
            table {
                width: calc(100% - 30px);
                margin: 0 15px;
                font-size: 12px;
            }
            .section-title {
                margin: 15px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- HEADER --}}
        <div class="header-empresa">
            <h1>
                ORDEN DE COMPRA
                <small>Documento de Solicitud</small>
            </h1>
        </div>

        {{-- INFORMACIÓN EMPRESA Y PROVEEDOR --}}
        <div class="info-grid">
            @php
                $empresaNombre = config('app.name', 'CONSERDEI');
                $empresaTelefono = config('app.telefono', '74006143');
                $empresaEmail = config('app.email', 'conserdei.luisfernando@gmail.com');
                $empresaDireccion = config('app.direccion', 'Zona 12 de Octubre, Calle 9 #178');
                $empresaNit = config('app.nit', '6824235016');
            @endphp

            <div class="empresa-block">
                <strong>EMPRESA</strong>
                <p><strong>{{ $empresaNombre }}</strong></p>
                <p>{{ $empresaDireccion }}</p>
                <p>Tel: (591) {{ $empresaTelefono }}</p>
                <p>Email: {{ $empresaEmail }}</p>
                <p>NIT: {{ $empresaNit }}</p>
            </div>

            <div class="proveedor-block">
                <strong>PROVEEDOR</strong>
                <p><strong>{{ $compra->proveedor->nombre ?? 'No especificado' }}</strong></p>
                <p>📞 {{ $compra->proveedor->telefono ?? 'No especificado' }}</p>
                <p>✉️ {{ $compra->proveedor->email ?? 'No especificado' }}</p>
                <p>📍 {{ $compra->proveedor->direccion ?? 'No especificada' }}</p>
                @if($compra->proveedor && $compra->proveedor->nit)
                    <p>NIT: {{ $compra->proveedor->nit }}</p>
                @endif
            </div>
        </div>

        {{-- INFORMACIÓN DE LA ORDEN --}}
        <div style="padding: 0 25px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; background: #f8f9fc; padding: 15px; border-radius: 6px;">
                {{-- <div>
                    <span class="badge">N° DE ORDEN</span>
                    <span class="numero-orden">#{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div> --}}
                <div style="text-align: right;">
                    <span class="badge">FECHA</span>
                    <div style="font-size: 16px; font-weight: bold; color: #1a237e; margin-top: 5px;">
                        {{ $compra->fecha ? date('d/m/Y', strtotime($compra->fecha)) : date('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- DETALLE DE PRODUCTOS --}}
        <div class="section-title">
            🛒 DETALLE DE PRODUCTOS SOLICITADOS
        </div>

        @php
            $productos = $compra->detalles->isNotEmpty()
                ? $compra->detalles
                : session('carrito_compra_' . $compra->id, []);
            $totalGeneral = 0;
        @endphp

        @if(count($productos) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="40%">PRODUCTO</th>
                        <th width="15%">MARCA</th>
                        <th width="10%" class="text-center">CANTIDAD</th>
                        {{-- <th width="15%" class="text-right">ULTIMO PRECIO UNIT.</th>
                        <th width="15%" class="text-right">SUBTOTAL</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $index => $item)
                        @php
                            if ($item instanceof \App\Models\CompraDetalle) {
                                $producto = $item->producto->nombre ?? 'Producto sin nombre';
                                $marca = $item->producto->marca ?? 'Sin marca';
                                $cantidad = $item->cantidad;
                                $precio = $item->precio_unitario ?? 0;
                                $subtotal = $item->subtotal ?? ($cantidad * $precio);
                            } else {
                                $producto = $item['producto_nombre'] ?? 'Producto sin nombre';
                                $marca = $item['marca'] ?? 'Sin marca';
                                $cantidad = $item['cantidad'] ?? 0;
                                $precio = $item['precio_unitario'] ?? 0;
                                $subtotal = $item['subtotal'] ?? ($cantidad * $precio);
                            }
                            $totalGeneral += $subtotal;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $producto }}</td>
                            <td>
                                @if($marca && $marca != 'Sin marca')
                                    <span class="marca-badge">{{ $marca }}</span>
                                @else
                                    {{ $marca }}
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($cantidad, 0) }}</td>
                            {{-- <td class="text-right">Bs {{ number_format($precio, 2) }}</td>
                            <td class="text-right">Bs {{ number_format($subtotal, 2) }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- <div class="total-box">
                <span>TOTAL ORDEN DE COMPRA</span>
                <span class="amount">Bs {{ number_format($totalGeneral, 2) }}</span>
            </div> --}}
        @else
            <div class="alert">
                <strong>⚠️ No hay productos registrados en esta orden</strong>
                <p style="margin-top: 10px;">Por favor, contacte al comprador para más información.</p>
            </div>
        @endif

        {{-- OBSERVACIONES --}}
        @if ($compra->observaciones)
            <div class="observaciones">
                <strong>📝 OBSERVACIONES Y CONDICIONES</strong>
                {{ $compra->observaciones }}
            </div>
        @endif

        {{-- MENSAJE AL PROVEEDOR --}}
        <div class="mensaje-proveedor">
            <strong>📢 Estimado proveedor:</strong><br>
            Por favor, confirme la disponibilidad de los productos solicitados y la fecha estimada de entrega.
            Puede responder a este correo o contactarnos directamente.
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-contact">
                <span>📞 (591) {{ $empresaTelefono }}</span>
                <span>✉️ {{ $empresaEmail }}</span>
                {{-- <span>🌐 www.{{ strtolower(str_replace(' ', '', $empresaNombre)) }}.com</span> --}}
            </div>
            <div style="opacity: 0.9; margin-top: 10px;">
                Sistema de Gestión de Inventario - Documento generado el {{ date('d/m/Y H:i') }}
            </div>
            <div style="font-size: 11px; opacity: 0.7; margin-top: 10px;">
                © {{ date('Y') }} {{ $empresaNombre }} - Todos los derechos reservados
            </div>
        </div>
    </div>
</body>
</html>
