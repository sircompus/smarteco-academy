$path0 = "C:\laragon\www\SEA\resources\views\portfolio\show.blade.php"
$content0 = @'
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
    <header class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 py-16 text-white" style="background: linear-gradient(to bottom right, #4f46e5, #4338ca, #6b21a8); color: #ffffff;">
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

        @if (filled($profile->effective_summary))
            <section class="rounded-2xl bg-white p-8 shadow-sm">
                <p class="text-center text-lg leading-8 text-gray-700">{{ $profile->effective_summary }}</p>
            </section>
        @endif

        @if ($profile->user?->profile?->bio)
            <section class="mt-8 rounded-2xl bg-white p-8 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Biographie</h2>
                <p class="mt-3 leading-7 text-gray-600">{{ $profile->user->profile->bio }}</p>
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

        <div class="mt-10 space-y-8">
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

        <div class="mt-8 space-y-8">
            {{-- Compétences --}}
            @if ($profile->skills->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Compétences</h2>

                    @php
                        $skillsByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
                    @endphp

                    @foreach ($skillsByCategory as $category => $categorySkills)
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $category }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-gray-700">
                            @foreach ($categorySkills as $skill)
                                <li>{{ $skill->name }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                </section>
            @endif

            {{-- Langues --}}
            @if ($profile->languages->isNotEmpty())
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Langues</h2>
                    <ul class="mt-4 space-y-1 text-sm text-gray-600">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} : {{ $lang->level_label }}</li>
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

'@
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/portfolio/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/portfolio/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\resources\views\student\cv\templates\ats.blade.php"
$content1 = @'
@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV — Version ATS')
@section('page-title', 'Mon CV — Version ATS')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 20mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-gray-800 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-10 font-mono text-sm leading-6 text-gray-900 print:border-0 print:p-0 print:shadow-none">

        <p class="mb-4 rounded-lg bg-amber-50 p-3 font-sans text-xs text-amber-700 print:hidden">
            Cette version simplifiée (une seule colonne, sans image, sans mise en forme complexe)
            est optimisée pour être correctement lue par les logiciels de tri automatique (ATS).
        </p>

        <h1 class="text-lg font-bold uppercase">{{ $profile->full_name }}</h1>
        @if ($profile->headline)
            <p>{{ $profile->headline }}</p>
        @endif

        <p class="mt-2">
            @if ($profile->email) {{ $profile->email }} @endif
            @if ($profile->phone) | {{ $profile->phone }} @endif
            @if ($profile->address) | {{ $profile->address }} @endif
        </p>

        @if ($profile->linkedin_url) <p>LinkedIn : {{ $profile->linkedin_url }}</p> @endif
        @if ($profile->github_url) <p>GitHub : {{ $profile->github_url }}</p> @endif

        @if (filled($profile->effective_summary))
            <h2 class="mt-6 font-bold uppercase">PROFIL</h2>
            <p>{{ $profile->effective_summary }}</p>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">EXPERIENCE PROFESSIONNELLE</h2>
            @foreach ($profile->experiences as $exp)
                <p class="mt-3 font-bold">{{ $exp->position }} - {{ $exp->company }}</p>
                <p>
                    {{ $exp->start_date?->format('m/Y') }} -
                    {{ $exp->is_current ? 'Present' : $exp->end_date?->format('m/Y') }}
                    @if ($exp->location) | {{ $exp->location }} @endif
                </p>
                @if ($exp->description)
                    <p>{{ $exp->description }}</p>
                @endif
            @endforeach
        @endif

        @if ($profile->educations->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">FORMATION</h2>
            @foreach ($profile->educations as $edu)
                <p class="mt-3 font-bold">{{ $edu->degree }} - {{ $edu->institution }}</p>
                <p>
                    {{ $edu->field_of_study }} |
                    {{ $edu->start_date?->format('Y') }} - {{ $edu->is_current ? 'Present' : $edu->end_date?->format('Y') }}
                </p>
                @if ($edu->description)
                    <p>{{ $edu->description }}</p>
                @endif
            @endforeach
        @endif

        @if ($profile->skills->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">COMPETENCES</h2>

            @php
                $skillsByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
            @endphp

            @foreach ($skillsByCategory as $category => $categorySkills)
                <p class="mt-3 font-semibold">{{ strtoupper($category) }} :</p>
                @foreach ($categorySkills as $skill)
                    <p>- {{ $skill->name }}</p>
                @endforeach
            @endforeach
        @endif

        @if ($profile->languages->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">LANGUES</h2>
            @foreach ($profile->languages as $lang)
                <p>{{ $lang->name }} : {{ $lang->level_label }}</p>
            @endforeach
        @endif

        @if ($profile->certifications->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">CERTIFICATIONS</h2>
            @foreach ($profile->certifications as $cert)
                <p>{{ $cert->name }} @if ($cert->issuer) - {{ $cert->issuer }} @endif @if ($cert->date_obtained) ({{ $cert->date_obtained->format('Y') }}) @endif</p>
            @endforeach
        @endif
    </div>
@endsection

'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/ats.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/ats.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\resources\views\student\cv\templates\classique.blade.php"
$content2 = @'
@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle classique')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
        }

        .cv-classique, .cv-classique * {
            font-family: 'Times New Roman', Times, serif !important;
            color: #000000 !important;
        }

        .cv-classique {
            font-size: 12pt;
            line-height: 1.5;
        }

        /* Titre principal : nom du candidat */
        .cv-classique h1 {
            font-size: 20pt;
            font-weight: bold;
        }

        /* Sous-titres : accroche + intitulés de section (Profil, Expérience...) */
        .cv-classique h2,
        .cv-classique .cv-subtitle {
            font-size: 16pt;
            font-weight: bold;
        }

        /* Paragraphes : tout le reste du texte */
        .cv-classique p,
        .cv-classique li {
            font-size: 12pt;
        }
    </style>
@endpush

@section('content')
    <div class="mb-4 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="cv-classique mx-auto max-w-3xl bg-white p-10 print:p-0 print:shadow-none" style="background:#ffffff; color:#000000;">

        <div class="border-b-2 border-gray-800 pb-4">
            <h1>{{ $profile->full_name }}</h1>

            @if ($profile->headline)
                <p class="cv-subtitle mt-1">{{ $profile->headline }}</p>
            @endif

            <p class="mt-2">
                @if ($profile->email) {{ $profile->email }} @endif
                @if ($profile->phone) — {{ $profile->phone }} @endif
                @if ($profile->address) — {{ $profile->address }} @endif
            </p>

            @if ($profile->linkedin_url)
                <p>{{ $profile->linkedin_url }}</p>
            @endif
        </div>

        @if (filled($profile->effective_summary))
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Profil</h2>
                <p class="mt-1">{{ $profile->effective_summary }}</p>
            </div>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Expérience professionnelle</h2>

                @foreach ($profile->experiences as $exp)
                    <div class="mt-2">
                        <p class="font-bold">
                            {{ $exp->position }} — {{ $exp->company }}
                            <span class="font-normal">
                                ({{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }})
                            </span>
                        </p>

                        @if ($exp->location)
                            <p>{{ $exp->location }}</p>
                        @endif

                        @if ($exp->description)
                            <p>{{ $exp->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($profile->educations->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Formation</h2>

                @foreach ($profile->educations as $edu)
                    <div class="mt-2">
                        <p class="font-bold">
                            {{ $edu->degree }} — {{ $edu->institution }}
                            <span class="font-normal">
                                ({{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }})
                            </span>
                        </p>

                        @if ($edu->field_of_study)
                            <p>{{ $edu->field_of_study }}</p>
                        @endif

                        @if ($edu->description)
                            <p>{{ $edu->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($profile->skills->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Compétences</h2>

                @php
                    $skillsByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
                @endphp

                @foreach ($skillsByCategory as $category => $categorySkills)
                    <p class="mt-2 font-bold">{{ $category }} :</p>
                    <p>{{ $categorySkills->pluck('name')->implode(', ') }}</p>
                @endforeach
            </div>
        @endif

        @if ($profile->languages->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Langues</h2>
                @foreach ($profile->languages as $lang)
                    <p>{{ $lang->name }} : {{ $lang->level_label }}</p>
                @endforeach
            </div>
        @endif

        @if ($profile->certifications->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Certifications</h2>
                @foreach ($profile->certifications as $cert)
                    <p>
                        {{ $cert->name }}
                        @if ($cert->issuer) — {{ $cert->issuer }} @endif
                        @if ($cert->date_obtained) ({{ $cert->date_obtained->format('Y') }}) @endif
                    </p>
                @endforeach
            </div>
        @endif
    </div>
@endsection

'@
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/classique.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/classique.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\resources\views\student\cv\templates\moderne.blade.php"
$content3 = @'
@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle moderne')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 0mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto grid max-w-3xl grid-cols-3 overflow-hidden rounded-2xl border border-gray-200 bg-white text-sm print:mx-0 print:max-w-none print:rounded-none print:border-0 print:shadow-none">

        {{-- Colonne latérale --}}
        <div class="col-span-1 bg-indigo-600 p-6 text-white">
            @if ($profile->photo_url)
                <img src="{{ $profile->photo_url }}" class="h-24 w-24 rounded-full border-4 border-white/30 object-cover">
            @endif

            <h1 class="mt-4 text-lg font-extrabold leading-tight">{{ $profile->full_name }}</h1>
            @if ($profile->headline)
                <p class="mt-1 text-xs text-indigo-100">{{ $profile->headline }}</p>
            @endif

            <div class="mt-6 space-y-1 text-xs text-indigo-100">
                @if ($profile->email) <p>{{ $profile->email }}</p> @endif
                @if ($profile->phone) <p>{{ $profile->phone }}</p> @endif
                @if ($profile->address) <p>{{ $profile->address }}</p> @endif
                @if ($profile->linkedin_url) <p class="break-all">{{ $profile->linkedin_url }}</p> @endif
            </div>

            @if ($profile->skills->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-white">Compétences</h2>

                    @php
                        $skillsByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
                    @endphp

                    @foreach ($skillsByCategory as $category => $categorySkills)
                        <p class="mt-3 text-[10px] font-semibold uppercase text-indigo-200">{{ $category }}</p>

                        <div class="mt-1 space-y-2">
                            @foreach ($categorySkills as $skill)
                                <div>
                                    <p class="text-xs text-indigo-100">{{ $skill->name }}</p>
                                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                                        <div class="h-full bg-white" style="width: {{ $skill->level_percent }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($profile->languages->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-white">Langues</h2>
                    <ul class="mt-2 space-y-1 text-xs text-indigo-100">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} : {{ $lang->level_label }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Colonne principale --}}
        <div class="col-span-2 p-6">
            @if (filled($profile->effective_summary))
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Profil</h2>
                    <p class="mt-2 leading-6 text-gray-700">{{ $profile->effective_summary }}</p>
                </div>
            @endif

            @if ($profile->experiences->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Expérience</h2>
                    <div class="mt-2 space-y-4">
                        @foreach ($profile->experiences as $exp)
                            <div class="border-l-2 border-indigo-100 pl-3">
                                <p class="font-semibold text-gray-900">{{ $exp->position }}</p>
                                <p class="text-xs text-gray-500">{{ $exp->company }} · {{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}</p>
                                @if ($exp->description)
                                    <p class="mt-1 text-gray-700">{{ $exp->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->educations->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Formation</h2>
                    <div class="mt-2 space-y-3">
                        @foreach ($profile->educations as $edu)
                            <div class="border-l-2 border-indigo-100 pl-3">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }}</p>
                                <p class="text-xs text-gray-500">{{ $edu->institution }} · {{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->certifications->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Certifications</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->certifications as $cert)
                            <li>{{ $cert->name }} @if ($cert->issuer) — {{ $cert->issuer }} @endif</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

'@
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/moderne.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/moderne.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
