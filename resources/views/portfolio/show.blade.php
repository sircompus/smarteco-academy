<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $profile->full_name }} — Portfolio</title>
    <meta name="description" content="{{ $profile->headline }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            @page { size: A4; margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <div class="no-print flex justify-center py-6">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    {{-- Bandeau d'en-tête --}}
    <header class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 py-16 text-white">
        <div class="mx-auto max-w-4xl px-6 text-center">
            @if ($profile->photo_url)
                <img
                    src="{{ $profile->photo_url }}"
                    class="mx-auto h-32 w-32 rounded-full border-4 border-white/40 object-cover shadow-xl"
                >
            @endif

            <h1 class="mt-6 text-4xl font-extrabold">{{ $profile->full_name }}</h1>

            @if ($profile->headline)
                <p class="mt-3 text-lg text-indigo-100">{{ $profile->headline }}</p>
            @endif

            <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm text-indigo-100">
                @if ($profile->email)
                    <a href="mailto:{{ $profile->email }}" class="hover:text-white">{{ $profile->email }}</a>
                @endif
                @if ($profile->phone)
                    <span>{{ $profile->phone }}</span>
                @endif
                @if ($profile->linkedin_url)
                    <a href="{{ $profile->linkedin_url }}" target="_blank" class="hover:text-white">LinkedIn</a>
                @endif
                @if ($profile->github_url)
                    <a href="{{ $profile->github_url }}" target="_blank" class="hover:text-white">GitHub</a>
                @endif
                @if ($profile->website_url)
                    <a href="{{ $profile->website_url }}" target="_blank" class="hover:text-white">Site web</a>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-6 py-12">

        @if ($profile->summary)
            <section class="rounded-2xl bg-white p-8 shadow-sm">
                <p class="text-center text-lg leading-8 text-gray-700">{{ $profile->summary }}</p>
            </section>
        @endif

        {{-- Projets --}}
        @if ($profile->projects->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-2xl font-extrabold text-gray-900">Projets</h2>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    @foreach ($profile->projects as $project)
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md">
                            @if ($project->image_url)
                                <img src="{{ $project->image_url }}" class="h-48 w-full object-cover">
                            @endif

                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900">{{ $project->title }}</h3>

                                @if ($project->description)
                                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ $project->description }}</p>
                                @endif

                                @if ($project->tags_array)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($project->tags_array as $tag)
                                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4 flex gap-3 text-sm font-semibold">
                                    @if ($project->project_url)
                                        <a href="{{ $project->project_url }}" target="_blank" class="text-indigo-600 hover:underline">Voir le projet →</a>
                                    @endif
                                    @if ($project->repo_url)
                                        <a href="{{ $project->repo_url }}" target="_blank" class="text-gray-500 hover:underline">Code source</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-10 grid gap-8 md:grid-cols-2">
            {{-- Expérience --}}
            @if ($profile->experiences->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Expérience</h2>

                    <div class="mt-4 space-y-4">
                        @foreach ($profile->experiences as $exp)
                            <div class="border-l-2 border-indigo-100 pl-4">
                                <p class="font-semibold text-gray-900">{{ $exp->position }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $exp->company }} · {{ $exp->start_date?->format('m/Y') }} –
                                    {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}
                                </p>
                                @if ($exp->description)
                                    <p class="mt-1 text-sm text-gray-600">{{ $exp->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Formation --}}
            @if ($profile->educations->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Formation</h2>

                    <div class="mt-4 space-y-4">
                        @foreach ($profile->educations as $edu)
                            <div class="border-l-2 border-indigo-100 pl-4">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $edu->institution }} · {{ $edu->start_date?->format('Y') }} –
                                    {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="mt-8 grid gap-8 md:grid-cols-3">
            {{-- Compétences --}}
            @if ($profile->skills->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Compétences</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($profile->skills as $skill)
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {{ $skill->name }}
                            </span>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Langues --}}
            @if ($profile->languages->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Langues</h2>
                    <ul class="mt-4 space-y-1 text-sm text-gray-600">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — {{ $lang->level_label }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Certifications --}}
            @if ($profile->certifications->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Certifications</h2>
                    <ul class="mt-4 space-y-1 text-sm text-gray-600">
                        @foreach ($profile->certifications as $cert)
                            <li>{{ $cert->name }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </main>

    <footer class="border-t border-gray-200 py-8 text-center text-xs text-gray-400">
        Portfolio généré via SmartEco Academy
    </footer>
</body>
</html>
