@extends('layouts.cv-print')

@section('title', 'CV classique — '.$profile->full_name)
@section('page-margin', '15mm')
@section('cv-body-class', 'cv-template-classique')

@section('content')
    <article
        class="cv-page cv-page--padded"
        data-cv-template="classique"
    >
        <header class="cv-section border-b-2 border-gray-900 pb-4">
            <div class="flex items-start justify-between gap-8">
                <div class="min-w-0 flex-1">
                    <h1 class="cv-name">
                        {{ $profile->full_name }}
                    </h1>

                    @if ($profile->headline)
                        <p class="cv-subtitle mt-1">
                            {{ $profile->headline }}
                        </p>
                    @endif

                    <div class="cv-contact mt-2">
                        <p>
                            @if ($profile->email)
                                {{ $profile->email }}
                            @endif

                            @if ($profile->phone)
                                · {{ $profile->phone }}
                            @endif
                        </p>

                        @if ($profile->address)
                            <p>{{ $profile->address }}</p>
                        @endif

                        @if (
                            $profile->linkedin_url
                            || $profile->github_url
                            || $profile->website_url
                        )
                            <p class="cv-url mt-1">
                                @if ($profile->linkedin_url)
                                    LinkedIn : {{ $profile->linkedin_url }}
                                @endif

                                @if ($profile->github_url)
                                    · GitHub : {{ $profile->github_url }}
                                @endif

                                @if ($profile->website_url)
                                    · Site : {{ $profile->website_url }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>

                @if ($profile->photo_url)
                    <img
                        src="{{ $profile->photo_url }}"
                        alt="Photo de {{ $profile->full_name }}"
                        class="cv-photo shrink-0"
                    >
                @endif
            </div>
        </header>

        @if (filled($profile->effective_summary))
            <section class="cv-section mt-5">
                <h2 class="cv-title uppercase tracking-wide">
                    Profil
                </h2>

                <p class="cv-description mt-2">
                    {{ \App\Support\CvTextFormatter::clean($profile->effective_summary) }}
                </p>
            </section>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <section class="mt-5">
                <h2 class="cv-title cv-section uppercase tracking-wide">
                    Expérience professionnelle
                </h2>

                @foreach ($profile->experiences as $experience)
                    <div class="cv-entry mt-3">
                        <div class="flex items-start justify-between gap-5">
                            <p class="cv-subtitle">
                                {{ $experience->position }}
                                — {{ $experience->company }}
                            </p>

                            <p class="cv-meta shrink-0">
                                {{ $experience->start_date?->format('m/Y') }}
                                –
                                {{ $experience->is_current
                                    ? 'Présent'
                                    : $experience->end_date?->format('m/Y') }}
                            </p>
                        </div>

                        @if ($experience->location)
                            <p class="cv-body-text italic">
                                {{ $experience->location }}
                            </p>
                        @endif

                        @if ($experience->description)
                            <p class="cv-description mt-1">
                                {{ \App\Support\CvTextFormatter::clean($experience->description) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        @if ($profile->educations->isNotEmpty())
            <section class="mt-5">
                <h2 class="cv-title cv-section uppercase tracking-wide">
                    Formation
                </h2>

                @foreach ($profile->educations as $education)
                    <div class="cv-entry mt-3">
                        <div class="flex items-start justify-between gap-5">
                            <p class="cv-subtitle">
                                {{ $education->degree ?: $education->field_of_study }}
                                — {{ $education->institution }}
                            </p>

                            <p class="cv-meta shrink-0">
                                {{ $education->start_date?->format('Y') }}
                                –
                                {{ $education->is_current
                                    ? 'Présent'
                                    : $education->end_date?->format('Y') }}
                            </p>
                        </div>

                        @if (
                            $education->degree
                            && $education->field_of_study
                        )
                            <p class="cv-body-text">
                                {{ $education->field_of_study }}
                            </p>
                        @endif

                        @if ($education->description)
                            <p class="cv-description mt-1">
                                {{ \App\Support\CvTextFormatter::clean($education->description) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        @if ($profile->skills->isNotEmpty())
            <section class="cv-section mt-5">
                <h2 class="cv-title uppercase tracking-wide">
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
                <h2 class="cv-title cv-section uppercase tracking-wide">
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
                            <p class="cv-body-text mt-1">
                                {{ $project->tags }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        @if ($profile->languages->isNotEmpty())
            <section class="cv-section mt-5">
                <h2 class="cv-title uppercase tracking-wide">
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
                <h2 class="cv-title uppercase tracking-wide">
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
