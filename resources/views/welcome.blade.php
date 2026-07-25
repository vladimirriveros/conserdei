<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Estilos -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body { background-color: #FDFDFC; color: #1b1b18; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
            nav { text-align: center; }
            a { padding: 10px; text-decoration: none; color: #1b1b18; border: 1px solid transparent; border-radius: 5px; }
            a:hover { border-color: #19140035; }
        </style>
    @endif
</head>
<body>
    <nav>
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Log In</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        @endif
    </nav>
</body>
</html>
