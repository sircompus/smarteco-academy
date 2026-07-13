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
        @yield('title', 'Administration') — SmartEco Academy
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-slate-100 text-slate-900"
>
    {{-- Arrière-plan mobile --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu latéral administrateur --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900 text-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center border-b border-slate-800 px-6">
            <a
                href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 font-bold text-white"
                >
                    SE
                </div>

                <div>
                    <p class="font-bold text-white">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-slate-400">
                        Administration
                    </p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Vue générale
            </p>

            <div class="space-y-1">
                {{-- Tableau de bord --}}
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-12h8V3h-8v6z"
                        />
                    </svg>

                    Statistiques
                </a>

                {{-- Module Centre --}}
                <a
                    href="{{ route('admin.centre.index') }}"
                    class="{{ request()->routeIs('admin.centre.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118 14.5c0 2.21-2.686 4-6 4s-6-1.79-6-4c0-1.381.484-2.651 1.29-3.672L12 14z"
                        />
                    </svg>

                    Gestion du centre
                </a>

                {{-- Module Inscriptions --}}
                <a
                    href="{{ route('admin.registrations.index') }}"
                    class="{{ request()->routeIs('admin.registrations.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6M9 8h6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"
                        />
                    </svg>

                    Gestion des inscriptions
                </a>

                {{-- Module Formations --}}
                <a
                    href="{{ route('admin.trainings.index') }}"
                    class="{{ request()->routeIs('admin.trainings.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-9v6a6 6 0 0012 0v-6"
                        />
                    </svg>

                    Gestion des formations
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Gestion
            </p>

            <div class="space-y-1">
                {{-- Utilisateurs --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m6-4a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>

                    Utilisateurs
                </a>

                {{-- Rôles --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 11c0-1.657 1.343-3 3-3s3 1.343 3 3-1.343 3-3 3-3-1.343-3-3zm-8 9v-1a5 5 0 015-5h1"
                        />
                    </svg>

                    Rôles et permissions
                </a>

                {{-- Modules --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h6v6H4V6zm10 0h6v6h-6V6zM4 16h6v4H4v-4zm10 0h6v4h-6v-4z"
                        />
                    </svg>

                    Gestion des modules
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Système
            </p>

            <div class="space-y-1">
                {{-- Paramètres --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15.5A3.5 3.5 0 1012 8a3.5 3.5 0 000 7.5z"
                        />
                    </svg>

                    Paramètres
                </a>

                {{-- Journaux --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 17v-6m4 6V7m4 10v-3M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"
                        />
                    </svg>

                    Journaux d’activité
                </a>
            </div>
        </nav>

        {{-- Profil administrateur --}}
        <div class="border-t border-slate-800 p-4">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-xl bg-slate-800 p-3 transition hover:bg-slate-700"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500 font-semibold text-white"
                >
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-slate-400">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Zone principale --}}
    <div class="min-h-screen lg:pl-72">
        {{-- Barre supérieure --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6"
        >
            <div class="flex items-center gap-4">
                {{-- Bouton mobile --}}
                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 lg:hidden"
                    @click="sidebarOpen = true"
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

                <div>
                    <h1 class="font-semibold text-gray-900">
                        @yield('page-title', 'Administration')
                    </h1>

                    <p class="hidden text-xs text-gray-500 sm:block">
                        Gestion générale de SmartEco Academy
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Accueil public --}}
                <a
                    href="{{ route('home') }}"
                    class="hidden rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 sm:block"
                >
                    Voir le site
                </a>

                {{-- Profil --}}
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Profil
                </a>

                {{-- Déconnexion --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700 sm:px-4"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Messages de succès --}}
        @if (session('success'))
            <div
                class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6"
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- Messages d’erreur --}}
        @if (session('error'))
            <div
                class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 sm:mx-6"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Contenu --}}
        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>