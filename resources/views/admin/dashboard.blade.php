@extends('layouts.admin')

@section('title', 'Tableau de bord administrateur')

@section('page-title', 'Tableau de bord administrateur')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            Vue générale
        </h2>

        <p class="mt-1 text-gray-600">
            Statistiques et état général de la plateforme.
        </p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Utilisateurs
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $usersCount }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Modules actifs
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $activeModulesCount }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Rôles
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $rolesCount }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Activités enregistrées
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $activityLogsCount }}
            </p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="font-bold text-gray-900">
                Gestion rapide
            </h3>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <a
                    href="{{ route('admin.users.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    Utilisateurs & rôles
                </a>

                <a
                    href="{{ route('admin.centre.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    Niveaux, filières & cours
                </a>

                <a
                    href="{{ route('admin.centre.packs.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    Packs (semestres / modules)
                </a>

                <a
                    href="{{ route('admin.centre.library.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    Bibliothèque de ressources
                </a>

                <a
                    href="{{ route('admin.centre.pack-enrollments.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    Inscriptions & paiements
                </a>

                <a
                    href="{{ route('admin.registrations.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    Dossiers d’admission
                </a>

                <a
                    href="{{ route('admin.cv.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    CV & Portfolios des étudiants
                </a>

                <a
                    href="{{ route('admin.centre.reports.index') }}"
                    class="rounded-xl border border-gray-200 p-4 text-sm font-semibold hover:border-indigo-300 hover:bg-indigo-50"
                >
                    État financier
                </a>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="font-bold text-gray-900">
                État du système
            </h3>

            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        Application
                    </span>

                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        Active
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        Base de données
                    </span>

                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        Connectée
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        Mode maintenance
                    </span>

                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        Désactivé
                    </span>
                </div>
            </div>
        </section>
    </div>
@endsection