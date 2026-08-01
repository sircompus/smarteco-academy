$path0 = "C:\laragon\www\SEA\app\Http\Controllers\Admin\CvController.php"
$content0 = @'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CvProfile;
use App\Models\User;
use App\Services\AtsScoreService;
use Illuminate\View\View;

class CvController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['etudiant', 'stagiaire']);
            })
            ->with('cvProfile')
            ->orderBy('name')
            ->get();

        return view('admin.cv.index', ['users' => $users]);
    }

    public function show(User $user): View
    {
        $profile = $user->cvProfile;

        if ($profile) {
            $profile->load([
                'educations', 'experiences', 'skills',
                'languages', 'certifications', 'projects',
            ]);
        }

        $ats = $profile ? app(AtsScoreService::class)->evaluate($profile) : null;

        return view('admin.cv.show', [
            'targetUser' => $user,
            'profile' => $profile,
            'ats' => $ats,
        ]);
    }

    public function showCv(User $user): View
    {
        $profile = $user->cvProfile()->firstOrFail();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications']);

        $view = $profile->cv_template === 'moderne' ? 'student.cv.templates.moderne' : 'student.cv.templates.classique';

        return view($view, ['profile' => $profile, 'layout' => 'layouts.admin']);
    }

    public function showAts(User $user): View
    {
        $profile = $user->cvProfile()->firstOrFail();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications']);

        return view('student.cv.templates.ats', ['profile' => $profile, 'layout' => 'layouts.admin']);
    }
}

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/CvController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/CvController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Models\CvProfile.php"
$content1 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CvProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'full_name',
        'headline',
        'email',
        'phone',
        'address',
        'photo_path',
        'summary',
        'linkedin_url',
        'github_url',
        'website_url',
        'cv_template',
        'portfolio_template',
        'is_public',
        'public_slug',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CvEducation::class)->orderBy('sort_order')->orderByDesc('end_date');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CvExperience::class)->orderBy('sort_order')->orderByDesc('end_date');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(CvSkill::class)->orderBy('sort_order');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(CvLanguage::class)->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(CvCertification::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(PortfolioProject::class)->orderBy('sort_order');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getPublicUrlAttribute(): ?string
    {
        return $this->public_slug ? route('portfolio.show', $this->public_slug) : null;
    }

    /**
     * Résumé à afficher : celui saisi par l'étudiant s'il existe,
     * sinon un résumé généré automatiquement à partir de son profil
     * (jamais enregistré en base — recalculé à chaque affichage).
     */
    public function getEffectiveSummaryAttribute(): string
    {
        if (filled($this->summary)) {
            return $this->summary;
        }

        return app(\App\Services\CvSummaryGeneratorService::class)->generate($this);
    }
}

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvProfile.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvProfile.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\app\Services\CvSummaryGeneratorService.php"
$content2 = @'
<?php

namespace App\Services;

use App\Models\CvProfile;

