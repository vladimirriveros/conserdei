@extends('adminlte::page')

@section('title', 'Nueva Compra')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nueva Compra</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Crear Nueva Compra</h3>
            </div>

            <form action="{{ route('compras.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    {{-- ALERTA DE PRODUCTOS SUGERIDOS ENVIADOS PARA REPOSICION--}}
                    @if($productos_sugeridos)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Alerta de stock bajo:</strong>
                            Se están reponiendo productos con stock bajo.
                            Después de crear la compra, podrá revisar y ajustar los productos sugeridos.
                        </div>
                    @endif
                    {{-- SELECCIONA PROVEEDOR Y FECHA DE COMPRA --}}
                    <div class="row">
                        {{-- proveedor_id --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="proveedor_id">Proveedor <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                    </div>
                                    <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}"
                                                {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                                {{ $proveedor->nombre }} - {{ $proveedor->empresa }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('proveedor_id')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        {{-- fecha --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha">Fecha de Compra <b style="color: red">(*)</b></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="date" name="fecha" id="fecha" class="form-control"
                                           value="{{ old('fecha', date('Y-m-d')) }}" required>
                                </div>
                                @error('fecha')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        {{-- estado (siempre pendiente al crear) --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                    </div>
                                    <input type="text" class="form-control" value="Pendiente" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- OBSERVACIONES --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea name="observaciones" id="observaciones"
                                          class="form-control" rows="3">{{ old('observaciones', $observacion_predefinida) }}</textarea>
                            </div>
                            @error('observaciones')
                                <small style="color: red">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Compra
                    </button>
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary float-right">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
