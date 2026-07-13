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
        @yield('title', 'Administration')
        — SmartEco Academy
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-slate-100"
>
    <div
        x-cloak
        x-show="sidebarOpen"
        class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu administrateur --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900 text-white transition-transform duration-300 lg:translate-x-0"
    >
        <div class="flex h-16 items-center border-b border-slate-800 px-6">
            <a
                href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 font-bold"
                >
                    SE
                </div>

                <div>
                    <p class="font-bold">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-slate-400">
                        Administration
                    </p>
                </div>
            </a>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Principal
            </p>

            <a
                href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard')
                    ? 'bg-indigo-600 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                    flex rounded-lg px-4 py-3 text-sm font-medium"
            >
                Statistiques
            </a>

            <a
                href="#"
                class="flex rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
            >
                Gestion des utilisateurs
            </a>

            <a
                href="#"
                class="flex rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
            >
                Gestion des rôles
            </a>

            <a
                href="#"
                class="flex rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
            >
                Gestion des modules
            </a>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Système
            </p>

            <a
                href="#"
                class="flex rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
            >
                Paramètres
            </a>

            <a
                href="#"
                class="flex rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white"
            >
                Journaux d’activité
            </a>
        </nav>

        <div class="border-t border-slate-800 p-4">
            <div class="rounded-xl bg-slate-800 p-4">
                <p class="text-sm font-semibold text-white">
                    {{ auth()->user()->name }}
                </p>

                <p class="mt-1 truncate text-xs text-slate-400">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-72">
        {{-- Barre supérieure --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-white px-4 shadow-sm sm:px-6"
        >
            <div class="flex items-center gap-4">
                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-gray-100 lg:hidden"
                    @click="sidebarOpen = true"
                >
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

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                >
                    Mon profil
                </a>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>