class CvSummaryGeneratorService
{
    /**
     * Génère un résumé professionnel de 3 à 10 lignes à partir des
     * formations, expériences, compétences et langues du profil.
     * N'est jamais enregistré en base — recalculé à chaque affichage
     * tant que l'étudiant n'a pas rempli son propre résumé.
     */
    public function generate(CvProfile $profile): string
    {
        $lines = [];

        // --- Ligne 1 : accroche générale ---
        $latestEducation = $profile->educations->first();

        if ($latestEducation) {
            $degree = $latestEducation->degree ?: 'formation';
            $field = $latestEducation->field_of_study ? " en {$latestEducation->field_of_study}" : '';
            $institution = $latestEducation->institution ? " à {$latestEducation->institution}" : '';

            $lines[] = trim("{$degree}{$field}{$institution}.");
        } else {
            $lines[] = 'Étudiant(e) motivé(e) à la recherche de nouvelles opportunités.';
        }

        // --- Ligne 2 : autres formations, si plusieurs ---
        if ($profile->educations->count() > 1) {
            $others = $profile->educations->skip(1)->pluck('degree')->filter()->implode(', ');
            if ($others) {
                $lines[] = "Parcours complété par : {$others}.";
            }
        }

        // --- Lignes expérience ---
        if ($profile->experiences->isNotEmpty()) {
            $latestExperience = $profile->experiences->first();
            $lines[] = "Expérience en tant que {$latestExperience->position} chez {$latestExperience->company}"
                .($latestExperience->is_current ? ' (poste actuel)' : '').'.';

            if ($profile->experiences->count() > 1) {
                $lines[] = "Totalise {$profile->experiences->count()} expérience(s) professionnelle(s) au total.";
            }
        } else {
            $lines[] = 'Ouvert(e) à une première expérience professionnelle ou un stage.';
        }

        // --- Compétences ---
        if ($profile->skills->isNotEmpty()) {
            $skillNames = $profile->skills->pluck('name')->take(6)->implode(', ');
            $lines[] = "Compétences clés : {$skillNames}.";
        }

        // --- Langues ---
        if ($profile->languages->isNotEmpty()) {
            $languageNames = $profile->languages->pluck('name')->implode(', ');
            $lines[] = "Langues parlées : {$languageNames}.";
        }

        // --- Certifications ---
        if ($profile->certifications->isNotEmpty()) {
            $lines[] = "Titulaire de {$profile->certifications->count()} certification(s) complémentaire(s).";
        }

        // --- Ligne de clôture ---
        $lines[] = 'Rigoureux(se), motivé(e) et désireux(se) de mettre ses compétences au service d\'une équipe.';

        // On garde entre 3 et 10 lignes.
        $lines = array_slice($lines, 0, 10);

        if (count($lines) < 3) {
            $lines[] = 'Profil sérieux, dynamique et adaptable, prêt(e) à relever de nouveaux défis.';
        }

        return implode(' ', $lines);
    }
}

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Services/CvSummaryGeneratorService.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Services/CvSummaryGeneratorService.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\resources\views\admin\cv\index.blade.php"
$content3 = @'
@extends('layouts.admin')

@section('title', 'CV & Portfolios des étudiants')
@section('page-title', 'CV & Portfolios des étudiants')

@section('content')
    <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-4">Étudiant</th>
                    <th class="px-6 py-4">CV rempli</th>
                    <th class="px-6 py-4">Portfolio public</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-6 py-4">
                            {{ $user->name }}
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                        </td>

                        <td class="px-6 py-4">
                            @if ($user->cvProfile)
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Oui</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">Pas encore</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if ($user->cvProfile?->is_public)
                                <a href="{{ $user->cvProfile->public_url }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:underline">
                                    Voir le lien public
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Privé</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <a href="{{ route('admin.cv.show', $user) }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                                Consulter
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            Aucun étudiant trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/cv/index.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/cv/index.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\resources\views\admin\cv\show.blade.php"
$content4 = @'
@extends('layouts.admin')

@section('title', 'CV de ' . $targetUser->name)
@section('page-title', 'CV de ' . $targetUser->name)

