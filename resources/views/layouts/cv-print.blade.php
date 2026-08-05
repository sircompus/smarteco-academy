<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>@yield('title', 'Curriculum vitae')</title>

    @vite([
        'resources/css/app.css',
        'resources/css/cv-print.css',
        'resources/js/app.js',
    ])

    <style>
        @page {
            size: A4;
            margin: @yield('page-margin', '14mm');
        }
    </style>

    @stack('styles')
</head>

<body class="cv-print-body @yield('cv-body-class')">
    <div
        class="cv-toolbar"
        data-cv-toolbar
        role="toolbar"
        aria-label="Actions du CV"
    >
        <button
            type="button"
            class="cv-toolbar__primary"
            onclick="window.print()"
        >
            Imprimer / Enregistrer en PDF
        </button>

        <button
            type="button"
            class="cv-toolbar__secondary"
            onclick="window.close()"
        >
            Fermer
        </button>
    </div>

    <main
        class="cv-print-shell"
        data-cv-layout="print"
    >
        @yield('content')
    </main>
</body>
</html>
