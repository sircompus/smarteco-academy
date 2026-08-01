<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SmartEco Academy') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 py-10">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/smarteco-logo.png') }}" class="h-12 w-12 rounded-xl object-contain" alt="SmartEco Academy">

                <div>
                    <p class="text-lg font-bold text-gray-900">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-gray-500">
                        Apprendre, créer et progresser
                    </p>
                </div>
            </a>

            <div class="mt-8 w-full sm:max-w-md overflow-hidden rounded-2xl bg-white px-6 py-8 shadow-sm">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
