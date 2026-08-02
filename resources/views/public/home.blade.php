@extends('layouts.public')

@section('title', 'Accueil — SmartEco Academy')

@section('content')
    <section class="bg-white">
        <div
            class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8"
        >
            <div>
                <p class="font-semibold text-indigo-600">
                    Plateforme académique et professionnelle
                </p>

                <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                    Développez vos compétences avec SmartEco Academy
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600">
                    Formations, préparation aux examens, projets,
                    services académiques, CV ATS et portfolio professionnel.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    @guest
                        <a
                            href="{{ route('register') }}"
                            class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700"
                        >
                            Créer un compte
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="rounded-xl border border-gray-300 px-6 py-3 font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Se connecter
                        </a>
                    @else
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700"
                        >
                            Accéder au tableau de bord
                        </a>
                    @endguest
                </div>
            </div>

            <div class="rounded-3xl bg-indigo-50 p-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-3xl font-bold text-indigo-600">
                            01
                        </p>

                        <h2 class="mt-3 font-semibold">
                            Formations
                        </h2>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-3xl font-bold text-indigo-600">
                            02
                        </p>

                        <h2 class="mt-3 font-semibold">
                            Examens
                        </h2>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-3xl font-bold text-indigo-600">
                            03
                        </p>

                        <h2 class="mt-3 font-semibold">
                            Projets
                        </h2>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-3xl font-bold text-indigo-600">
                            04
                        </p>

                        <h2 class="mt-3 font-semibold">
                            Services
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        id="formations"
        class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8"
    >
        <h2 class="text-3xl font-bold text-gray-900">
            Nos principales fonctionnalités
        </h2>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">
                    Informatique et IA
                </h3>

                <p class="mt-3 text-sm leading-6 text-gray-600">
                    Apprentissage des technologies numériques et
                    de l’intelligence artificielle.
                </p>
            </article>

            <article class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">
                    Préparation aux examens
                </h3>

                <p class="mt-3 text-sm leading-6 text-gray-600">
                    Ressources, exercices et accompagnement pédagogique.
                </p>
            </article>

            <article
                id="services"
                class="rounded-2xl bg-white p-6 shadow-sm"
            >
                <h3 class="font-bold text-gray-900">
                    Services professionnels
                </h3>

                <p class="mt-3 text-sm leading-6 text-gray-600">
                    CV ATS, portfolios et accompagnement des projets.
                </p>
            </article>
        </div>
    </section>
@endsection