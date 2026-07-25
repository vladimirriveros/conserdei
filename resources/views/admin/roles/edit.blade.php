@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Rol</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><b>Editar Rol</b></h3>
                </div>
                <div class="card-body">
                    @if($rol->name === 'admin')
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i>
                            <strong>¡Atención!</strong> Este es el rol "admin" y está protegido.
                            Solo puedes modificar su nombre si eres super administrador.
                        </div>
                    @endif

                    <form action="{{ route('roles.update', $rol->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nombre del rol <b style="color: red">(*)</b></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                </div>
                                <input type="text"
                                       value="{{ old('name', $rol->name) }}"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       required
                                       {{ $rol->name === 'admin' ? 'disabled' : '' }}>
                            </div>
                            @error('name')
                                <small style="color: red">{{ $message }}</small>
                            @enderror

                            @if($rol->name === 'admin')
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    El nombre del rol "admin" no puede ser modificado por seguridad.
                                </small>
                                <input type="hidden" name="name" value="admin">
                            @endif
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
