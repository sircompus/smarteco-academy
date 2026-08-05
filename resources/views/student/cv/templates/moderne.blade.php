@extends('layouts.cv-print')

@section('title', 'CV moderne — '.$profile->full_name)
@section('page-margin', '0mm')
@section('cv-body-class', 'cv-template-moderne')

@section('content')
    <article
        class="cv-page grid grid-cols-[32%_68%] overflow-hidden bg-white"
        data-cv-template="moderne"
    >
        <aside class="bg-indigo-700 p-[11mm] text-white">
            @if ($profile->photo_url)
                <img
                    src="{{ $profile->photo_url }}"
                    alt="Photo de {{ $profile->full_name }}"
                    class="cv-photo cv-photo--round border-4 border-white/30"
                >
            @endif

            <h1 class="cv-name mt-5 text-white">
                {{ $profile->full_name }}
            </h1>

            @if ($profile->headline)
                <p class="cv-subtitle mt-2 text-indigo-100">
                    {{ $profile->headline }}
                </p>
            @endif

            <section class="cv-section mt-7">
                <h2 class="cv-title text-white">
                    Contact
                </h2>

                <div class="cv-contact mt-2 space-y-1 text-indigo-50">
                    @if ($profile->email)
                        <p class="cv-url">{{ $profile->email }}</p>
                    @endif

                    @if ($profile->phone)
                        <p>{{ $profile->phone }}</p>
                    @endif

                    @if ($profile->address)
                        <p>{{ $profile->address }}</p>
                    @endif

                    @if ($profile->linkedin_url)
                        <p class="cv-url">{{ $profile->linkedin_url }}</p>
                    @endif

                    @if ($profile->github_url)
                        <p class="cv-url">{{ $profile->github_url }}</p>
                    @endif

                    @if ($profile->website_url)
                        <p class="cv-url">{{ $profile->website_url }}</p>
                    @endif
                </div>
            </section>

            @if ($profile->skills->isNotEmpty())
                <section class="cv-section mt-7">
                    <h2 class="cv-title text-white">
                        Compétences
                    </h2>

                    @php
                        $skillsByCategory = $profile->skills->groupBy(
                            fn ($skill) => $skill->category ?: 'Autres'
                        );
                    @endphp

                    @foreach ($skillsByCategory as $category => $categorySkills)
                        <p class="cv-subtitle mt-3 text-indigo-100">
                            {{ $category }}
                        </p>

                        <ul class="cv-body-text mt-1 space-y-1 text-indigo-50">
                            @foreach ($categorySkills as $skill)
                                <li>• {{ $skill->name }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                </section>
            @endif

            @if ($profile->languages->isNotEmpty())
                <section class="cv-section mt-7">
                    <h2 class="cv-title text-white">
                        Langues
                    </h2>

                    <ul class="cv-body-text mt-2 space-y-1 text-indigo-50">
                        @foreach ($profile->languages as $language)
                            <li>
                                {{ $language->name }} :
                                {{ $language->level_label }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>

        <div class="p-[11mm] text-gray-800">
            @if (filled($profile->effective_summary))
                <section class="cv-section">
                    <h2 class="cv-title uppercase tracking-wider text-indigo-700">
                        Profil
                    </h2>

                    <p class="cv-description mt-2">
                        {{ \App\Support\CvTextFormatter::clean($profile->effective_summary) }}
                    </p>
                </section>
            @endif

            @if ($profile->experiences->isNotEmpty())
                <section class="mt-6">
                    <h2 class="cv-title cv-section uppercase tracking-wider text-indigo-700">
                        Expérience
                    </h2>

                    @foreach ($profile->experiences as $experience)
                        <div class="cv-entry mt-3 border-l-2 border-indigo-100 pl-3">
                            <p class="cv-subtitle text-gray-950">
                                {{ $experience->position }}
                            </p>

                            <p class="cv-meta text-gray-600">
                                {{ $experience->company }}
                                ·
                                {{ $experience->start_date?->format('m/Y') }}
                                –
                                {{ $experience->is_current
                                    ? 'Présent'
                                    : $experience->end_date?->format('m/Y') }}
                            </p>

                            @if ($experience->location)
                                <p class="cv-meta text-gray-600">
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
                <section class="mt-6">
                    <h2 class="cv-title cv-section uppercase tracking-wider text-indigo-700">
                        Formation
                    </h2>

                    @foreach ($profile->educations as $education)
                        <div class="cv-entry mt-3 border-l-2 border-indigo-100 pl-3">
                            <p class="cv-subtitle text-gray-950">
                                {{ $education->degree ?: $education->field_of_study }}
                            </p>

                            <p class="cv-meta text-gray-600">
                                {{ $education->institution }}
                                ·
                                {{ $education->start_date?->format('Y') }}
                                –
                                {{ $education->is_current
                                    ? 'Présent'
                                    : $education->end_date?->format('Y') }}
                            </p>

                            @if (
                                $education->degree
                                && $education->field_of_study
                            )
                                <p class="cv-body-text mt-1">
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

            @if ($profile->projects->isNotEmpty())
                <section class="mt-6">
                    <h2 class="cv-title cv-section uppercase tracking-wider text-indigo-700">
                        Projets
                    </h2>

                    @foreach ($profile->projects as $project)
                        <div class="cv-entry mt-3 border-l-2 border-indigo-100 pl-3">
                            <p class="cv-subtitle text-gray-950">
                                {{ $project->title }}
                            </p>

                            @if ($project->description)
                                <p class="cv-description mt-1">
                                    {{ \App\Support\CvTextFormatter::clean($project->description) }}
                                </p>
                            @endif

                            @if ($project->tags)
                                <p class="cv-body-text mt-1 text-gray-600">
                                    {{ $project->tags }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </section>
            @endif

            @if ($profile->certifications->isNotEmpty())
                <section class="cv-section mt-6">
                    <h2 class="cv-title uppercase tracking-wider text-indigo-700">
                        Certifications
                    </h2>

                    <ul class="cv-body-text mt-2 space-y-1">
                        @foreach ($profile->certifications as $certification)
                            <li>
                                {{ $certification->name }}

                                @if ($certification->issuer)
                                    — {{ $certification->issuer }}
                                @endif

                                @if ($certification->date_obtained)
                                    ({{ $certification->date_obtained->format('Y') }})
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </article>
@endsection
