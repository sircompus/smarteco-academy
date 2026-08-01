@extends('layouts.student')

@section('title', 'Mon CV & Portfolio')
@section('page-title', 'Mon CV & Portfolio')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Actions rapides : téléchargements + partage --}}
    <section id="exports" class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold">Exports & partage</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Génère ton CV, sa version ATS, ou partage ton portfolio public.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('student.cv.download.cv') }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Voir / Imprimer mon CV
                </a>

                <a href="{{ route('student.cv.download.ats') }}" target="_blank" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                    Version ATS
                </a>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 rounded-xl bg-gray-50 p-4">
            <form method="POST" action="{{ route('student.cv.public.toggle') }}">
                @csrf
                @method('PATCH')
                <button class="rounded-lg {{ $profile->is_public ? 'bg-red-50 text-red-600' : 'bg-green-600 text-white' }} px-4 py-2 text-sm font-semibold">
                    {{ $profile->is_public ? 'Rendre privé' : 'Rendre public' }}
                </button>
            </form>

            @if ($profile->is_public && $profile->public_url)
                <div class="text-sm">
                    <span class="text-gray-500">Lien public :</span>
                    <a href="{{ $profile->public_url }}" target="_blank" class="font-semibold text-indigo-600 hover:underline">
                        {{ $profile->public_url }}
                    </a>
                </div>
            @else
                <p class="text-sm text-gray-400">Ton portfolio est actuellement privé.</p>
            @endif
        </div>
    </section>

    {{-- Score ATS --}}
    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-lg font-bold">Score de compatibilité ATS</h2>

            <div class="flex items-center gap-3">
                <div class="h-3 w-40 overflow-hidden rounded-full bg-gray-100">
                    <div
                        class="h-full {{ $ats['score'] >= 80 ? 'bg-green-500' : ($ats['score'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                        style="width: {{ $ats['score'] }}%"
                    ></div>
                </div>
                <span class="text-xl font-extrabold {{ $ats['score'] >= 80 ? 'text-green-600' : ($ats['score'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $ats['score'] }}/100
                </span>
            </div>
        </div>

        <div class="mt-4 grid gap-2 md:grid-cols-2">
            @foreach ($ats['checks'] as $check)
                <div class="flex items-start gap-2 text-sm">
                    <span>{{ $check['passed'] ? '✅' : '⚠️' }}</span>
                    <div>
                        <p class="{{ $check['passed'] ? 'text-gray-700' : 'font-medium text-amber-700' }}">
                            {{ $check['label'] }}
                        </p>
                        @if (! $check['passed'])
                            <p class="text-xs text-gray-500">{{ $check['advice'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Informations personnelles --}}
    <section id="profile-info" class="mt-8 rounded-2xl bg-white p-6 shadow-sm" x-data="{ open: true }">
        <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
            <h2 class="text-lg font-bold">Informations personnelles</h2>
            <span x-text="open ? '−' : '+'" class="text-xl text-gray-400"></span>
        </button>

        <form
            x-show="open"
            method="POST"
            action="{{ route('student.cv.profile.update') }}"
            enctype="multipart/form-data"
            class="mt-4 grid gap-4 md:grid-cols-2"
        >
            @csrf
            @method('PATCH')

            <div class="md:col-span-2 flex items-center gap-4">
                @if ($profile->photo_url)
                    <img src="{{ $profile->photo_url }}" class="h-16 w-16 rounded-full object-cover" alt="Photo">
                @endif
                <input type="file" name="photo" accept="image/*" class="text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Nom complet</label>
                <input name="full_name" value="{{ old('full_name', $profile->full_name) }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="text-sm font-medium">Titre / accroche</label>
                <input name="headline" value="{{ old('headline', $profile->headline) }}" placeholder="Ex : Étudiant en Gestion — Comptabilité" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $profile->email) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">Téléphone</label>
                <input name="phone" value="{{ old('phone', $profile->phone) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium">Adresse</label>
                <input name="address" value="{{ old('address', $profile->address) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium">Résumé professionnel</label>
                <textarea name="summary" rows="4" class="mt-1 block w-full rounded-lg border-gray-300" placeholder="Laisse vide pour une génération automatique basée sur ton profil (formations, expériences, compétences)">{{ old('summary', $profile->summary) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">
                    Si tu laisses ce champ vide, un résumé sera généré automatiquement à partir de tes formations,
                    expériences et compétences sur ton CV et ton portfolio (rien n'est enregistré tant que tu n'écris pas le tien).
                </p>
            </div>

            <div>
                <label class="text-sm font-medium">LinkedIn</label>
                <input name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" placeholder="https://linkedin.com/in/..." class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">GitHub</label>
                <input name="github_url" value="{{ old('github_url', $profile->github_url) }}" placeholder="https://github.com/..." class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium">Site web personnel</label>
                <input name="website_url" value="{{ old('website_url', $profile->website_url) }}" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-sm font-medium">Modèle de CV</label>
                <select name="cv_template" class="mt-1 block w-full rounded-lg border-gray-300">
                    <option value="classique" @selected($profile->cv_template === 'classique')>Classique</option>
                    <option value="moderne" @selected($profile->cv_template === 'moderne')>Moderne</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Modèle de portfolio</label>
                <select name="portfolio_template" class="mt-1 block w-full rounded-lg border-gray-300">
                    <option value="elegant" @selected($profile->portfolio_template === 'elegant')>Élégant</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                    Enregistrer
                </button>
            </div>
        </form>
    </section>

    @include('student.cv._section-educations', ['profile' => $profile])
    @include('student.cv._section-experiences', ['profile' => $profile])
    @include('student.cv._section-skills', ['profile' => $profile])
    @include('student.cv._section-languages', ['profile' => $profile])
    @include('student.cv._section-certifications', ['profile' => $profile])
    @include('student.cv._section-projects', ['profile' => $profile])
@endsection
