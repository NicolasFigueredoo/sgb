<!-- resources/views/layouts/default.blade.php -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Autopartes TB')</title>
    @vite(['resources/css/app.css', 'resources/js/app.tsx']) {{-- si usás Vite --}}
    @stack('head') {{-- Para inyectar scripts o estilos específicos --}}
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script src="//unpkg.com/alpinejs" defer></script>
    <meta name="description" content="@yield('description', 'Autopartes TB - Tu tienda de autopartes en línea') ">
    <meta name="keywords"
        content="@yield('keywords', 'autopartes, repuestos, accesorios, automóviles, tienda en línea')">

        

</head>

<body class="font-sans text-gray-900 antialiased">

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Contenido principal --}}

    @yield('content')


    {{-- Footer (opcional) --}}
    @includeIf('components.footer')

    {{-- resources/views/components/whatsapp.blade.php --}}
    @if(isset($contacto['wp']) && !empty($contacto['wp']))
        <a target="_blank" rel="noopener noreferrer"
            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contacto['wp']) }}" class="fixed right-0 bottom-0">
            <img src="{{ asset('images/wpIcon.png') }}" alt="WhatsApp" />
        </a>
    @endif

    @stack('scripts') {{-- Scripts específicos de cada vista --}}
</body>

</html>