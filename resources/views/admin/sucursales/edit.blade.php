@extends('adminlte::page')

{{-- @section('title', 'Categorias') --}}

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}">Sucursales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar datos de la sucursal</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title"><b>Llene los datos del formulario </b></h3>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
              <div class="card-body" style="display: block;">
                <form action=" {{ route('sucursales.update', $sucursal->id) }}" method="POST">
                {{-- <form action=" {{ url('/admin/sucursales'. $sucursal->id) }}" method="POST"> --}}
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre">Nombre de la sucursal <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    </div>
                                    <input type="text" value="{{ old('nombre', $sucursal->nombre) }}" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre de la sucursal" required>
                                </div>
                                @error('nombre')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="direccion">Dirección <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    </div>
                                    <input type="text" value="{{ old('direccion', $sucursal->direccion) }}" class="form-control" id="direccion" name="direccion" placeholder="Ingrese la dirección de la sucursal" required>
                                </div>
                                @error('direccion')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="telefono">Teléfono <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="text" value="{{ old('telefono', $sucursal->telefono) }}" class="form-control" id="telefono" name="telefono" placeholder="Ingrese el teléfono de la sucursal" required>
                                </div>
                                @error('telefono')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="estado">Estado <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                    </div>
                                    <select class="form-control" id="activa" name="activa" required>
                                        <option value="" disabled selected>Seleccione el estado</option>
                                        <option value="1" {{ old('activa', $sucursal->activa) == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('activa', $sucursal->activa) == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </div>
                                @error('activa')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('sucursales.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">Actualizar</button>
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

</style>
@stop

@section('js')

@stop
