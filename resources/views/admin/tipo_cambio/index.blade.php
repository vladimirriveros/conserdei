@extends('adminlte::page')

@section('title', 'Tipos de Cambio')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tipo_cambio.index') }}">Tipos de cambio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    @livewire('tipo-cambio-manager')
@stop
