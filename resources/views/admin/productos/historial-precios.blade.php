@extends('adminlte::page')

@section('title', 'Historial de Precios')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('productos.show', $producto->id) }}">{{ $producto->nombre }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Historial de Precios</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <b>Historial de Precios: {{ $producto->nombre }} ({{ $producto->codigo }})</b>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Producto
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Pasar el ID del producto al componente Livewire --}}
                    <livewire:admin.productos.historial-precios :productoId="$producto->id" />
                </div>
            </div>
        </div>
    </div>
@stop
