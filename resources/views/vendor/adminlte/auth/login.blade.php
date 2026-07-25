@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    {{-- Puedes agregar estilos adicionales aquí --}}
    <style>
        .login-page {
            background: linear-gradient(135deg, #ead866 0%, #4977e0 100%);
        }
        .login-box {
            max-width: 400px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #eae666 0%, #346288 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(6, 49, 238, 0.903);
            color: white;
        }
        .input-group-text {
            background: transparent;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
    </style>
@stop

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
    }
@endphp

@section('auth_header')
    <div class="text-center mb-4">
        {{-- <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Logo" style="max-width: 150px;"> --}}
        <h3 class="mt-3" style="color: #2843bb; font-weight: 600;">Bienvenido de nuevo</h3>
        <p class="text-muted">Inicia sesión para acceder al panel</p>
    </div>
@stop

@section('auth_body')
    <form action="{{ $loginUrl }}" method="post">
        @csrf

        {{-- Email field con ícono más moderno --}}
        <div class="input-group mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-envelope" style="color: #082eda;"></i>
                </span>
            </div>
            <input type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   placeholder="Correo electrónico"
                   style="border-left: none;"
                   autofocus>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password field con ícono más moderno --}}
        <div class="input-group mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-lock" style="color: #0c35eb;"></i>
                </span>
            </div>
            <input type="password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Contraseña"
                   style="border-left: none;">

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Opciones de login --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="icheck-primary">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="text-muted">
                    Recordarme
                </label>
            </div>

            @if($passResetUrl ?? config('adminlte.password_reset_url', 'password/reset'))
                <a href="{{ $passResetUrl ?? url(config('adminlte.password_reset_url', 'password/reset')) }}"
                   class="text-decoration-none"
                   style="color: #0d37f1;">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        {{-- Botón de login personalizado --}}
        <button type="submit" class="btn btn-login btn-block mb-3">
            <i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión
        </button>

        {{-- Mensaje de ayuda --}}
        <p class="text-center text-muted mb-0" style="font-size: 14px;">
            ¿Problemas para acceder? Contacta al administrador
        </p>
    </form>
@stop

{{-- Eliminamos completamente el footer con los enlaces de registro --}}
@section('auth_footer')
    {{-- Vacío a propósito para eliminar registro y recuperación --}}
@stop
