@extends('layouts.student')

@section('title', 'Tableau de bord étudiant')

@section('page-title', 'Tableau de bord étudiant')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            Bonjour {{ auth()->user()->name }}
        </h2>

        <p class="mt-1 text-gray-600">
            Retrouvez vos formations, inscriptions et services.
        </p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Modules disponibles
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $activeModules->count() }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Inscriptions (packs)
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $packEnrollmentsCount }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Formations suivies
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $trainingEnrollmentsCount }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Notifications
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ auth()->user()->unreadNotifications()->count() }}
            </p>
        </div>
    </div>

    <section class="mt-8">
        <h3 class="text-lg font-bold text-gray-900">
            Modules actifs
        </h3>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($activeModules as $module)
                @php
                    $routeName = $moduleRoutes[$module->slug] ?? null;
                    $hasRoute = $routeName && \Illuminate\Support\Facades\Route::has($routeName);
                @endphp

                @if ($hasRoute)
                    <a href="{{ route($routeName) }}" class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="font-semibold text-indigo-700">
                            {{ $module->name }}
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ $module->description }}
                        </p>

                        <p class="mt-3 text-xs font-semibold text-indigo-600">Accéder →</p>
                    </a>
                @else
                    <article class="rounded-2xl bg-gray-50 p-5 opacity-75">
                        <h4 class="font-semibold text-gray-900">
                            {{ $module->name }}
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ $module->description }}
                        </p>

                        <p class="mt-3 text-xs font-semibold text-gray-400">Bientôt disponible</p>
                    </article>
                @endif
            @empty
                <p class="text-gray-500">
                    Aucun module actif.
                </p>
            @endforelse
        </div>
    </section>
@endsection