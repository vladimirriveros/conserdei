@extends('adminlte::auth.login')

@section('auth_footer')
    <form method="POST" action="{{ route('login.guest') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-outline-primary btn-block">
            <i class="fas fa-eye mr-1"></i> Ingresar como invitado
        </button>
        <p class="text-muted text-center small mt-2 mb-0">
            Acceso de demostración en modo solo lectura.
        </p>
    </form>
@stop
