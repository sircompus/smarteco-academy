<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', config('app.name', 'SmartEco Academy'))
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">

    {{-- En-tête --}}
    <header
        x-data="{ mobileMenuOpen: false }"
        class="border-b border-gray-200 bg-white"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                {{-- Logo --}}
                <a
                    href="{{ route('home') }}"
                    class="flex items-center gap-3"
                >
                    <img
                        src="{{ asset('images/smarteco-logo.png') }}"
                        class="h-10 w-10 rounded-xl object-contain"
                        alt="SmartEco Academy"
                    >

                    <div>
                        <p class="font-bold text-gray-900">
                            SmartEco Academy
                        </p>

                        <p class="text-xs text-gray-500">
                            Apprendre, créer et progresser
                        </p>
                    </div>
                </a>

                {{-- Navigation desktop --}}
                <nav class="hidden items-center gap-8 md:flex">
                    <a
                        href="{{ route('home') }}"
                        class="text-sm font-medium text-gray-700 hover:text-indigo-600"
                    >
                        Accueil
                    </a>

                    <a
                        href="#formations"
                        class="text-sm font-medium text-gray-700 hover:text-indigo-600"
                    >
                        Formations
                    </a>

                    <a
                        href="#services"
                        class="text-sm font-medium text-gray-700 hover:text-indigo-600"
                    >
                        Services
                    </a>

                    <a
                        href="#contact"
                        class="text-sm font-medium text-gray-700 hover:text-indigo-600"
                    >
                        Contact
                    </a>
                </nav>

                {{-- Authentification --}}
                <div class="hidden items-center gap-3 md:flex">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Tableau de bord
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="text-sm font-semibold text-gray-700 hover:text-indigo-600"
                        >
                            Connexion
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Créer un compte
                        </a>
                    @endauth
                </div>

                {{-- Bouton mobile --}}
                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 md:hidden"
                    @click="mobileMenuOpen = ! mobileMenuOpen"
                >
                    <span class="sr-only">
                        Ouvrir le menu
                    </span>

                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            </div>

            {{-- Navigation mobile --}}
            <div
                x-cloak
                x-show="mobileMenuOpen"
                class="border-t border-gray-100 py-4 md:hidden"
            >
                <nav class="flex flex-col gap-2">
                    <a
                        href="{{ route('home') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium hover:bg-gray-100"
                    >
                        Accueil
                    </a>

                    <a
                        href="#formations"
                        class="rounded-lg px-3 py-2 text-sm font-medium hover:bg-gray-100"
                    >
                        Formations
                    </a>

                    <a
                        href="#services"
                        class="rounded-lg px-3 py-2 text-sm font-medium hover:bg-gray-100"
                    >
                        Services
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white"
                        >
                            Tableau de bord
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="rounded-lg px-3 py-2 text-sm font-medium hover:bg-gray-100"
                        >
                            Connexion
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white"
                        >
                            Créer un compte
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    {{-- Contenu --}}
    <main>
        @yield('content')
    </main>

    {{-- Pied de page --}}
    <footer
        id="contact"
        class="mt-16 bg-gray-900 text-gray-300"
    >
        <div
            class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8"
        >
            <div>
                <h3 class="font-bold text-white">
                    SmartEco Academy
                </h3>

                <p class="mt-3 text-sm leading-6 text-gray-400">
                    Une plateforme consacrée à l’apprentissage,
                    aux formations et aux services académiques.
                </p>
            </div>

            <div>
                <h3 class="font-bold text-white">
                    Navigation
                </h3>

                <div class="mt-3 flex flex-col gap-2 text-sm">
                    <a href="{{ route('home') }}" class="hover:text-white">
                        Accueil
                    </a>

                    <a href="#formations" class="hover:text-white">
                        Formations
                    </a>

                    <a href="#services" class="hover:text-white">
                        Services
                    </a>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-white">
                    Contact
                </h3>

                <p class="mt-3 text-sm text-gray-400">
                    contact@smarteco-academy.test
                </p>
            </div>
        </div>

        <div class="border-t border-gray-800 py-5 text-center text-sm text-gray-500">
            © {{ date('Y') }} SmartEco Academy. Tous droits réservés.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>