$strayFiles = @(
    "C:\laragon\www\SEA\resources\views\layouts\admin-menu-reorganized.blade.php",
    "C:\laragon\www\SEA\resources\views\layouts\admin-print-fix.blade.php"
)
foreach ($f in $strayFiles) {
    if (Test-Path $f) { Remove-Item $f -Force; Write-Host "SUPPRIME: $f" -ForegroundColor Yellow }
}

$path0 = "C:\laragon\www\SEA\app\Http\Controllers\ProfileController.php"
$content0 = @'
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'profile' => $request->user()->profile,
        ]);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:4096'],
        ]);

        $profile = $request->user()->profile()->firstOrCreate([]);

        $profile->update([
            'avatar_path' => $request->file('avatar')->store('avatars', 'public'),
        ]);

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/ProfileController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/ProfileController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\resources\views\layouts\admin.blade.php"
$content1 = @'
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
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900 text-white shadow-xl transition-transform duration-300 lg:translate-x-0 print:hidden"
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
                    href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('supervisor.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard', 'supervisor.dashboard')
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
            </div>

            @if (auth()->user()->hasRole('admin'))
                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Centre de formation
                </p>

                <div class="space-y-1">
                    {{-- Module Centre --}}
                    <a
                        href="{{ route('admin.centre.index') }}"
                        class="{{ request()->routeIs('admin.centre.index')
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

                        Niveaux, filières & cours
                    </a>

                    {{-- Générateur de cursus --}}
                    <a
                        href="{{ route('admin.centre.curriculum.index') }}"
                        class="{{ request()->routeIs('admin.centre.curriculum.*')
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>

                        Générateur de cursus
                    </a>

                    {{-- Packs (semestres / modules) --}}
                    <a
                        href="{{ route('admin.centre.packs.index') }}"
                        class="{{ request()->routeIs('admin.centre.packs.*')
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
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                            />
                        </svg>

                        Packs (semestres / modules)
                    </a>

                    {{-- Bibliothèque de ressources --}}
                    <a
                        href="{{ route('admin.centre.library.index') }}"
                        class="{{ request()->routeIs('admin.centre.library.*')
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
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>

                        Bibliothèque de ressources
                    </a>
                </div>
            @endif

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Inscriptions & validations
            </p>

            <div class="space-y-1">
                {{-- Inscriptions & paiements --}}
                <a
                    href="{{ route('admin.centre.pack-enrollments.index') }}"
                    class="{{ request()->routeIs('admin.centre.pack-enrollments.*')
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
                            d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>

                    Inscriptions & paiements aux packs
                </a>

                <a
                    href="{{ route('admin.cv.index') }}"
                    class="{{ request()->routeIs('admin.cv.*')
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
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>

                    CV & Portfolios des étudiants
                </a>

                <a
                    href="{{ route('admin.cv.skills.index') }}"
                    class="{{ request()->routeIs('admin.cv.skills.*')
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
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    Catalogue de compétences (CV)
                </a>

                <a
                    href="{{ route('admin.centre.reports.index') }}"
                    class="{{ request()->routeIs('admin.centre.reports.*')
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
                            d="M9 17v-6m4 6V7m4 10v-3M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"
                        />
                    </svg>

                    État financier (imprimable)
                </a>

                {{-- Module Inscriptions (dossiers d'admission) --}}
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

                    Dossiers d’admission
                </a>

                {{-- Inscriptions & paiements formations --}}
                <a
                    href="{{ route('admin.trainings.enrollments.index') }}"
                    class="{{ request()->routeIs('admin.trainings.enrollments.*')
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
                            d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>

                    Inscriptions & paiements formations
                </a>
            </div>

            @if (auth()->user()->hasRole('admin'))
                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Autres modules
                </p>

                <div class="space-y-1">
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

                        Formations courtes
                    </a>
                </div>

                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Comptes
                </p>

                <div class="space-y-1">
                    {{-- Comptes Prof / Superviseur / Admin --}}
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*')
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
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"
                            />
                        </svg>

                        Utilisateurs & rôles
                    </a>
                </div>
            @endif
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
                    @if (auth()->user()->profile?->avatar_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile->avatar_path) }}" class="h-full w-full rounded-full object-cover" alt="Avatar">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
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
    <div class="min-h-screen lg:pl-72 print:pl-0">
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
        <main class="p-4 sm:p-6 print:p-0">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/admin.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/admin.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\resources\views\layouts\professor.blade.php"
$content2 = @'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Espace professeur') — SmartEco Academy</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

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
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0 print:hidden"
    >
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
            <a href="{{ route('professor.dashboard') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                    SE
                </div>

                <div>
                    <p class="font-bold text-gray-900">SmartEco Academy</p>
                    <p class="text-xs text-gray-500">Espace professeur</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
                <a
                    href="{{ route('professor.dashboard') }}"
                    class="{{ request()->routeIs('professor.dashboard')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A8.968 8.968 0 003 6.253v13A8.968 8.968 0 017.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5A8.968 8.968 0 0121 6.253v13A8.968 8.968 0 0016.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>

                    Mes cours
                </a>
            </div>
        </nav>

        <div class="border-t border-gray-200 p-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg p-3 transition hover:bg-gray-100">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 font-semibold text-indigo-700">
                    @if (auth()->user()->profile?->avatar_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile->avatar_path) }}" class="h-full w-full rounded-full object-cover" alt="Avatar">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
            </a>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-64 print:pl-0">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6 print:hidden">
            <div class="flex items-center gap-4">
                <button type="button" class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Ouvrir le menu</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div>
                    <h1 class="font-semibold text-gray-900">@yield('page-title', 'Tableau de bord')</h1>
                    <p class="hidden text-xs text-gray-500 sm:block">Espace professeur</p>
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

        <main class="p-4 sm:p-6 print:p-0">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/professor.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/professor.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\resources\views\layouts\student.blade.php"
