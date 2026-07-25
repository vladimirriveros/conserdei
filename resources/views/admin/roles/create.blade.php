@extends('adminlte::page')

{{-- @section('title', 'Categorias') --}}

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Creación de Roles</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title"><b>Llene los datos del formulario </b></h3>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
              <div class="card-body" style="display: block;">
                {{-- ------------------------------FORMULARIO---------------------------------------- --}}
                {{-- -------------------------------------------------------------------------------- --}}
                <form action=" {{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre">Nombre del rol <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user-check"></i></span>
                                    </div>
                                    <input type="text" value="{{ old('name') }}" class="form-control" id="name" name="name" placeholder="Ingrese el nombre de la sucursal" required>
                                </div>
                                @error('name')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="col-md-12">
                            <div class="form-group">
                                <label for="estado">Estado <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                    </div>
                                    <select class="form-control" id="activa" name="activa" required>
                                        <option value="" disabled selected>Seleccione el estado</option>
                                        <option value="1" {{ old('activa') == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('activa') == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </div>
                                @error('activa')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div> --}}
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
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

</style>
@stop

@section('js')

@stop
