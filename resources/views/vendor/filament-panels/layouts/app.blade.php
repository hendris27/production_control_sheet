<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>{{ config('app.name') }}</title>
    {{-- Pastikan Tailwind CSS selalu ter-load di semua halaman Filament Panel --}}
    @vite(['resources/css/app.css'])
    @filamentStyles
    @livewireStyles
    @stack('head')
</head>

<body class="bg-gray-50">
    {{ $slot ?? '' }}
    @livewireScripts
    @filamentScripts
    @stack('scripts')
</body>

</html>
