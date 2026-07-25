@extends('adminlte::page')

@section('content_header')
    <h1><i class="fas fa-key"></i> Cambiar Contraseña</h1>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                </div>
                <div class="card-body">
                    @if (session('mensaje'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="icon fas fa-check"></i> {{ session('mensaje') }}
                        </div>
                    @endif

                    <form action="{{ route('password.change.update') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="current_password">Contraseña Actual <b class="text-danger">(*)</b></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" placeholder="Ingrese su contraseña actual"
                                    required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('current_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="new_password">Nueva Contraseña <b class="text-danger">(*)</b></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                    id="new_password" name="new_password"
                                    placeholder="Ingrese nueva contraseña (mínimo 8 caracteres)" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('new_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            {{-- Indicador de requisitos --}}
                            <div class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%;">
                                    </div>
                                </div>
                                <small id="password-requirements" class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Requisitos: 8+ caracteres, mayúscula, minúscula, número, carácter especial (@$!%*?&#)
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirmar Nueva Contraseña <b
                                    class="text-danger">(*)</b></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                </div>
                                <input type="password" class="form-control" id="new_password_confirmation"
                                    name="new_password_confirmation" placeholder="Confirme la nueva contraseña" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="new_password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small id="confirm-message" class="form-text"></small>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('home') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary float-right">
                                    <i class="fas fa-save"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </div>
                    </form>
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
            if (input) {
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
            }
        });
    });

    // Validación en tiempo real de coincidencia de contraseñas
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    const confirmMessage = document.getElementById('confirm-message');
    const strengthBar = document.getElementById('password-strength');

    if (newPassword && confirmPassword) {
        // Confirmación de contraseña
        confirmPassword.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (newPassword.value === this.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    confirmMessage.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Las contraseñas coinciden</span>';
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                    confirmMessage.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Las contraseñas no coinciden</span>';
                }
            } else {
                this.classList.remove('is-invalid', 'is-valid');
                confirmMessage.innerHTML = '';
            }
        });

        // Medidor de fuerza de contraseña y ocultar error
        newPassword.addEventListener('input', function() {
            const password = this.value;
            const errorContainer = this.closest('.form-group').querySelector('.text-danger');

            // Verificar si cumple todos los requisitos
            const isValid = password.length >= 8 &&
                           /[A-Z]/.test(password) &&
                           /[a-z]/.test(password) &&
                           /[0-9]/.test(password) &&
                           /[@$!%*?&#]/.test(password);

            // Ocultar o mostrar el mensaje de error
            if (errorContainer) {
                if (isValid || password.length === 0) {
                    errorContainer.style.display = 'none';
                } else {
                    errorContainer.style.display = 'block';
                }
            }

            // Calcular y actualizar barra de fuerza
            let score = 0;
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[@$!%*?&#]/.test(password)) score++;

            const percent = (score / 5) * 100;
            strengthBar.style.width = percent + '%';

            if (score <= 2) {
                strengthBar.className = 'progress-bar bg-danger';
            } else if (score <= 3) {
                strengthBar.className = 'progress-bar bg-warning';
            } else if (score <= 4) {
                strengthBar.className = 'progress-bar bg-info';
            } else {
                strengthBar.className = 'progress-bar bg-success';
            }
        });
    }
</script>
@stop
