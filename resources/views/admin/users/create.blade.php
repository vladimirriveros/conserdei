@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Creación de Usuario</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus"></i> <b>Crear Nuevo Usuario</b></h3>
                    <div class="card-tools">
                        <span class="badge badge-warning">Campos obligatorios (*)</span>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.store') }}" method="POST">
                        @csrf

                        {{-- Fila 1: Nombre y Email --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre del usuario <b style="color: red">(*)</b></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        </div>
                                        <input type="text" value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name"
                                               placeholder="Ingrese el nombre completo" required>
                                    </div>
                                    @error('name')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <small class="form-text text-muted">Ej: Juan Pérez</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Correo electrónico <b style="color: red">(*)</b></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" value="{{ old('email') }}"
                                               class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email"
                                               placeholder="Ingrese el correo electrónico" required>
                                    </div>
                                    @error('email')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <small class="form-text text-muted">Ej: usuario@dominio.com</small>
                                </div>
                            </div>
                        </div>

                        {{-- Fila 2: Contraseña y Confirmación --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Contraseña <b style="color: red">(*)</b></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <input type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password"
                                               placeholder="Mínimo 8 caracteres" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                                    data-target="password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <small class="form-text text-muted">Mínimo 8 caracteres, incluir mayúsculas y números</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirmar contraseña <b style="color: red">(*)</b></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                        </div>
                                        <input type="password"
                                               class="form-control"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               placeholder="Confirme la contraseña" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                                    data-target="password_confirmation">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Debe coincidir con la contraseña anterior</small>
                                </div>
                            </div>
                        </div>

                        {{-- Fila 3: Rol (ocupa todo el ancho) --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="roles">Asignar Rol <b style="color: red">(*)</b></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                        </div>
                                        <select class="form-control @error('roles') is-invalid @enderror"
                                                id="roles" name="roles" required>
                                            <option value="">-- Seleccione un rol --</option>
                                            @foreach ($roles as $rol)
                                                <option value="{{ $rol->name }}"
                                                    {{ old('roles') == $rol->name ? 'selected' : '' }}>
                                                    <i class="fas fa-{{ $rol->name == 'admin' ? 'crown' : ($rol->name == 'super-admin' ? 'star' : 'user') }}"></i>
                                                    {{ ucfirst($rol->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('roles')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        El rol define los permisos y accesos del usuario en el sistema
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="float-right">
                                    <a href="{{ route('user.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Usuario
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tarjeta informativa lateral --}}
            <div class="card card-info">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-info-circle"></i> <b>Información importante</b></h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success"></i>
                            Todos los campos con <b style="color: red">(*)</b> son obligatorios
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-key text-warning"></i>
                            La contraseña debe tener mínimo 8 caracteres
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-user-tag text-primary"></i>
                            El rol asignado determinará los permisos del usuario
                        </li>
                        <li>
                            <i class="fas fa-shield-alt text-danger"></i>
                            Asigne roles con precaución, especialmente los de administrador
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Validación en tiempo real de coincidencia de contraseñas
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;

            if (confirm.length > 0 && password !== confirm) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (confirm.length > 0 && password === confirm) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-invalid', 'is-valid');
            }
        });

        // Mostrar/ocultar contraseña con tecla Enter
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
    </script>
@stop
