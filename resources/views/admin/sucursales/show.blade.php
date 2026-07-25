@extends('adminlte::page')

{{-- @section('title', 'Categorias') --}}

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}">Sucursales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Datos de la sucursal</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title"><b>Datos registrados</b></h3>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
              <div class="card-body" style="display: block;">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre">Nombre de la sucursal</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    </div>
                                    <input type="text" value="{{ $sucursal->nombre }}" class="form-control" id="nombre" name="nombre" readonly>
                                </div>
                                @error('nombre')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    </div>
                                    <input type="text" value="{{ $sucursal->direccion }}" class="form-control" id="direccion" name="direccion" readonly>
                                </div>
                                @error('direccion')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="text" value="{{ $sucursal->telefono }}" class="form-control" id="telefono" name="telefono" readonly>
                                </div>
                                @error('telefono')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                    </div>
                                    <select class="form-control" id="activa" name="activa" disabled>
                                        <option value="" disabled selected>Seleccione el estado</option>
                                        <option value="1" {{ $sucursal->activa == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ $sucursal->activa == '0' ? 'selected' : '' }}>Inactivo</option>
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
                            <a href="{{ route('sucursales.index') }}" class="btn btn-secondary">Volver a Listado de Sucursales</a>
                        </div>
                    </div>
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
