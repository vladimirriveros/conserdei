@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 18pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Usuario</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Usuario</li>
        </ol>
    </nav>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-edit"></i> <b>Editar Usuario</b></h3>
                    <div class="card-tools">
                        <span class="badge badge-warning">Campos obligatorios (*)</span>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- ALERTA PARA SUPER ADMIN --}}
                        @if ($user->is_protected)
                            <div class="alert alert-warning alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
                                Este es un <strong>Super Administrador</strong> y tiene permisos globales.
                                @if ($user->id === auth()->id())
                                    <br><span class="text-danger">⚠️ Estás editando tu propio usuario.</span>
                                @else
                                    <br><span class="text-warning">⚠️ Solo puedes ver la información, no editarla.</span>
                                @endif
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                {{-- NOMBRE DEL USUARIO --}}
                                <div class="form-group">
                                    <label for="name">Nombre del Usuario <b style="color: red">(*)</b></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        </div>
                                        <input type="text" value="{{ old('name', $user->name) }}"
                                            class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name"
                                            placeholder="Nombre completo"
                                            {{ ($user->is_protected && $user->id !== auth()->id()) ? 'disabled' : '' }}
                                            required>
                                    </div>
                                    @error('name')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- CORREO ELECTRÓNICO --}}
                                <div class="form-group">
                                    <label for="email">Correo Electrónico <b style="color: red">(*)</b></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" value="{{ old('email', $user->email) }}"
                                            class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email"
                                            placeholder="correo@dominio.com"
                                            {{ ($user->is_protected && $user->id !== auth()->id()) ? 'disabled' : '' }}
                                            required>
                                    </div>
                                    @error('email')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- ROLES --}}
                                <div class="form-group">
                                    <label for="roles">
                                        <i class="fas fa-user-tag"></i> Roles
                                        @if(!$user->is_protected && $user->id !== auth()->id())
                                            <b style="color: red">(*)</b>
                                        @endif
                                    </label>

                                    @if($user->is_protected)
                                        {{-- Super Admin - Solo lectura --}}
                                        <div class="alert alert-info">
                                            <i class="fas fa-shield-alt"></i>
                                            <strong>Super Administrador</strong> - Tiene todos los permisos por defecto.
                                        </div>
                                        <div class="mb-3">
                                            <strong>Roles actuales:</strong><br>
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-primary badge-lg">{{ $role->name }}</span>
                                            @endforeach
                                        </div>
                                        {{-- Mantener roles actuales --}}
                                        @foreach($user->roles as $role)
                                            <input type="hidden" name="roles[]" value="{{ $role->id }}">
                                        @endforeach
                                    @elseif($user->id === auth()->id())
                                        {{-- Usuario editando su propio perfil - NO puede cambiar roles --}}
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            No puedes cambiar tus propios roles. Contacta al administrador si necesitas cambiar tus permisos.
                                        </div>
                                        <div class="mb-3">
                                            <strong>Tus roles actuales:</strong><br>
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-primary badge-lg">{{ $role->name }}</span>
                                            @endforeach
                                        </div>
                                        {{-- Mantener roles actuales --}}
                                        @foreach($user->roles as $role)
                                            <input type="hidden" name="roles[]" value="{{ $role->id }}">
                                        @endforeach
                                    @else
                                        {{-- Administrador editando a otro usuario - Puede cambiar roles --}}
                                        <div class="mb-3">
                                            @foreach($roles as $role)
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                           class="form-check-input @error('roles') is-invalid @enderror"
                                                           id="role{{ $role->id }}"
                                                           name="roles[]"
                                                           value="{{ $role->id }}"
                                                           {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="role{{ $role->id }}">
                                                        {{ $role->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('roles')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Selecciona los roles que tendrá este usuario
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- MENSAJE INFORMATIVO PARA ADMINISTRADORES --}}
                        @if($user->id !== auth()->id() && !$user->is_protected)
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle"></i>
                                Estás editando a <strong>{{ $user->name }}</strong>.
                                Solo puedes modificar su <strong>nombre</strong>, <strong>email</strong> y <strong>roles</strong>.
                            </div>
                        @endif

                        {{-- CAMBIO DE CONTRASEÑA - Solo para el propio usuario --}}
                        @if($user->id === auth()->id())
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="card card-outline card-warning">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <i class="fas fa-key"></i> <b>Cambiar Contraseña</b>
                                            </h5>
                                            <div class="card-tools">
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                Solo completa estos campos si deseas cambiar tu contraseña.
                                            </div>

                                            {{-- Contraseña Actual --}}
                                            <div class="form-group">
                                                <label for="current_password">Contraseña Actual <b style="color: red">(*)</b></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                    </div>
                                                    <input type="password"
                                                        class="form-control @error('current_password') is-invalid @enderror"
                                                        id="current_password" name="current_password"
                                                        placeholder="Ingrese su contraseña actual" required>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                                            data-target="current_password">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                @error('current_password')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            {{-- Nueva Contraseña --}}
                                            <div class="form-group">
                                                <label for="password">Nueva Contraseña</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                    </div>
                                                    <input type="password"
                                                        class="form-control @error('password') is-invalid @enderror"
                                                        id="password" name="password"
                                                        placeholder="Nueva contraseña (opcional)">
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
                                                <small class="form-text text-muted">Mínimo 8 caracteres. Déjalo vacío si no deseas cambiarla.</small>
                                            </div>

                                            {{-- Confirmar Nueva Contraseña --}}
                                            <div class="form-group">
                                                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                                    </div>
                                                    <input type="password"
                                                        class="form-control"
                                                        id="password_confirmation" name="password_confirmation"
                                                        placeholder="Confirme la nueva contraseña">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                                            data-target="password_confirmation">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Debe coincidir con la nueva contraseña</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- BOTONES --}}
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('user.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                                <button type="submit" class="btn btn-success float-right"
                                    {{ ($user->is_protected && $user->id !== auth()->id()) ? 'disabled' : '' }}>
                                    <i class="fas fa-save"></i>
                                    @if ($user->is_protected && $user->id === auth()->id())
                                        Actualizar mi perfil
                                    @elseif($user->is_protected)
                                        Solo lectura
                                    @elseif($user->id === auth()->id())
                                        Actualizar mi perfil
                                    @else
                                        Actualizar Usuario
                                    @endif
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
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');

        if (password && confirm) {
            confirm.addEventListener('input', function() {
                if (this.value.length > 0 && password.value !== this.value) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (this.value.length > 0 && password.value === this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.remove('is-invalid', 'is-valid');
                }
            });
        }

        // Mostrar/ocultar con tecla Enter
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
