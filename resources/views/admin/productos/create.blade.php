@extends('adminlte::page')

@section('title', 'Crear Productos')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Creación de Productos</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Llene los datos del formulario </b></h3>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="display: block;">

                    <form action=" {{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

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
                                                            data-nombre="{{ $categoria->nombre }}" {{-- <-- AGREGAR ESTA LÍNEA --}}
                                                            {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                                            {{ $categoria->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('nombre')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- // NOMBRE DEL PRODUCTO --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nombre"> Nombre <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-box"></i></span>
                                                </div>
                                                <input type="text" value="{{ old('nombre') }}" class="form-control"
                                                    id="nombre" name="nombre"
                                                    placeholder="Ingrese el nombre del producto" required>
                                            </div>
                                            @error('nombre')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- CODIGO DEL PRODUCTO --}}
                                    {{-- CODIGO DEL PRODUCTO --}}
                                    {{-- CODIGO DEL PRODUCTO --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="codigo"> Código <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                                </div>
                                                <input type="text" value="{{ old('codigo', $nuevoCodigo ?? '') }}"
                                                    class="form-control" id="codigo" name="codigo"
                                                    placeholder="Código del producto" required
                                                    style="background-color: #ffffff; font-weight: bold;">
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-info text-white" id="codigo-estado">
                                                        <i class="fas fa-spinner fa-spin" style="display: none;"></i>
                                                        <i class="fas fa-check-circle"
                                                            style="color: white; display: none;"></i>
                                                        <i class="fas fa-times-circle"
                                                            style="color: white; display: none;"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            @error('codigo')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- // MARCA DEL PRODUCTO --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="marca"> Marca <b style="color: red">(*)</b></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    {{-- <span class="input-group-text"><i
                                                            class="fas fa-balance-scale"></i></span> --}}
                                                </div>
                                                <select name="marca" id="marca" class="form-control select2" required>
                                                    <option value="" disabled selected>Seleccione una marca</option>
                                                    <option value="DISMAT"
                                                        {{ old('marca') == 'DISMAT' ? 'selected' : '' }}>DISMAT</option>
                                                    <option value="TIGRE" {{ old('marca') == 'TIGRE' ? 'selected' : '' }}>
                                                        TIGRE</option>
                                                    <option value="ILLIMANI"
                                                        {{ old('marca') == 'ILLIMANI' ? 'selected' : '' }}>ILLIMANI
                                                    </option>
                                                    <option value="SALADILLO"
                                                        {{ old('marca') == 'SALADILLO' ? 'selected' : '' }}>SALADILLO
                                                    </option>
                                                    <option value="VASEN" {{ old('marca') == 'VASEN' ? 'selected' : '' }}>
                                                        VASEN</option>
                                                    <option value="ASTRA" {{ old('marca') == 'ASTRA' ? 'selected' : '' }}>
                                                        ASTRA</option>
                                                    <option value="PAVCO" {{ old('marca') == 'PAVCO' ? 'selected' : '' }}>
                                                        PAVCO</option>
                                                    <option value="KRONA" {{ old('marca') == 'KRONA' ? 'selected' : '' }}>
                                                        KRONA</option>
                                                    <option value="IPS" {{ old('marca') == 'IPS' ? 'selected' : '' }}>
                                                        IPS</option>
                                                    <option value="FG" {{ old('marca') == 'FG' ? 'selected' : '' }}>FG
                                                    </option>
                                                    <option value="PARABOND"
                                                        {{ old('marca') == 'PARABOND' ? 'selected' : '' }}>PARABOND
                                                    </option>
                                                    <option value="KILLING"
                                                        {{ old('marca') == 'KILLING' ? 'selected' : '' }}>KILLING</option>
                                                    <option value="CRISTYS"
                                                        {{ old('marca') == 'CRISTYS' ? 'selected' : '' }}>CRISTYS</option>
                                                    <option value="ULTRAPEG"
                                                        {{ old('marca') == 'ULTRAPEG' ? 'selected' : '' }}>ULTRAPEG
                                                    </option>
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
                                                                <option value="150">150 PSI</option>
                                                                <option value="200">200 PSI</option>
                                                                <option value="250">250 PSI</option>
                                                                <option value="315">315 PSI</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="diametro">Diámetro</label>
                                                            <select name="diametro" id="diametro" class="form-control">
                                                                <option value="">Seleccione diámetro</option>
                                                                <option value="1/2">1/2"</option>
                                                                <option value="3/4">3/4"</option>
                                                                <option value="1">1"</option>
                                                                <option value="1 1/4">1 1/4"</option>
                                                                <option value="1 1/2">1 1/2"</option>
                                                                <option value="2">2"</option>
                                                                <option value="3">3"</option>
                                                                <option value="4">4"</option>
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
                                                <textarea id="descripcion" name="descripcion"></textarea>
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
                                                    min="0.01" value="{{ old('precio_compra') }}"
                                                    class="form-control" id="precio_compra" name="precio_compra"
                                                    placeholder="Ingrese el precio de compra del producto" required>
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
                                                    min="0.01" value="{{ old('precio_venta') }}"
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
                                                    <span class="input-group-text"><i
                                                            class="fas fa-money-bill-wave"></i></span>
                                                </div>
                                                <input style="text-align: center" type="number" step="0.01"
                                                    min="0" value="{{ old('porcentaje') }}" class="form-control"
                                                    id="porcentaje" name="porcentaje"
                                                    placeholder="Ingrese el porcentaje de ganancia" required>
                                            </div>
                                            @error('porcentaje')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const precioCompraInput = document.getElementById('precio_compra');
                                            const precioVentaInput = document.getElementById('precio_venta');
                                            const porcentajeInput = document.getElementById('porcentaje');

                                            // Calcular el porcentaje cuando se ingresa el precio de venta
                                            precioVentaInput.addEventListener('input', function() {
                                                const precioCompra = parseFloat(precioCompraInput.value);
                                                const precioVenta = parseFloat(precioVentaInput.value);
                                                if (!isNaN(precioCompra) && !isNaN(precioVenta) && precioCompra > 0) {
                                                    const porcentajeGanancia = ((precioVenta - precioCompra) / precioCompra) * 100;
                                                    porcentajeInput.value = porcentajeGanancia.toFixed(2);
                                                }
                                            });

                                            // Calcular el precio de venta cuando se ingresa el porcentaje
                                            porcentajeInput.addEventListener('input', function() {
                                                const precioCompra = parseFloat(precioCompraInput.value);
                                                const porcentajeGanancia = parseFloat(porcentajeInput.value);
                                                if (!isNaN(precioCompra) && !isNaN(porcentajeGanancia) && precioCompra > 0) {
                                                    const precioVentaCalculado = precioCompra * (1 + (porcentajeGanancia / 100));
                                                    precioVentaInput.value = precioVentaCalculado.toFixed(2);
                                                }
                                            });
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
                                                    <option value="" disabled selected>Seleccione una unidad de
                                                        medida</option>
                                                    <option value="unidad"
                                                        {{ old('unidad_medida') == 'unidad' ? 'selected' : '' }}>Unidad
                                                    </option>
                                                    <option value="litro"
                                                        {{ old('unidad_medida') == 'litro' ? 'selected' : '' }}>Litro
                                                    </option>
                                                    <option value="kilogramo"
                                                        {{ old('unidad_medida') == 'kilogramo' ? 'selected' : '' }}>
                                                        Kilogramo</option>
                                                    <option value="metro"
                                                        {{ old('unidad_medida') == 'metro' ? 'selected' : '' }}>Metro
                                                    </option>
                                                    {{-- <option value="paquete"
                                                        {{ old('unidad_medida') == 'paquete' ? 'selected' : '' }}>Paquete
                                                    </option> --}}
                                                    {{-- <option value="caja"
                                                        {{ old('unidad_medida') == 'caja' ? 'selected' : '' }}>Caja
                                                    </option> --}}
                                                    <option value="barra"
                                                        {{ old('unidad_medida') == 'barra' ? 'selected' : '' }}>Barra
                                                    </option>
                                                    <option value="pieza"
                                                        {{ old('unidad_medida') == 'pieza' ? 'selected' : '' }}>Pieza
                                                    </option>
                                                    {{-- <option value="rollo"
                                                        {{ old('unidad_medida') == 'rollo' ? 'selected' : '' }}>Rollo
                                                    </option> --}}
                                                    {{-- <option value="docena"
                                                        {{ old('unidad_medida') == 'docena' ? 'selected' : '' }}>Docena
                                                    </option> --}}
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
                                                    <span class="input-group-text"><i
                                                            class="fas fa-arrow-down"></i></span>
                                                </div>
                                                <input style="text-align: center" type="number" min="1"
                                                    value="{{ old('stock_minimo') }}" class="form-control"
                                                    id="stock_minimo" name="stock_minimo"
                                                    placeholder="Ingrese el stock mínimo del producto" required>
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
                                                    value="{{ old('stock_maximo') }}" class="form-control"
                                                    id="stock_maximo" name="stock_maximo"
                                                    placeholder="Ingrese el stock máximo del producto" required>
                                            </div>
                                            @error('stock_maximo')
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
                                            <br><br><br>
                                            <img id="imgPreview" src="" width="100%"
                                                alt="Vista previa de la imagen">
                                            <script>
                                                function previewImage(event) {
                                                    const input = event.target;
                                                    const file = input.files[0];
                                                    if (file) {
                                                        const reader = new FileReader();
                                                        reader.onload = function(file) {
                                                            const imgPreview = document.getElementById('imgPreview');
                                                            imgPreview.src = file.target.result;
                                                            imgPreview.style.display = 'block';
                                                        };
                                                        reader.readAsDataURL(file);
                                                    }
                                                }
                                            </script>
                                            @error('imagen')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">


                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
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
            .ck.ck-toolbar {
                flex-wrap: wrap;
            }

            @media (max-width: 768px) {
                .ck-editor__editable {
                    min-height: 250px;
                    padding: 10px;
                }
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

    {{-- //SELECT2 --}}
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
        // Configuración del editor CKEditor
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

        // Validación de código en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const codigoInput = document.getElementById('codigo');
            const estadoIcon = document.getElementById('codigo-estado');
            const spinner = estadoIcon.querySelector('.fa-spinner');
            const checkIcon = estadoIcon.querySelector('.fa-check-circle');
            const timesIcon = estadoIcon.querySelector('.fa-times-circle');

            let timeoutId;

            // Función para verificar código
            function verificarCodigo() {
                const codigo = codigoInput.value.trim();

                if (codigo.length < 4) {
                    // Código muy corto
                    spinner.style.display = 'none';
                    checkIcon.style.display = 'none';
                    timesIcon.style.display = 'none';
                    estadoIcon.classList.remove('bg-success', 'bg-danger');
                    estadoIcon.classList.add('bg-info');
                    return;
                }

                // Mostrar spinner
                spinner.style.display = 'inline-block';
                checkIcon.style.display = 'none';
                timesIcon.style.display = 'none';
                estadoIcon.classList.remove('bg-success', 'bg-danger');
                estadoIcon.classList.add('bg-info');

                // Hacer petición AJAX
                fetch('{{ route('productos.verificar-codigo') }}?codigo=' + encodeURIComponent(codigo))
                    .then(response => response.json())
                    .then(data => {
                        spinner.style.display = 'none';

                        if (data.existe) {
                            // Código ya existe
                            checkIcon.style.display = 'none';
                            timesIcon.style.display = 'inline-block';
                            estadoIcon.classList.remove('bg-info', 'bg-success');
                            estadoIcon.classList.add('bg-danger');
                            codigoInput.setCustomValidity('Este código ya existe');
                        } else {
                            // Código disponible
                            checkIcon.style.display = 'inline-block';
                            timesIcon.style.display = 'none';
                            estadoIcon.classList.remove('bg-info', 'bg-danger');
                            estadoIcon.classList.add('bg-success');
                            codigoInput.setCustomValidity('');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        spinner.style.display = 'none';
                    });
            }

            // Evento input con debounce
            codigoInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(verificarCodigo, 500);
            });

            // Verificar al cargar la página (si ya hay valor)
            if (codigoInput.value) {
                verificarCodigo();
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
                // Normalizar: quitar espacios extras y convertir a mayúsculas para comparar
                const nombreCategoria = selectedOption.dataset.nombre.trim().toUpperCase();

                console.log('Categoría seleccionada:', nombreCategoria); // Para depuración

                // Buscar si la categoría contiene "PLOMERIA" (flexible)
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

        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño retraso para asegurar que el DOM esté listo
            setTimeout(() => {
                toggleNormaField();
            }, 100);

            const categoriaSelect = document.getElementById('categoria_id');
            if (categoriaSelect) {
                categoriaSelect.addEventListener('change', toggleNormaField);
            }
        });
    </script>
@stop
