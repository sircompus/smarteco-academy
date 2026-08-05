@extends('layouts.cv-print')

@section('title', 'CV ATS — '.$profile->full_name)
@section('page-margin', '16mm')
@section('cv-body-class', 'cv-template-ats')

@section('content')
    <article
        class="cv-page cv-page--padded"
        data-cv-template="ats"
    >
        <p class="cv-screen-only mb-5 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
            Version une colonne, sans photo ni éléments graphiques,
            conçue pour faciliter la lecture par les logiciels ATS.
        </p>

        <header class="cv-section border-b border-black pb-3">
            <h1 class="cv-name uppercase">
                {{ $profile->full_name }}
            </h1>

            @if ($profile->headline)
                <p class="cv-subtitle mt-1">
                    {{ $profile->headline }}
                </p>
            @endif

            <div class="cv-contact mt-2 space-y-0.5">
                @if ($profile->email)
                    <p>E-mail : {{ $profile->email }}</p>
                @endif

                @if ($profile->phone)
                    <p>Téléphone : {{ $profile->phone }}</p>
                @endif

                @if ($profile->address)
                    <p>Adresse : {{ $profile->address }}</p>
                @endif

                @if ($profile->linkedin_url)
                    <p class="cv-url">
                        LinkedIn : {{ $profile->linkedin_url }}
                    </p>
                @endif

                @if ($profile->github_url)
                    <p class="cv-url">
                        GitHub : {{ $profile->github_url }}
                    </p>
                @endif

                @if ($profile->website_url)
                    <p class="cv-url">
                        Site web : {{ $profile->website_url }}
                    </p>
                @endif
            </div>
        </header>

        @if (filled($profile->effective_summary))
            <section class="cv-section mt-5">
                <h2 class="cv-title uppercase">
                    Profil professionnel
                </h2>

                <p class="cv-description mt-2">
                    {{ \App\Support\CvTextFormatter::clean($profile->effective_summary) }}
                </p>
            </section>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <section class="mt-5">
                <h2 class="cv-title cv-section uppercase">
                    Expérience professionnelle
                </h2>

                @foreach ($profile->experiences as $exp)
                    <div class="cv-entry mt-3">
                        <p class="cv-subtitle">
                            {{ $exp->position }} — {{ $exp->company }}
                        </p>

                        <p class="cv-meta">
                            {{ $exp->start_date?->format('m/Y') }}
                            –
                            {{ $exp->is_current
                                ? 'Présent'
                                : $exp->end_date?->format('m/Y') }}

                            @if ($exp->location)
                                | {{ $exp->location }}
                            @endif
                        </p>

                        @if ($exp->description)
                            <p class="cv-description mt-1">
                                {{ \App\Support\CvTextFormatter::clean($exp->description) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        @if ($profile->educations->isNotEmpty())
            <section class="mt-5">
                <h2 class="cv-title cv-section uppercase">
                    Formation
                </h2>

                @foreach ($profile->educations as $edu)
                    <div class="cv-entry mt-3">
                        <p class="cv-subtitle">
                            {{ $edu->degree ?: $edu->field_of_study }}
                            — {{ $edu->institution }}
                        </p>

                        <p class="cv-meta">
                            {{ $edu->start_date?->format('Y') }}
                            –
                            {{ $edu->is_current
                                ? 'Présent'
                                : $edu->end_date?->format('Y') }}
                        </p>

                        @if ($edu->degree && $edu->field_of_study)
                            <p class="cv-body-text">
                                {{ $edu->field_of_study }}
                            </p>
                        @endif

                        @if ($edu->description)
                            <p class="cv-description mt-1">
                                {{ \App\Support\CvTextFormatter::clean($edu->description) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        @if ($profile->skills->isNotEmpty())
            <section class="cv-section mt-5">
                <h2 class="cv-title uppercase">
                    Compétences
                </h2>

                @php
                    $skillsByCategory = $profile->skills->groupBy(
                        fn ($skill) => $skill->category ?: 'Autres'
                    );
                @endphp

                @foreach ($skillsByCategory as $category => $categorySkills)
                    <p class="cv-body-text mt-2">
                        <strong>{{ $category }} :</strong>
                        {{ $categorySkills->pluck('name')->implode(', ') }}
                    </p>
                @endforeach
            </section>
        @endif

        @if ($profile->projects->isNotEmpty())
            <section class="mt-5">
                <h2 class="cv-title cv-section uppercase">
                    Projets
                </h2>

                @foreach ($profile->projects as $project)
                    <div class="cv-entry mt-3">
                        <p class="cv-subtitle">{{ $project->title }}</p>

                        @if ($project->description)
                            <p class="cv-description mt-1">
                                {{ \App\Support\CvTextFormatter::clean($project->description) }}
                            </p>
                        @endif

                        @if ($project->tags)
                            <p class="cv-body-text">
                                Technologies : {{ $project->tags }}
                            </p>
                        @endif

                        @if ($project->project_url)
                            <p class="cv-url cv-body-text">
                                Projet : {{ $project->project_url }}
                            </p>
                        @endif

                        @if ($project->repo_url)
                            <p class="cv-url cv-body-text">
                                Code : {{ $project->repo_url }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        @if ($profile->languages->isNotEmpty())
            <section class="cv-section mt-5">
                <h2 class="cv-title uppercase">
                    Langues
                </h2>

                @foreach ($profile->languages as $language)
                    <p class="cv-body-text mt-1">
                        {{ $language->name }} :
                        {{ $language->level_label }}
                    </p>
                @endforeach
            </section>
        @endif

        @if ($profile->certifications->isNotEmpty())
            <section class="cv-section mt-5">
                <h2 class="cv-title uppercase">
                    Certifications
                </h2>

                @foreach ($profile->certifications as $certification)
                    <p class="cv-body-text mt-1">
                        {{ $certification->name }}

                        @if ($certification->issuer)
                            — {{ $certification->issuer }}
                        @endif

                        @if ($certification->date_obtained)
                            ({{ $certification->date_obtained->format('Y') }})
                        @endif
                    </p>
                @endforeach
            </section>
        @endif
    </article>
@endsection
