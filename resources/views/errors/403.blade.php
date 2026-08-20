<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso restringido · Café Coocentral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-8 text-center">
            <span class="mx-auto flex items-center justify-center w-14 h-14 rounded-2xl bg-red-50 text-red-500 mb-4">
                <x-heroicon-o-lock-closed class="w-7 h-7" />
            </span>
            <h1 class="text-lg font-semibold text-gray-900 mb-1">Acceso restringido</h1>
            <p class="text-sm text-gray-500 mb-6">{{ $exception->getMessage() ?: 'No tienes permiso para ver esta sección.' }}</p>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center w-full rounded-xl bg-brand-600 text-white text-sm font-medium py-2.5 hover:bg-brand-700 transition-colors">
                Volver al inicio
            </a>
        </div>
    </div>
</body>
</html>
