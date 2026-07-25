@extends('adminlte::page')

{{-- @section('title', 'Categorias') --}}

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}">Categorías</a></li>
            <li class="breadcrumb-item active" aria-current="page">Creación de Categorías</li>
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
                <form action=" {{ route('categorias.create') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre">Nombre de la categoría <b>(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-tags"></i></span>
                                    </div>
                                    <input type="text" value="{{ old('nombre') }}" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre de la categoría" required>
                                </div>
                                @error('nombre')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="descripcion">Descripción de la categoría (opcional)</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Ingrese la descripción de la categoría">{{ old('descripcion') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
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
