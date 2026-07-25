@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Actualizacion de Productos</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><b>Llene los datos del formulario </b></h3>
                </div>
                <div class="card-body" style="display: block;">

                    <form action="{{ route('productos.update', $producto->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">

                            {{-- SECCION DE LOS DATOS DEL PRODUCTO --}}
                            <div class="col-md-9">

                                {{-- PRIMER ROW --}}
                                <div class="row">

                                    {{-- CATEGORIA DEL PRODUCTO --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="categoria_id">Categoria<b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-tags"></i></span>
                                                </div>
                                                <select name="categoria_id" id="categoria_id" required class="form-control">
                                                    <option value="">Seleccione una categoria</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}"
                                                            data-nombre="{{ $categoria->nombre }}"
                                                            {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                                            {{ $categoria->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('categoria_id')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- CODIGO DEL PRODUCTO --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="codigo"> Código <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                                </div>
                                                <input type="text" value="{{ old('codigo', $producto->codigo) }}"
                                                    class="form-control" id="codigo" name="codigo"
                                                    placeholder="Ej: PROD0001" required style="background-color: #ffffff;">
                                            </div>
                                            @error('codigo')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- NOMBRE DEL PRODUCTO --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nombre"> Nombre <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-box"></i></span>
                                                </div>
                                                <input type="text" value="{{ old('nombre', $producto->nombre) }}"
                                                    class="form-control" id="nombre" name="nombre" required>
                                            </div>
                                            @error('nombre')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- MARCA DEL PRODUCTO --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="marca"> Marca <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">

                                                </div>
                                                <select name="marca" id="marca" class="form-control select2" required>
                                                    <option value="">Seleccione una marca</option>
                                                    <option value="DISMAT"
                                                        {{ old('marca', $producto->marca) == 'DISMAT' ? 'selected' : '' }}>
                                                        DISMAT</option>
                                                    <option value="TIGRE"
                                                        {{ old('marca', $producto->marca) == 'TIGRE' ? 'selected' : '' }}>
                                                        TIGRE</option>
                                                    <option value="ILLIMANI"
                                                        {{ old('marca', $producto->marca) == 'ILLIMANI' ? 'selected' : '' }}>
                                                        ILLIMANI</option>
                                                    <option value="SALADILLO"
                                                        {{ old('marca', $producto->marca) == 'SALADILLO' ? 'selected' : '' }}>
                                                        SALADILLO</option>
                                                    <option value="VASEN"
                                                        {{ old('marca', $producto->marca) == 'VASEN' ? 'selected' : '' }}>
                                                        VASEN</option>
                                                    <option value="ASTRA"
                                                        {{ old('marca', $producto->marca) == 'ASTRA' ? 'selected' : '' }}>
                                                        ASTRA</option>
                                                    <option value="PAVCO"
                                                        {{ old('marca', $producto->marca) == 'PAVCO' ? 'selected' : '' }}>
                                                        PAVCO</option>
                                                    <option value="KRONA"
                                                        {{ old('marca', $producto->marca) == 'KRONA' ? 'selected' : '' }}>
                                                        KRONA</option>
                                                    <option value="IPS"
                                                        {{ old('marca', $producto->marca) == 'IPS' ? 'selected' : '' }}>IPS
                                                    </option>
                                                    <option value="FG"
                                                        {{ old('marca', $producto->marca) == 'FG' ? 'selected' : '' }}>FG
                                                    </option>
                                                    <option value="PARABOND"
                                                        {{ old('marca', $producto->marca) == 'PARABOND' ? 'selected' : '' }}>
                                                        PARABOND</option>
                                                    <option value="KILLING"
                                                        {{ old('marca', $producto->marca) == 'KILLING' ? 'selected' : '' }}>
                                                        KILLING</option>
                                                    <option value="CRISTYS"
                                                        {{ old('marca', $producto->marca) == 'CRISTYS' ? 'selected' : '' }}>
                                                        CRISTYS</option>
                                                    <option value="ULTRAPEG"
                                                        {{ old('marca', $producto->marca) == 'ULTRAPEG' ? 'selected' : '' }}>
                                                        ULTRAPEG</option>
                                                </select>
                                            </div>
                                            @error('marca')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- CAMPO NORMA (condicional) --}}
                                <div class="row" id="norma-field" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="card card-info">
                                            <div class="card-header">
                                                <h3 class="card-title"><b>Especificaciones de Plomería</b></h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="norma">Norma Técnica <b
                                                                    style="color: red">(*)</b></label>
                                                            <select name="norma" id="norma"
                                                                class="form-control select2-norma">
                                                                <option value="">Seleccione una norma</option>
                                                                <option value="ASTM D1785"
                                                                    {{ old('norma', $producto->norma ?? '') == 'ASTM D1785' ? 'selected' : '' }}>
                                                                    ASTM D1785 - Tubería PVC</option>
                                                                <option value="ASTM D2466"
                                                                    {{ old('norma', $producto->norma ?? '') == 'ASTM D2466' ? 'selected' : '' }}>
                                                                    ASTM D2466 - Conexiones PVC</option>
                                                                <option value="ASTM D2846"
                                                                    {{ old('norma', $producto->norma ?? '') == 'ASTM D2846' ? 'selected' : '' }}>
                                                                    ASTM D2846 - CPVC</option>
                                                                <option value="ISO 1452"
                                                                    {{ old('norma', $producto->norma ?? '') == 'ISO 1452' ? 'selected' : '' }}>
                                                                    ISO 1452 - Sistemas de tuberías plásticas</option>
                                                                <option value="NBR 5648"
                                                                    {{ old('norma', $producto->norma ?? '') == 'NBR 5648' ? 'selected' : '' }}>
                                                                    NBR 5648 - Tubos PVC</option>
                                                                <option value="NTP 399.002"
                                                                    {{ old('norma', $producto->norma ?? '') == 'NTP 399.002' ? 'selected' : '' }}>
                                                                    NTP 399.002 - Tuberías PVC</option>
                                                                <option value="NTP 399.019"
                                                                    {{ old('norma', $producto->norma ?? '') == 'NTP 399.019' ? 'selected' : '' }}>
                                                                    NTP 399.019 - Conexiones PVC</option>
                                                            </select>
                                                            @error('norma')
                                                                <small style="color: red">{{ $message }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="presion">Presión de Trabajo</label>
                                                            <select name="presion" id="presion" class="form-control">
                                                                <option value="">Seleccione presión</option>
                                                                <option value="150 PSI"
                                                                    {{ old('presion', $producto->presion ?? '') == '150 PSI' ? 'selected' : '' }}>
                                                                    150 PSI</option>
                                                                <option value="200 PSI"
                                                                    {{ old('presion', $producto->presion ?? '') == '200 PSI' ? 'selected' : '' }}>
                                                                    200 PSI</option>
                                                                <option value="250 PSI"
                                                                    {{ old('presion', $producto->presion ?? '') == '250 PSI' ? 'selected' : '' }}>
                                                                    250 PSI</option>
                                                                <option value="315 PSI"
                                                                    {{ old('presion', $producto->presion ?? '') == '315 PSI' ? 'selected' : '' }}>
                                                                    315 PSI</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="diametro">Diámetro</label>
                                                            <select name="diametro" id="diametro" class="form-control">
                                                                <option value="">Seleccione diámetro</option>
                                                                <option value="1/2"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '1/2' ? 'selected' : '' }}>
                                                                    1/2"</option>
                                                                <option value="3/4"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '3/4' ? 'selected' : '' }}>
                                                                    3/4"</option>
                                                                <option value="1"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '1' ? 'selected' : '' }}>
                                                                    1"</option>
                                                                <option value="1 1/4"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '1 1/4' ? 'selected' : '' }}>
                                                                    1 1/4"</option>
                                                                <option value="1 1/2"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '1 1/2' ? 'selected' : '' }}>
                                                                    1 1/2"</option>
                                                                <option value="2"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '2' ? 'selected' : '' }}>
                                                                    2"</option>
                                                                <option value="3"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '3' ? 'selected' : '' }}>
                                                                    3"</option>
                                                                <option value="4"
                                                                    {{ old('diametro', $producto->diametro ?? '') == '4' ? 'selected' : '' }}>
                                                                    4"</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- SEGUNDO ROW --}}
                                <div class="row">

                                    {{-- DESCRIPCION DEL PRODUCTO --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="descripcion">Descripción <b style="color: red">(*)</b></label>
                                            <div class="editor-wrapper">
                                                <textarea id="descripcion" name="descripcion">{{ $producto->descripcion }}</textarea>
                                            </div>
                                            @error('descripcion')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- TERCER ROW --}}
                                <div class="row">

                                    {{-- PRECIO COMPRA DEL PRODUCTO --}}
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="precio_compra"> Precio Compra <b
                                                    style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-money-bill-wave"></i></span>
                                                </div>
                                                <input style="text-align: center" type="number" step="0.01"
                                                    value="{{ old('precio_compra', $producto->precio_compra) }}"
                                                    class="form-control" id="precio_compra" name="precio_compra"
                                                    required>
                                            </div>
                                            @error('precio_compra')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- PRECIO VENTA DEL PRODUCTO --}}
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="precio_venta"> Precio Venta <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-money-bill-wave"></i></span>
                                                </div>
                                                <input style="text-align: center" type="number" step="0.01"
                                                    min="0.01"
                                                    value="{{ old('precio_venta', $producto->precio_venta) }}"
                                                    class="form-control" id="precio_venta" name="precio_venta"
                                                    placeholder="Ingrese el precio de venta del producto" required>
                                            </div>
                                            @error('precio_venta')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- PORCENTAJE DE GANANCIA --}}
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="porcentaje"> Porcentaje Ganancia <b
                                                    style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                                </div>
                                                <input style="text-align: center" type="number" step="0.01"
                                                    min="0"
                                                    value="{{ old('porcentaje', $producto->porcentaje_ganancia) }}"
                                                    class="form-control" id="porcentaje" name="porcentaje"
                                                    placeholder="Ingrese el porcentaje de ganancia" required>
                                            </div>
                                            @error('porcentaje')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            // Obtener referencia a los inputs
                                            const precioCompraInput = document.getElementById('precio_compra');
                                            const precioVentaInput = document.getElementById('precio_venta');
                                            const porcentajeInput = document.getElementById('porcentaje');

                                            // Función para calcular porcentaje desde precio de venta
                                            function calcularPorcentaje() {
                                                const precioCompra = parseFloat(precioCompraInput.value);
                                                const precioVenta = parseFloat(precioVentaInput.value);
                                                if (!isNaN(precioCompra) && !isNaN(precioVenta) && precioCompra > 0) {
                                                    const porcentajeGanancia = ((precioVenta - precioCompra) / precioCompra) * 100;
                                                    porcentajeInput.value = porcentajeGanancia.toFixed(2);
                                                }
                                            }

                                            // Función para calcular precio de venta desde porcentaje
                                            function calcularPrecioVenta() {
                                                const precioCompra = parseFloat(precioCompraInput.value);
                                                const porcentajeGanancia = parseFloat(porcentajeInput.value);
                                                if (!isNaN(precioCompra) && !isNaN(porcentajeGanancia) && precioCompra > 0) {
                                                    const precioVentaCalculado = precioCompra * (1 + (porcentajeGanancia / 100));
                                                    precioVentaInput.value = precioVentaCalculado.toFixed(2);
                                                }
                                            }

                                            // Agregar event listeners
                                            if (precioVentaInput) {
                                                precioVentaInput.addEventListener('input', calcularPorcentaje);
                                            }

                                            if (porcentajeInput) {
                                                porcentajeInput.addEventListener('input', calcularPrecioVenta);
                                            }

                                            // Si también quieres que se actualice cuando cambie el precio de compra
                                            if (precioCompraInput) {
                                                precioCompraInput.addEventListener('input', function() {
                                                    // Determinar qué campo está siendo modificado actualmente
                                                    if (document.activeElement === precioVentaInput) {
                                                        calcularPorcentaje();
                                                    } else if (document.activeElement === porcentajeInput) {
                                                        calcularPrecioVenta();
                                                    }
                                                });
                                            }

                                            // Inicializar los valores (opcional, para asegurar que los cálculos sean correctos)
                                            // Esto es útil si los valores vienen de la base de datos
                                            if (precioCompraInput && precioVentaInput && porcentajeInput) {
                                                // Pequeño retraso para asegurar que todo esté cargado
                                                setTimeout(() => {
                                                    calcularPorcentaje();
                                                }, 100);
                                            }
                                        });
                                    </script>

                                    {{-- UNIDAD DE MEDIDA DEL PRODUCTO --}}
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="unidad_medida"> Unidad de Medida <b
                                                    style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-balance-scale"></i></span>
                                                </div>
                                                <select name="unidad_medida" id="unidad_medida" class="form-control"
                                                    required>
                                                    <option value="" disabled>Seleccione una unidad de medida
                                                    </option>
                                                    <option value="UNIDAD"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'UNIDAD' ? 'selected' : '' }}>
                                                        Unidad</option>
                                                    <option value="LITRO"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'LITRO' ? 'selected' : '' }}>
                                                        Litro</option>
                                                    <option value="KILOGRAMO"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'KILOGRAMO' ? 'selected' : '' }}>
                                                        Kilogramo</option>
                                                    <option value="METRO"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'METRO' ? 'selected' : '' }}>
                                                        Metro</option>
                                                    {{-- <option value="PAQUETE"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'PAQUETE' ? 'selected' : '' }}>
                                                        Paquete</option>
                                                    <option value="CAJA"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'CAJA' ? 'selected' : '' }}>
                                                        Caja</option> --}}
                                                    <option value="BARRA"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'BARRA' ? 'selected' : '' }}>
                                                        Barra</option>
                                                    <option value="PIEZA"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'PIEZA' ? 'selected' : '' }}>
                                                        Pieza</option>
                                                    {{-- <option value="ROLLO"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'ROLLO' ? 'selected' : '' }}>
                                                        Rollo</option> --}}
                                                    {{-- <option value="DOCENA"
                                                        {{ old('unidad_medida', $producto->unidad_medida) == 'DOCENA' ? 'selected' : '' }}>
                                                        Docena</option> --}}
                                                </select>
                                            </div>
                                            @error('unidad_medida')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- STOCK MINIMO DEL PRODUCTO --}}
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="stock_minimo"> Stock Mínimo <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-arrow-down"></i></span>
                                                </div>
                                                <input style="text-align: center" type="number" min="1"
                                                    value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                                                    class="form-control" id="stock_minimo" name="stock_minimo"
                                                    placeholder="Stock mínimo" required>
                                            </div>
                                            @error('stock_minimo')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- STOCK MAXIMO DEL PRODUCTO --}}
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="stock_maximo"> Stock Máximo <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-arrow-up"></i></span>
                                                </div>
                                                <input style="text-align: center" type="number" min="1"
                                                    value="{{ old('stock_maximo', $producto->stock_maximo) }}"
                                                    class="form-control" id="stock_maximo" name="stock_maximo"
                                                    placeholder="Stock máximo" required>
                                            </div>
                                            @error('stock_maximo')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- ESTADO DEL PRODUCTO --}}
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="estado"> Estado <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-check-circle"></i></span>
                                                </div>
                                                <select name="estado" id="estado" class="form-control" required>
                                                    <option value="" disabled>Seleccione el estado</option>
                                                    <option value="1"
                                                        {{ old('estado', $producto->estado) == '1' ? 'selected' : '' }}>
                                                        Activo</option>
                                                    <option value="0"
                                                        {{ old('estado', $producto->estado) == '0' ? 'selected' : '' }}>
                                                        Inactivo</option>
                                                </select>
                                            </div>
                                            @error('estado')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- SECCION DE LA IMAGEN DEL PRODUCTO --}}
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="imagen"> Imagen del producto </label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                                                </div>
                                                <input type="file" class="form-control" id="imagen" name="imagen"
                                                    accept="image/*" onchange="previewImage(event)">
                                            </div>

                                            {{-- IMAGEN PREVIEW --}}
                                            <br>
                                            <img id="imgPreview" src="{{ asset('storage/' . $producto->imagen) }}"
                                                width="100%" alt="Vista previa de la imagen">
                                            @error('imagen')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCION PARA CAMBIO DE STOCK (SOLO SE MUESTRA SI SE MODIFICAN) --}}
                        <hr>
                        <div class="row" id="stock-change-fields" style="display: none;">
                            <div class="col-md-12">
                                <div class="card card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title"><b><i class="fas fa-exchange-alt"></i> Registro de Cambio de Stock</b></h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="motivo_stock">Motivo del cambio</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i
                                                                    class="fas fa-question-circle"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" id="motivo_stock"
                                                            name="motivo_stock"
                                                            placeholder="Ej: Reajuste de inventario, Cambio de política, etc.">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="observaciones_stock">Observaciones</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i
                                                                    class="fas fa-comment"></i></span>
                                                        </div>
                                                        <textarea class="form-control" id="observaciones_stock" name="observaciones_stock" rows="1"
                                                            placeholder="Comentario adicional sobre el cambio..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .ck.ck-editor {
            width: 100% !important;
        }

        .ck-editor__editable {
            width: 100% !important;
            min-height: 300px;
            box-sizing: border-box;
        }

        .ck.ck-toolbar {
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .ck-editor__editable {
                min-height: 250px;
                padding: 10px;
            }
        }

        .select2-container .select2-selection--single {
            height: 38px;
        }

        .select2-selection__rendered {
            line-height: 36px !important;
        }

        .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#marca').select2({
                placeholder: "Seleccione una marca",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        ClassicEditor
            .create(document.querySelector('#descripcion'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', '|',
                        'link', 'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'alignment', '|',
                        'blockQuote', 'insertTable', 'mediaEmbed', '|',
                        'undo', 'redo', '|',
                        'fontBackgroundColor', 'fontColor', 'fontSize', 'fontFamily', '|',
                        'code', 'codeBlock', 'htmlEmbed', '|',
                        'sourceEditing'
                    ],
                    shouldNotGroupWhenFull: true
                },
                language: 'es'
            })
            .then(editor => {
                const editorEl = editor.ui.view.element;
                editorEl.style.width = '100%';
                editorEl.querySelector('.ck-editor__editable').style.width = '100%';
            })
            .catch(error => {
                console.error(error);
            });
    </script>

    <script>
        // Detectar cambios en stock_minimo y stock_maximo para mostrar el campo de motivo
        document.addEventListener('DOMContentLoaded', function() {
            const stockMinimoInput = document.getElementById('stock_minimo');
            const stockMaximoInput = document.getElementById('stock_maximo');
            const stockChangeFields = document.getElementById('stock-change-fields');

            // Guardar valores originales
            const originalStockMinimo = stockMinimoInput ? stockMinimoInput.value : null;
            const originalStockMaximo = stockMaximoInput ? stockMaximoInput.value : null;

            function checkStockChanges() {
                if (!stockMinimoInput || !stockMaximoInput) return;

                const currentMinimo = stockMinimoInput.value;
                const currentMaximo = stockMaximoInput.value;

                const minimoCambio = (currentMinimo !== originalStockMinimo);
                const maximoCambio = (currentMaximo !== originalStockMaximo);

                if (minimoCambio || maximoCambio) {
                    stockChangeFields.style.display = 'block';
                } else {
                    stockChangeFields.style.display = 'none';
                    // Limpiar campos si se ocultan
                    document.getElementById('motivo_stock').value = '';
                    document.getElementById('observaciones_stock').value = '';
                }
            }

            if (stockMinimoInput) {
                stockMinimoInput.addEventListener('input', checkStockChanges);
            }
            if (stockMaximoInput) {
                stockMaximoInput.addEventListener('input', checkStockChanges);
            }
        });
    </script>

    <script>
        function toggleNormaField() {
            const categoriaSelect = document.getElementById('categoria_id');
            const normaField = document.getElementById('norma-field');
            const selectedOption = categoriaSelect.options[categoriaSelect.selectedIndex];
            const normaInput = document.getElementById('norma');

            if (selectedOption && selectedOption.dataset.nombre) {
                const nombreCategoria = selectedOption.dataset.nombre.trim().toUpperCase();

                if (nombreCategoria.includes('PLOMERIA')) {
                    normaField.style.display = 'block';
                    if (normaInput) {
                        normaInput.setAttribute('required', 'required');
                    }
                } else {
                    normaField.style.display = 'none';
                    if (normaInput) {
                        normaInput.removeAttribute('required');
                    }
                }
            } else {
                normaField.style.display = 'none';
                if (normaInput) {
                    normaInput.removeAttribute('required');
                }
            }
        }

        function previewImage(event) {
            const input = event.target;
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgPreview = document.getElementById('imgPreview');
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                toggleNormaField();
            }, 200);

            const categoriaSelect = document.getElementById('categoria_id');
            if (categoriaSelect) {
                categoriaSelect.addEventListener('change', toggleNormaField);
            }
        });
    </script>
@stop
