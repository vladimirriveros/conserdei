@extends('adminlte::page')

@section('title', 'Lotes Vencidos')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Inicio</a>
            </li>

            <li class="breadcrumb-item active">
                Productos Vencidos
            </li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')

    <div class="card card-outline card-danger">
        <div class="card-header bg-danger">
            <h3 class="card-title">
                <b>⚠ Productos Vencidos</b>
            </h3>
        </div>

        <div class="card-body">

            @if ($productos_vencidos->count())

                <table class="table table-bordered table-hover">
                    <thead class="bg-danger text-white">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Sucursal</th>
                            <th>Fecha vencimiento</th>
                            <th>Cantidad</th>
                            <th>Accion</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($productos_vencidos as $item)
                            {{-- {{ dd($item->lote) }} --}}
                            <tr class="table-danger">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->codigo_producto }}</td>
                                <td>{{ $item->producto }}</td>
                                <td>{{ $item->lote }}</td>
                                {{-- <td>{{ $item->lote_id }}</td> --}}

                                <td>
                                    <span class="badge badge-info">
                                        {{ $item->sucursal }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') }}
                                </td>
                                <td>{{ $item->cantidad }}</td>
                                <td>
                                    <a href="{{ route('lotes.vencidos.sucursal', $item->sucursal_id) }}"
                                        class="btn btn-sm btn-warning">

                                        <i class="fas fa-sign-out-alt"></i>
                                        Enviar a Salidas
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-dark">
                                        Vencido
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            @else
                <div class="alert alert-success">
                    ✅ No hay productos vencidos.
                </div>

            @endif

        </div>

        <div class="mt-3">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>

@stop
