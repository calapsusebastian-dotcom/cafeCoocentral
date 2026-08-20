@props(['title' => 'Iniciar sesión'])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Café Coocentral</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
