$path0 = "C:\laragon\www\SEA\database\seeders\SkillSuggestionSeeder.php"
$content0 = @'
<?php

namespace Database\Seeders;

use App\Models\SkillSuggestion;
use Illuminate\Database\Seeder;

class SkillSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // --- COMPÉTENCES TECHNIQUES ET NUMÉRIQUES ---
            'Intelligence artificielle et analyse' => [
                'IA générative appliquée au management',
                "Analyse et synthèse de l'information",
                'Veille stratégique',
                'Machine learning',
                'Business intelligence',
                'Power BI',
                'R (langage statistique)',
                'Python',
            ],
            "Systèmes d'information et cloud" => [
                'Gouvernance des SI',
                'Transformation numérique',
                'Google Cloud Platform',
                'Docker',
                'Kubernetes',
                'Ansible',
                'Maven',
                'SQL Server',
                'Bases de données',
            ],
            'Développement et outils numériques' => [
                'PHP',
                'HTML',
                'JEE',
                'Outils web',
                'Word',
                'Excel',
                'Access',
                'PowerPoint',
                'Publisher',
                'MS Project',
            ],
            'Logiciels de gestion' => [
                'SAGE SAARI',
                'SAGE i7',
                'CIEL',
                'ATLAS',
                'EBP',
            ],
            'Infrastructure et maintenance' => [
                'Hardware',
                'Réseaux',
                'Électromécanique',
                'Électronique industrielle',
                'Logiciels de simulation',
            ],

            // --- COMPÉTENCES PROFESSIONNELLES ---
            'Compétences professionnelles' => [
                'Rigueur, autonomie, persévérance et sens des responsabilités',
                "Capacité d'analyse, de synthèse, d'organisation et de résolution de problèmes",
                'Pédagogie, communication, vulgarisation et accompagnement du changement',
                'Travail en équipe, relation avec les parties prenantes et orientation résultats',
            ],
        ];

        // On repart d'un catalogue propre avec cette structure.
        SkillSuggestion::query()->delete();

        $sortOrder = 0;

        foreach ($skills as $category => $names) {
            foreach ($names as $name) {
                SkillSuggestion::updateOrCreate(
                    ['name' => $name],
                    [
                        'category' => $category,
                        'is_active' => true,
                        'sort_order' => $sortOrder,
                    ]
                );

                $sortOrder++;
            }
        }
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/seeders/SkillSuggestionSeeder.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/seeders/SkillSuggestionSeeder.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\resources\views\student\cv\_section-skills.blade.php"
$content1 = @'
<section id="skills" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Compétences</h2>

    @if ($profile->skills->isNotEmpty())
        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-400">Déjà ajoutées</p>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($profile->skills as $skill)
                <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                    <span class="font-medium text-indigo-700">{{ $skill->name }}</span>
                    <span class="text-xs text-indigo-400">({{ $skill->level_label }})</span>
                    <form method="POST" action="{{ route('student.cv.skills.destroy', $skill) }}">
                        @csrf @method('DELETE')
                        <button class="text-indigo-400 hover:text-red-600">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('student.cv.skills.store') }}" class="mt-5 rounded-xl border border-dashed border-gray-300 p-4">
        @csrf

        <p class="text-sm font-medium text-gray-700">
            Coche toutes les compétences qui te concernent, choisis un niveau, puis valide en une seule fois.
        </p>

        @php
            $alreadyHave = $profile->skills->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();

            $suggestedByCategory = \App\Models\SkillSuggestion::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($s) => $s->category ?: 'Autres');
        @endphp

        <div class="mt-3 space-y-5">
            @foreach ($suggestedByCategory as $category => $categorySkills)
                @php
                    $visibleSkills = $categorySkills->filter(
                        fn ($s) => ! in_array(mb_strtolower($s->name), $alreadyHave, true)
                    );
                @endphp

                @if ($visibleSkills->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-indigo-600">{{ $category }}</p>

                        <div class="mt-2 grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 md:grid-cols-3">
                            @foreach ($visibleSkills as $suggestedSkill)
                                <label class="flex items-start gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="skills[]" value="{{ $suggestedSkill->name }}" class="mt-0.5 rounded border-gray-300">
                                    <span>{{ $suggestedSkill->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">Niveau (appliqué à tout ce que tu coches)</label>
                <select name="level" class="mt-1 block rounded-lg border-gray-300">
                    <option value="debutant">Débutant</option>
                    <option value="intermediaire" selected>Intermédiaire</option>
                    <option value="avance">Avancé</option>
                    <option value="expert">Expert</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500">Autre compétence (non listée)</label>
                <input name="custom_skill" placeholder="Ex : Une compétence spécifique" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Ajouter les compétences cochées
            </button>
        </div>
    </form>
</section>

'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-skills.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-skills.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\resources\views\student\cv\templates\ats.blade.php"
$content2 = @'
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
            @foreach ($profile->skills as $skill)
                <p>- {{ $skill->name }}</p>
            @endforeach
        @endif

        @if ($profile->languages->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">LANGUES</h2>
            <p>
                @foreach ($profile->languages as $lang)
                    {{ $lang->name }} ({{ $lang->level_label }}){{ ! $loop->last ? ', ' : '' }}
                @endforeach
            </p>
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
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/ats.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/ats.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
