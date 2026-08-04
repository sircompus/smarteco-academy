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
        @yield('title', 'Espace étudiant') — SmartEco Academy
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
    class="min-h-screen bg-gray-100 text-gray-900"
>
    {{-- Arrière-plan mobile --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu latéral étudiant --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0 print:hidden"
    >
        {{-- Logo --}}
        <div class="flex flex-col items-center gap-2 border-b border-gray-200 px-6 py-5">
            <a
                href="{{ route('student.dashboard') }}"
                class="flex flex-col items-center gap-2 text-center"
            >
                <img
                    src="{{ asset('images/smarteco-logo.png') }}"
                    class="w-full h-auto object-contain"
                    alt="SmartEco Academy"
                >

                <div>
                    <p class="font-bold text-gray-900">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-gray-500">
                        Espace étudiant
                    </p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Principal
            </p>

            <div class="space-y-1">
                {{-- Tableau de bord --}}
                <a
                    href="{{ route('student.dashboard') }}"
                    class="{{ request()->routeIs('student.dashboard')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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

                    Tableau de bord
                </a>

                {{-- Cours du module Centre --}}
                <a
                    href="{{ route('student.courses.index') }}"
                    class="{{ request()->routeIs('student.courses.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A8.968 8.968 0 003 6.253v13A8.968 8.968 0 017.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5A8.968 8.968 0 0121 6.253v13A8.968 8.968 0 0016.5 18c-1.746 0-3.332.477-4.5 1.253"
                        />
                    </svg>

                    Mes cours
                </a>

                {{-- Packs (semestres / modules) --}}
                <a
                    href="{{ route('student.packs.index') }}"
                    class="{{ request()->routeIs('student.packs.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        />
                    </svg>

                    Packs (semestres / modules)
                </a>

                {{-- Bibliothèque de ressources --}}
                <a
                    href="{{ route('student.library.index') }}"
                    class="{{ request()->routeIs('student.library.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>

                    Bibliothèque de ressources
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Carrière
            </p>

            <div class="space-y-1">
                {{-- CV & Portfolio --}}
                <a
                    href="{{ route('student.cv.edit') }}"
                    class="{{ request()->routeIs('student.cv.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>

                    Mon CV & Portfolio
                </a>

                {{-- Veille d'emploi --}}
                <a
                    href="{{ route('student.job-watches.index') }}"
                    class="{{ request()->routeIs('student.job-watches.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M9 17v-2a4 4 0 014-4h4m0 0l-3-3m3 3l-3 3M5 5h6a2 2 0 012 2v2M5 5a2 2 0 00-2 2v10a2 2 0 002 2h6"
                        />
                    </svg>

                    Veille d'emploi
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Formation
            </p>

            <div class="space-y-1">
                {{-- Inscriptions --}}
                <a
    href="{{ route('student.registrations.index') }}"
    class="{{ request()->routeIs('student.registrations.*')
        ? 'bg-indigo-50 text-indigo-700'
        : 'text-gray-700 hover:bg-gray-100' }}
        flex items-center rounded-lg px-4 py-3 text-sm font-medium"
>
    Mes inscriptions
</a>

                {{-- Formations --}}
                <a
                    href="{{ route('student.trainings.index') }}"
                    class="{{ request()->routeIs('student.trainings.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"
                        />
                    </svg>

                    Mes formations
                </a>
            </div>
        </nav>

        {{-- Profils mis en avant (fondateur / équipe) --}}
        @php
            $featuredProfiles = \App\Models\CvProfile::where('is_public', true)
                ->where('show_in_navigation', true)
                ->get();
        @endphp

        @if ($featuredProfiles->isNotEmpty())
            <div class="border-t border-gray-200 px-4 py-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Notre équipe</p>

                <div class="flex flex-wrap gap-2">
                    @foreach ($featuredProfiles as $featured)
                        <a
                            href="{{ $featured->public_url }}"
                            target="_blank"
                            title="{{ $featured->full_name }} — CV, portfolio & biographie"
                            class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border-2 border-indigo-100 bg-indigo-50 font-semibold text-indigo-700 transition hover:border-indigo-400"
                        >
                            @if ($featured->photo_url)
                                <img src="{{ $featured->photo_url }}" class="h-full w-full object-cover" alt="{{ $featured->full_name }}">
                            @else
                                {{ strtoupper(substr($featured->full_name ?: '?', 0, 1)) }}
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Profil étudiant --}}
        <div class="border-t border-gray-200 p-4">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-lg p-3 transition hover:bg-gray-100"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700"
                >
                    @if (auth()->user()->profile?->avatar_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile->avatar_path) }}" class="h-full w-full rounded-full object-cover" alt="Avatar">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
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

    {{-- Zone principale --}}
    <div class="min-h-screen lg:pl-64 print:pl-0">
        {{-- Barre supérieure --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6 print:hidden"
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
                        @yield('page-title', 'Tableau de bord')
                    </h1>

                    <p class="hidden text-xs text-gray-500 sm:block">
                        Bienvenue sur votre espace personnel
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Notifications --}}
                <button
                    type="button"
                    class="relative rounded-lg p-2 text-gray-600 transition hover:bg-gray-100"
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
                            class="absolute right-0 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                        >
                            {{ auth()->user()->unreadNotifications()->count() }}
                        </span>
                    @endif
                </button>

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
                        class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 sm:px-4"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Messages --}}
        @if (session('success'))
            <div
                class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6"
            >
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 sm:mx-6"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Contenu --}}
        <main class="p-4 sm:p-6 print:p-0">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>