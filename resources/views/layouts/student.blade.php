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
        @yield('title', 'Espace étudiant')
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
    class="min-h-screen bg-gray-100"
>
    {{-- Superposition mobile --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu latéral --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    >
        <div class="flex h-16 items-center border-b px-6">
            <a
                href="{{ route('student.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white"
                >
                    SE
                </div>

                <div>
                    <p class="font-bold text-gray-900">
                        SmartEco
                    </p>

                    <p class="text-xs text-gray-500">
                        Espace étudiant
                    </p>
                </div>
            </a>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
            <a
                href="{{ route('student.dashboard') }}"
                class="{{ request()->routeIs('student.dashboard')
                    ? 'bg-indigo-50 text-indigo-700'
                    : 'text-gray-700 hover:bg-gray-100' }}
                    flex items-center rounded-lg px-4 py-3 text-sm font-medium"
            >
                Tableau de bord
            </a>

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                Mes inscriptions
            </a>

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                Mes formations
            </a>

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                Mes examens
            </a>

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                Mes projets
            </a>

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                CV ATS
            </a>

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100"
            >
                Portfolio
            </a>
        </nav>

        <div class="border-t p-4">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-lg p-3 hover:bg-gray-100"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700"
                >
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-gray-500">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Contenu principal --}}
    <div class="min-h-screen lg:pl-64">

        {{-- Barre supérieure --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-white px-4 shadow-sm sm:px-6"
        >
            <div class="flex items-center gap-4">
                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden"
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
                        @yield('page-title', 'Tableau de bord')
                    </h1>

                    <p class="hidden text-xs text-gray-500 sm:block">
                        Bienvenue sur votre espace personnel
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Notifications --}}
                <button
                    type="button"
                    class="relative rounded-lg p-2 text-gray-600 hover:bg-gray-100"
                    title="Notifications"
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
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>

                    @if (auth()->user()->unreadNotifications()->count() > 0)
                        <span
                            class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                        >
                            {{ auth()->user()->unreadNotifications()->count() }}
                        </span>
                    @endif
                </button>

                {{-- Profil --}}
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
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
                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Messages --}}
        @if (session('success'))
            <div class="mx-4 mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6">
                {{ session('success') }}
            </div>
        @endif

        {{-- Contenu de la page --}}
        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>