$content3 = @'
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
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
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

                {{-- Examens --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
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
                            d="M9 5h6m-6 4h6m-6 4h4m-8-9h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"
                        />
                    </svg>

                    Mes examens
                </a>

                {{-- Projets --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
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
                            d="M3 7h18M5 7v12h14V7M9 11h6"
                        />
                    </svg>

                    Mes projets
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Services
            </p>

            <div class="space-y-1">
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    CV ATS
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Portfolio
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Community
                </a>
            </div>
        </nav>

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
'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/student.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/student.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\resources\views\layouts\supervisor.blade.php"
$content4 = @'
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
                    @if (auth()->user()->profile?->avatar_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile->avatar_path) }}" class="h-full w-full rounded-full object-cover" alt="Avatar">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
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

'@
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/supervisor.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/supervisor.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\resources\views\profile\edit.blade.php"
$content5 = @'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-avatar-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

'@
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/profile/edit.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/profile/edit.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\profile\partials\update-avatar-form.blade.php"
$content6 = @'
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Photo de profil
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Cette photo apparaît dans le menu, en haut de ton espace.
        </p>
    </header>

    @if (session('status') === 'avatar-updated')
        <p class="mt-2 text-sm text-green-600">Photo mise à jour.</p>
    @endif

    <div class="mt-4 flex items-center gap-6">
        @if ($profile?->avatar_path)
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($profile->avatar_path) }}"
                class="h-20 w-20 rounded-full object-cover"
                alt="Photo de profil"
            >
        @else
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-xl font-bold text-indigo-700">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="flex items-center gap-3">
            @csrf
            @method('PATCH')

            <input type="file" name="avatar" accept="image/*" required class="text-sm">

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                Mettre à jour
            </button>
        </form>
    </div>

    @error('avatar')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>

'@
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/profile/partials/update-avatar-form.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/profile/partials/update-avatar-form.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\routes\web.php"
$content7 = @'
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        if ($request->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('student.dashboard');
    })->name('dashboard');

    Route::get('/student/dashboard', function () {
        $activeModules = DB::table('modules')
            ->where('is_active', true)
            ->orderBy('menu_order')
            ->get();

        return view('student.dashboard', [
            'activeModules' => $activeModules,
        ]);
    })->name('student.dashboard');

    Route::get('/admin/dashboard', function (Request $request) {
        abort_unless(
            $request->user()->hasRole('admin'),
            403,
            'Accès réservé aux administrateurs.'
        );

        return view('admin.dashboard', [
            'usersCount' => DB::table('users')->count(),
            'activeModulesCount' => DB::table('modules')
                ->where('is_active', true)
                ->count(),
            'rolesCount' => DB::table('roles')->count(),
            'activityLogsCount' => DB::table('activity_logs')->count(),
        ]);
    })->name('admin.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])
        ->name('profile.avatar.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/centre.php';
require __DIR__.'/registration.php';
require __DIR__.'/training.php';
require __DIR__.'/cv.php';
'@
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/web.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/web.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
