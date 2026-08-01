<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Espace superviseur') — SmartEco Academy</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100 text-gray-900">
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    >
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
            <a href="{{ route('supervisor.dashboard') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                    SE
                </div>

                <div>
                    <p class="font-bold text-gray-900">SmartEco Academy</p>
                    <p class="text-xs text-gray-500">Espace superviseur</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
                <a
                    href="{{ route('supervisor.dashboard') }}"
                    class="{{ request()->routeIs('supervisor.dashboard')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A8.968 8.968 0 003 6.253v13A8.968 8.968 0 017.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5A8.968 8.968 0 0121 6.253v13A8.968 8.968 0 0016.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>

                    Tableau de bord
                </a>

                <a
                    href="{{ route('admin.centre.pack-enrollments.index') }}"
                    class="{{ request()->routeIs('admin.centre.pack-enrollments.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>

                    Inscriptions & paiements
                </a>

                <a
                    href="{{ route('admin.centre.reports.index') }}"
                    class="{{ request()->routeIs('admin.centre.reports.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"/>
                    </svg>

                    État financier (imprimable)
                </a>

                <a
                    href="{{ route('admin.registrations.index') }}"
                    class="{{ request()->routeIs('admin.registrations.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/>
                    </svg>

                    Dossiers d’admission
                </a>

                <a
                    href="{{ route('admin.trainings.enrollments.index') }}"
                    class="{{ request()->routeIs('admin.trainings.enrollments.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-9v6a6 6 0 0012 0v-6"/>
                    </svg>

                    Inscriptions & paiements formations
                </a>

                <a
                    href="{{ route('admin.cv.index') }}"
                    class="{{ request()->routeIs('admin.cv.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>

                    CV & Portfolios des étudiants
                </a>
            </div>
        </nav>

        <div class="border-t border-gray-200 p-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg p-3 transition hover:bg-gray-100">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
            </a>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6">
            <div class="flex items-center gap-4">
                <button type="button" class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Ouvrir le menu</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div>
                    <h1 class="font-semibold text-gray-900">@yield('page-title', 'Tableau de bord')</h1>
                    <p class="hidden text-xs text-gray-500 sm:block">Espace superviseur</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                    Profil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 sm:px-4">
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 p-4 sm:mx-6">
                <ul class="list-disc pl-5 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
