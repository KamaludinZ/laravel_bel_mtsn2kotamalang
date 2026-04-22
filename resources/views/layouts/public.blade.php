<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appName ?? config('app.name') }}</title>

    <!-- Favicon -->
    @if($appLogo ?? false)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $appLogo) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $appLogo) }}">
    @endif

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen">
    {{ $slot }}
</body>
</html>