@section('content')
    @if (! $profile)
        <section class="rounded-2xl bg-white p-10 text-center shadow-sm">
            <p class="text-gray-500">
                {{ $targetUser->name }} n'a pas encore commencé son CV.
            </p>
        </section>
    @else
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-gray-900">{{ $profile->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $profile->headline }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="h-3 w-32 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full {{ $ats['score'] >= 80 ? 'bg-green-500' : ($ats['score'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                            style="width: {{ $ats['score'] }}%"
                        ></div>
                    </div>
                    <span class="font-bold {{ $ats['score'] >= 80 ? 'text-green-600' : ($ats['score'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ $ats['score'] }}/100
                    </span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.cv.download.cv', $targetUser) }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Voir / Imprimer le CV
                </a>
                <a href="{{ route('admin.cv.download.ats', $targetUser) }}" target="_blank" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                    Version ATS
                </a>
                @if ($profile->is_public)
                    <a href="{{ $profile->public_url }}" target="_blank" class="rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-700">
                        Portfolio public
                    </a>
                @endif
            </div>
        </section>

        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Formation ({{ $profile->educations->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @forelse ($profile->educations as $edu)
                        <li>{{ $edu->degree }} — {{ $edu->institution }}</li>
                    @empty
                        <li class="text-gray-400">Aucune</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Expérience ({{ $profile->experiences->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @forelse ($profile->experiences as $exp)
                        <li>{{ $exp->position }} — {{ $exp->company }}</li>
                    @empty
                        <li class="text-gray-400">Aucune</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Compétences ({{ $profile->skills->count() }})</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse ($profile->skills as $skill)
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $skill->name }}</span>
                    @empty
                        <span class="text-sm text-gray-400">Aucune</span>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Projets portfolio ({{ $profile->projects->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @forelse ($profile->projects as $project)
                        <li>{{ $project->title }}</li>
                    @empty
                        <li class="text-gray-400">Aucun</li>
                    @endforelse
                </ul>
            </section>
        </div>
    @endif
@endsection

'@
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/cv/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/cv/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\resources\views\layouts\admin.blade.php"
$content5 = @'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Administration') — SmartEco Academy
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-slate-100 text-slate-900"
>
    {{-- Arrière-plan mobile --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu latéral administrateur --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900 text-white shadow-xl transition-transform duration-300 lg:translate-x-0 print:hidden"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center border-b border-slate-800 px-6">
            <a
                href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 font-bold text-white"
                >
                    SE
                </div>

                <div>
                    <p class="font-bold text-white">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-slate-400">
                        Administration
                    </p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Vue générale
            </p>

            <div class="space-y-1">
                {{-- Tableau de bord --}}
                <a
                    href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('supervisor.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard', 'supervisor.dashboard')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-12h8V3h-8v6z"
                        />
                    </svg>

                    Statistiques
                </a>
            </div>

            @if (auth()->user()->hasRole('admin'))
                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Centre de formation
                </p>

                <div class="space-y-1">
                    {{-- Module Centre --}}
                    <a
                        href="{{ route('admin.centre.index') }}"
                        class="{{ request()->routeIs('admin.centre.index')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118 14.5c0 2.21-2.686 4-6 4s-6-1.79-6-4c0-1.381.484-2.651 1.29-3.672L12 14z"
                            />
                        </svg>

                        Niveaux, filières & cours
                    </a>

                    {{-- Générateur de cursus --}}
                    <a
                        href="{{ route('admin.centre.curriculum.index') }}"
                        class="{{ request()->routeIs('admin.centre.curriculum.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>

                        Générateur de cursus
                    </a>

                    {{-- Packs (semestres / modules) --}}
                    <a
                        href="{{ route('admin.centre.packs.index') }}"
                        class="{{ request()->routeIs('admin.centre.packs.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                            />
                        </svg>

                        Packs (semestres / modules)
                    </a>

                    {{-- Bibliothèque de ressources --}}
                    <a
                        href="{{ route('admin.centre.library.index') }}"
                        class="{{ request()->routeIs('admin.centre.library.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>

                        Bibliothèque de ressources
                    </a>
                </div>
            @endif

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Inscriptions & validations
            </p>

            <div class="space-y-1">
                {{-- Inscriptions & paiements --}}
                <a
                    href="{{ route('admin.centre.pack-enrollments.index') }}"
                    class="{{ request()->routeIs('admin.centre.pack-enrollments.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>

                    Inscriptions & paiements aux packs
                </a>

                <a
                    href="{{ route('admin.cv.index') }}"
                    class="{{ request()->routeIs('admin.cv.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>

                    CV & Portfolios des étudiants
                </a>

                <a
                    href="{{ route('admin.centre.reports.index') }}"
                    class="{{ request()->routeIs('admin.centre.reports.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 17v-6m4 6V7m4 10v-3M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"
                        />
                    </svg>

                    État financier (imprimable)
                </a>

                {{-- Module Inscriptions (dossiers d'admission) --}}
                <a
                    href="{{ route('admin.registrations.index') }}"
                    class="{{ request()->routeIs('admin.registrations.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6M9 8h6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"
                        />
                    </svg>

                    Dossiers d’admission
                </a>

                {{-- Inscriptions & paiements formations --}}
                <a
                    href="{{ route('admin.trainings.enrollments.index') }}"
                    class="{{ request()->routeIs('admin.trainings.enrollments.*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg
                        class="mr-3 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                    </svg>

                    Inscriptions & paiements formations
                </a>
            </div>

            @if (auth()->user()->hasRole('admin'))
                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Autres modules
                </p>

                <div class="space-y-1">
                    {{-- Module Formations --}}
                    <a
                        href="{{ route('admin.trainings.index') }}"
                        class="{{ request()->routeIs('admin.trainings.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-9v6a6 6 0 0012 0v-6"
                            />
                        </svg>

                        Formations courtes
                    </a>
                </div>

                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Comptes
                </p>

                <div class="space-y-1">
                    {{-- Comptes Prof / Superviseur / Admin --}}
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*')
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}
                            flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"
                            />
                        </svg>

                        Utilisateurs & rôles
                    </a>
                </div>
            @endif
        </nav>

        {{-- Profil administrateur --}}
        <div class="border-t border-slate-800 p-4">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-xl bg-slate-800 p-3 transition hover:bg-slate-700"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500 font-semibold text-white"
                >
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-slate-400">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Zone principale --}}
    <div class="min-h-screen lg:pl-72 print:pl-0">
        {{-- Barre supérieure --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6 print:hidden"
        >
            <div class="flex items-center gap-4">
                {{-- Bouton mobile --}}
                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <span class="sr-only">
                        Ouvrir le menu
                    </span>

                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <div>
                    <h1 class="font-semibold text-gray-900">
                        @yield('page-title', 'Administration')
                    </h1>

                    <p class="hidden text-xs text-gray-500 sm:block">
                        Gestion générale de SmartEco Academy
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Accueil public --}}
                <a
                    href="{{ route('home') }}"
                    class="hidden rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 sm:block"
                >
                    Voir le site
                </a>

                {{-- Profil --}}
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Profil
                </a>

                {{-- Déconnexion --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700 sm:px-4"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Messages de succès --}}
        @if (session('success'))
            <div
                class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6"
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- Messages d’erreur --}}
        @if (session('error'))
            <div
                class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 sm:mx-6"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Contenu --}}
        <main class="p-4 sm:p-6 print:p-0">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
'@
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/admin.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/admin.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\layouts\supervisor.blade.php"
$content6 = @'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Espace superviseur') — SmartEco Academy</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100 text-gray-900">
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    >
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
            <a href="{{ route('supervisor.dashboard') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                    SE
                </div>

                <div>
                    <p class="font-bold text-gray-900">SmartEco Academy</p>
                    <p class="text-xs text-gray-500">Espace superviseur</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
                <a
                    href="{{ route('supervisor.dashboard') }}"
                    class="{{ request()->routeIs('supervisor.dashboard')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A8.968 8.968 0 003 6.253v13A8.968 8.968 0 017.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5A8.968 8.968 0 0121 6.253v13A8.968 8.968 0 0016.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>

                    Tableau de bord
                </a>

                <a
                    href="{{ route('admin.centre.pack-enrollments.index') }}"
                    class="{{ request()->routeIs('admin.centre.pack-enrollments.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m-6-4h18M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>

                    Inscriptions & paiements
                </a>

                <a
                    href="{{ route('admin.centre.reports.index') }}"
                    class="{{ request()->routeIs('admin.centre.reports.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"/>
                    </svg>

                    État financier (imprimable)
                </a>

                <a
                    href="{{ route('admin.registrations.index') }}"
                    class="{{ request()->routeIs('admin.registrations.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/>
                    </svg>

                    Dossiers d’admission
                </a>

                <a
                    href="{{ route('admin.trainings.enrollments.index') }}"
                    class="{{ request()->routeIs('admin.trainings.enrollments.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-9v6a6 6 0 0012 0v-6"/>
                    </svg>

                    Inscriptions & paiements formations
                </a>

                <a
                    href="{{ route('admin.cv.index') }}"
                    class="{{ request()->routeIs('admin.cv.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
                        flex items-center rounded-lg px-4 py-3 text-sm font-medium transition"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>

                    CV & Portfolios des étudiants
                </a>
            </div>
        </nav>

        <div class="border-t border-gray-200 p-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg p-3 transition hover:bg-gray-100">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
            </a>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6">
            <div class="flex items-center gap-4">
                <button type="button" class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Ouvrir le menu</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div>
                    <h1 class="font-semibold text-gray-900">@yield('page-title', 'Tableau de bord')</h1>
                    <p class="hidden text-xs text-gray-500 sm:block">Espace superviseur</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                    Profil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 sm:px-4">
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 p-4 sm:mx-6">
                <ul class="list-disc pl-5 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>

'@
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/supervisor.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/supervisor.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\resources\views\portfolio\show.blade.php"
$content7 = @'
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

        @if (filled($profile->effective_summary))
            <section class="rounded-2xl bg-white p-8 shadow-sm">
                <p class="text-center text-lg leading-8 text-gray-700">{{ $profile->effective_summary }}</p>
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

'@
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/portfolio/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/portfolio/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\resources\views\student\cv\_section-languages.blade.php"
$content8 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Langues</h2>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($profile->languages as $language)
            <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                <span class="font-medium text-indigo-700">{{ $language->name }}</span>
                <span class="text-xs text-indigo-400">({{ $language->level_label }})</span>
                <form method="POST" action="{{ route('student.cv.languages.destroy', $language) }}">
                    @csrf @method('DELETE')
                    <button class="text-indigo-400 hover:text-red-600">×</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.languages.store') }}" class="mt-4 flex flex-wrap gap-2">
        @csrf
        <select name="name" class="rounded-lg border-gray-300" required>
            <option value="">Choisir une langue</option>
            <option value="Arabe">Arabe</option>
            <option value="Français">Français</option>
            <option value="Anglais">Anglais</option>
            <option value="Espagnol">Espagnol</option>
            <option value="Allemand">Allemand</option>
            <option value="Autre">Autre</option>
        </select>
        <select name="level" class="rounded-lg border-gray-300">
            <option value="debutant">Débutant</option>
            <option value="intermediaire" selected>Intermédiaire</option>
            <option value="courant">Courant</option>
            <option value="bilingue">Bilingue</option>
            <option value="natif">Langue maternelle</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter</button>
    </form>
</section>

'@
$dir8 = Split-Path $path8 -Parent
if (-not (Test-Path $dir8)) { New-Item -ItemType Directory -Path $dir8 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-languages.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-languages.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path9 = "C:\laragon\www\SEA\resources\views\student\cv\_section-skills.blade.php"
$content9 = @'
<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Compétences</h2>

    <div class="mt-4 flex flex-wrap gap-2">
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

    <form method="POST" action="{{ route('student.cv.skills.store') }}" class="mt-4 flex flex-wrap gap-2">
        @csrf
        <input name="name" list="skills-suggestions" placeholder="Ex : Excel, Comptabilité..." class="rounded-lg border-gray-300" required>

        <datalist id="skills-suggestions">
            {{-- Bureautique / gestion --}}
            <option value="Excel avancé">
            <option value="Word">
            <option value="PowerPoint">
            <option value="Sage Comptabilité">
            <option value="SAP">
            <option value="Comptabilité générale">
            <option value="Comptabilité analytique">
            <option value="Fiscalité">
            <option value="Contrôle de gestion">
            <option value="Analyse financière">
            <option value="Gestion budgétaire">
            <option value="Audit">
            {{-- Commerce / marketing --}}
            <option value="Marketing digital">
            <option value="Négociation commerciale">
            <option value="Relation client">
            <option value="Étude de marché">
            <option value="Réseaux sociaux">
            <option value="SEO / référencement">
            {{-- RH --}}
            <option value="Gestion de la paie">
            <option value="Recrutement">
            <option value="Droit du travail">
            <option value="Gestion des conflits">
            {{-- Économétrie / data --}}
            <option value="Statistiques">
            <option value="Analyse de données">
            <option value="Python">
            <option value="R (langage statistique)">
            <option value="SPSS">
            <option value="Économétrie appliquée">
            {{-- Transversal --}}
            <option value="Gestion de projet">
            <option value="Travail d'équipe">
            <option value="Communication">
            <option value="Rédaction professionnelle">
            <option value="Intelligence artificielle (bases)">
            <option value="Anglais des affaires">
        </datalist>

        <select name="level" class="rounded-lg border-gray-300">
            <option value="debutant">Débutant</option>
            <option value="intermediaire" selected>Intermédiaire</option>
            <option value="avance">Avancé</option>
            <option value="expert">Expert</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter</button>
    </form>
</section>

'@
$dir9 = Split-Path $path9 -Parent
if (-not (Test-Path $dir9)) { New-Item -ItemType Directory -Path $dir9 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path9, $content9, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-skills.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-skills.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path10 = "C:\laragon\www\SEA\resources\views\student\cv\edit.blade.php"
$content10 = @'
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
    <section class="rounded-2xl bg-white p-6 shadow-sm">
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
    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm" x-data="{ open: true }">
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

'@
$dir10 = Split-Path $path10 -Parent
if (-not (Test-Path $dir10)) { New-Item -ItemType Directory -Path $dir10 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path10, $content10, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/edit.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/edit.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path11 = "C:\laragon\www\SEA\resources\views\student\cv\templates\ats.blade.php"
$content11 = @'
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
            <p>{{ $profile->skills->pluck('name')->implode(', ') }}</p>
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
$dir11 = Split-Path $path11 -Parent
if (-not (Test-Path $dir11)) { New-Item -ItemType Directory -Path $dir11 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path11, $content11, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/ats.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/ats.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path12 = "C:\laragon\www\SEA\resources\views\student\cv\templates\classique.blade.php"
$content12 = @'
@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle classique')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-10 text-sm print:border-0 print:p-0 print:shadow-none">

        <div class="flex items-center gap-6 border-b-2 border-gray-800 pb-6">
            @if ($profile->photo_url)
                <img src="{{ $profile->photo_url }}" class="h-24 w-24 rounded-full object-cover">
            @endif

            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $profile->full_name }}</h1>
                @if ($profile->headline)
                    <p class="mt-1 text-base text-gray-600">{{ $profile->headline }}</p>
                @endif

                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                    @if ($profile->email) <span>{{ $profile->email }}</span> @endif
                    @if ($profile->phone) <span>{{ $profile->phone }}</span> @endif
                    @if ($profile->address) <span>{{ $profile->address }}</span> @endif
                    @if ($profile->linkedin_url) <span>{{ $profile->linkedin_url }}</span> @endif
                </div>
            </div>
        </div>

        @if (filled($profile->effective_summary))
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Profil</h2>
                <p class="mt-2 leading-6 text-gray-700">{{ $profile->effective_summary }}</p>
            </div>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Expérience professionnelle</h2>
                <div class="mt-2 space-y-4">
                    @foreach ($profile->experiences as $exp)
                        <div>
                            <div class="flex items-baseline justify-between">
                                <p class="font-semibold text-gray-900">{{ $exp->position }} — {{ $exp->company }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}
                                </p>
                            </div>
                            @if ($exp->location)
                                <p class="text-xs text-gray-400">{{ $exp->location }}</p>
                            @endif
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
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Formation</h2>
                <div class="mt-2 space-y-3">
                    @foreach ($profile->educations as $edu)
                        <div>
                            <div class="flex items-baseline justify-between">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }} — {{ $edu->institution }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}
                                </p>
                            </div>
                            @if ($edu->field_of_study)
                                <p class="text-xs text-gray-400">{{ $edu->field_of_study }}</p>
                            @endif
                            @if ($edu->description)
                                <p class="mt-1 text-gray-700">{{ $edu->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 grid grid-cols-2 gap-6">
            @if ($profile->skills->isNotEmpty())
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Compétences</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->skills as $skill)
                            <li>{{ $skill->name }} — <span class="text-xs text-gray-400">{{ $skill->level_label }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($profile->languages->isNotEmpty())
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Langues</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — <span class="text-xs text-gray-400">{{ $lang->level_label }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if ($profile->certifications->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Certifications</h2>
                <ul class="mt-2 space-y-1 text-gray-700">
                    @foreach ($profile->certifications as $cert)
                        <li>
                            {{ $cert->name }}
                            @if ($cert->issuer) — {{ $cert->issuer }} @endif
                            @if ($cert->date_obtained) <span class="text-xs text-gray-400">({{ $cert->date_obtained->format('Y') }})</span> @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection

'@
$dir12 = Split-Path $path12 -Parent
if (-not (Test-Path $dir12)) { New-Item -ItemType Directory -Path $dir12 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path12, $content12, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/classique.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/classique.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path13 = "C:\laragon\www\SEA\resources\views\student\cv\templates\moderne.blade.php"
$content13 = @'
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
                    <div class="mt-2 space-y-2">
                        @foreach ($profile->skills as $skill)
                            <div>
                                <p class="text-xs text-indigo-100">{{ $skill->name }}</p>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                                    <div class="h-full bg-white" style="width: {{ $skill->level_percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->languages->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-white">Langues</h2>
                    <ul class="mt-2 space-y-1 text-xs text-indigo-100">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — {{ $lang->level_label }}</li>
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
$dir13 = Split-Path $path13 -Parent
if (-not (Test-Path $dir13)) { New-Item -ItemType Directory -Path $dir13 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path13, $content13, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/moderne.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/moderne.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path14 = "C:\laragon\www\SEA\routes\cv.php"
$content14 = @'
<?php

use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\Student\CvController;
use Illuminate\Support\Facades\Route;

// --- Espace étudiant : CV builder ---
Route::middleware(['auth', 'verified'])
    ->prefix('cv')
    ->name('student.cv.')
    ->group(function () {
        Route::get('/', [CvController::class, 'edit'])->name('edit');
        Route::patch('/profile', [CvController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/public', [CvController::class, 'togglePublic'])->name('public.toggle');

        Route::post('/educations', [CvController::class, 'storeEducation'])->name('educations.store');
        Route::patch('/educations/{education}', [CvController::class, 'updateEducation'])->name('educations.update');
        Route::delete('/educations/{education}', [CvController::class, 'destroyEducation'])->name('educations.destroy');

        Route::post('/experiences', [CvController::class, 'storeExperience'])->name('experiences.store');
        Route::patch('/experiences/{experience}', [CvController::class, 'updateExperience'])->name('experiences.update');
        Route::delete('/experiences/{experience}', [CvController::class, 'destroyExperience'])->name('experiences.destroy');

        Route::post('/skills', [CvController::class, 'storeSkill'])->name('skills.store');
        Route::delete('/skills/{skill}', [CvController::class, 'destroySkill'])->name('skills.destroy');

        Route::post('/languages', [CvController::class, 'storeLanguage'])->name('languages.store');
        Route::delete('/languages/{language}', [CvController::class, 'destroyLanguage'])->name('languages.destroy');

        Route::post('/certifications', [CvController::class, 'storeCertification'])->name('certifications.store');
        Route::delete('/certifications/{certification}', [CvController::class, 'destroyCertification'])->name('certifications.destroy');

        Route::post('/projects', [CvController::class, 'storeProject'])->name('projects.store');
        Route::patch('/projects/{project}', [CvController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [CvController::class, 'destroyProject'])->name('projects.destroy');

        Route::get('/download/cv', [CvController::class, 'showCv'])->name('download.cv');
        Route::get('/download/ats', [CvController::class, 'showAts'])->name('download.ats');
    });

// --- Portfolio public (sans authentification) ---
Route::get('/portfolio/{slug}', [PublicPortfolioController::class, 'show'])->name('portfolio.show');

// --- Espace admin : consultation des CV des étudiants ---
Route::middleware(['auth', 'verified', 'role:admin,superviseur'])
    ->prefix('admin/cv')
    ->name('admin.cv.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CvController::class, 'index'])->name('index');
        Route::get('/{user}', [\App\Http\Controllers\Admin\CvController::class, 'show'])->name('show');
        Route::get('/{user}/cv', [\App\Http\Controllers\Admin\CvController::class, 'showCv'])->name('download.cv');
        Route::get('/{user}/ats', [\App\Http\Controllers\Admin\CvController::class, 'showAts'])->name('download.ats');
    });

'@
$dir14 = Split-Path $path14 -Parent
if (-not (Test-Path $dir14)) { New-Item -ItemType Directory -Path $dir14 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path14, $content14, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/cv.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/cv.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
