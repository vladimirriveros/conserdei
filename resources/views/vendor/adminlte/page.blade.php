@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    @if (($mensaje = Session::get('mensaje')) && ($icono = Session::get('icono')))
        <script>
            Swal.fire({
                position: "top-center",
                icon: "{{ $icono }}",
                title: "{{ $mensaje }}",
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif






<script>
$(document).ready(function() {
    var sidebarContent = `
        <div class="p-3">
            <h5><i class="fas fa-cogs"></i> Configuración</h5>
            <hr class="mb-2">
            <h6>Cambiar tema</h6>
            <div class="form-group mb-3">
                <select id="theme-selector" class="form-control form-control-sm">
                    <option value="default">Por defecto</option>
                    <option value="primary">Azul (Primario)</option>
                    <option value="success">Verde</option>
                    <option value="danger">Rojo</option>
                    <option value="warning">Amarillo</option>
                    <option value="info">Celeste</option>
                    <option value="secondary">Gris</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="dark-mode">
                    <label class="custom-control-label" for="dark-mode">Modo oscuro</label>
                </div>
            </div>
            <hr class="mb-2">
            <h6>Sistema</h6>
            <small class="text-muted">
                <div><i class="fas fa-user mr-2"></i>{{ Auth::user()->name }}</div>
                <div><i class="fas fa-calendar mr-2"></i>{{ date('d/m/Y') }}</div>
                <div><i class="fas fa-clock mr-2"></i>{{ date('H:i') }}</div>
            </small>
        </div>
    `;

    setTimeout(function() {
        $('.control-sidebar').html(sidebarContent);
        $('[data-widget="control-sidebar"]').on('click', function() {
            setTimeout(function() {
                $('body').toggleClass('control-sidebar-slide-open');
            }, 100);
        });
    }, 100);

    $(document).on('change', '#theme-selector', function() {
        var theme = $(this).val();
        if (theme === 'default') {
            $('.main-sidebar').removeClass('sidebar-dark-primary sidebar-dark-success sidebar-dark-danger sidebar-dark-warning sidebar-dark-info sidebar-dark-secondary')
                .addClass('sidebar-dark-primary elevation-4');
            $('.main-header').removeClass('navbar-primary navbar-success navbar-danger navbar-warning navbar-info navbar-secondary')
                .addClass('navbar-white navbar-light');
            $('.info-box-icon').removeClass('bg-info bg-primary bg-success bg-danger bg-warning bg-secondary')
                .addClass('bg-info');
            localStorage.setItem('selected-theme', 'default');
        } else {
            $('.main-sidebar').removeClass('sidebar-dark-primary sidebar-dark-success sidebar-dark-danger sidebar-dark-warning sidebar-dark-info sidebar-dark-secondary')
                .addClass('sidebar-dark-' + theme + ' elevation-4');
            $('.main-header').removeClass('navbar-primary navbar-success navbar-danger navbar-warning navbar-info navbar-secondary navbar-white navbar-light')
                .addClass('navbar-' + theme + ' navbar-light');
            $('.info-box-icon').removeClass('bg-info bg-primary bg-success bg-danger bg-warning bg-secondary')
                .addClass('bg-' + theme);
            localStorage.setItem('selected-theme', theme);
        }
    });

    $(document).on('change', '#dark-mode', function() {
        if ($(this).is(':checked')) {
            $('body').addClass('dark-mode');
            localStorage.setItem('dark-mode', 'true');
        } else {
            $('body').removeClass('dark-mode');
            localStorage.setItem('dark-mode', 'false');
        }
    });

    setTimeout(function() {
        var savedTheme = localStorage.getItem('selected-theme');
        var darkMode = localStorage.getItem('dark-mode');
        if (savedTheme) {
            $('#theme-selector').val(savedTheme).trigger('change');
        } else {
            $('#theme-selector').val('default');
        }
        if (darkMode === 'true') {
            $('#dark-mode').prop('checked', true).trigger('change');
        }
    }, 100);
});
</script>

@stop
