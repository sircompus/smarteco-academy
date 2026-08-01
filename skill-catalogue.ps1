$path0 = "C:\laragon\www\SEA\app\Http\Controllers\Admin\SkillSuggestionController.php"
$content0 = @'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkillSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillSuggestionController extends Controller
{
    public function index(): View
    {
        return view('admin.cv.skills.index', [
            'skills' => SkillSuggestion::query()
                ->orderBy('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($skill) => $skill->category ?: 'Sans catégorie'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skill_suggestions,name'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        SkillSuggestion::create($data + [
            'is_active' => true,
            'sort_order' => SkillSuggestion::count(),
        ]);

        return back()->with('success', 'Compétence ajoutée au catalogue.');
    }

    public function update(Request $request, SkillSuggestion $skill): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skill_suggestions,name,'.$skill->id],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);

        $skill->update($data);

        return back()->with('success', 'Compétence mise à jour.');
    }

    public function destroy(SkillSuggestion $skill): RedirectResponse
    {
        $skill->delete();

        return back()->with('success', 'Compétence retirée du catalogue.');
    }
}

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/SkillSuggestionController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/SkillSuggestionController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Models\SkillSuggestion.php"
$content1 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/SkillSuggestion.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/SkillSuggestion.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\database\migrations\2026_08_01_160000_create_skill_suggestions_table.php"
$content2 = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable(); // ex: Gestion, Commerce, RH, IT...
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_suggestions');
    }
};

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/migrations/2026_08_01_160000_create_skill_suggestions_table.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/migrations/2026_08_01_160000_create_skill_suggestions_table.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\database\seeders\DatabaseSeeder.php"
$content3 = @'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ModuleSeeder::class,
            AcademicSeeder::class,
            SemesterSubjectSeeder::class,
            SkillSuggestionSeeder::class,
        ]);
    }
}
'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/seeders/DatabaseSeeder.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/seeders/DatabaseSeeder.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\database\seeders\SkillSuggestionSeeder.php"
$content4 = @'
<?php

namespace Database\Seeders;

use App\Models\SkillSuggestion;
use Illuminate\Database\Seeder;

class SkillSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'Gestion' => [
                'Excel avancé', 'Word', 'PowerPoint', 'Sage Comptabilité', 'SAP',
                'Comptabilité générale', 'Comptabilité analytique', 'Fiscalité',
                'Contrôle de gestion', 'Analyse financière', 'Gestion budgétaire', 'Audit',
            ],
            'Commerce' => [
                'Marketing digital', 'Négociation commerciale', 'Relation client',
                'Étude de marché', 'Réseaux sociaux', 'SEO / référencement',
            ],
            'Ressources humaines' => [
                'Gestion de la paie', 'Recrutement', 'Droit du travail', 'Gestion des conflits',
            ],
            'Économétrie / Data' => [
                'Statistiques', 'Analyse de données', 'Python', 'R (langage statistique)',
                'SPSS', 'Économétrie appliquée',
            ],
            'Transversal' => [
                'Gestion de projet', "Travail d'équipe", 'Communication',
                'Rédaction professionnelle', 'Intelligence artificielle (bases)', 'Anglais des affaires',
            ],
        ];

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
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/seeders/SkillSuggestionSeeder.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/seeders/SkillSuggestionSeeder.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\resources\views\admin\cv\skills\index.blade.php"
$content5 = @'
@extends('layouts.admin')

@section('title', 'Catalogue de compétences')
@section('page-title', 'Catalogue de compétences (CV)')

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

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Ajouter une compétence au catalogue</h2>
        <p class="mt-1 text-sm text-gray-500">
            Ces compétences apparaissent en cases à cocher dans le CV builder des étudiants.
        </p>

        <form method="POST" action="{{ route('admin.cv.skills.store') }}" class="mt-4 flex flex-wrap gap-3">
            @csrf
            <input name="name" placeholder="Nom de la compétence" class="rounded-lg border-gray-300" required>
            <input name="category" placeholder="Catégorie (ex: Gestion, IT...)" class="rounded-lg border-gray-300">
            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Ajouter
            </button>
        </form>
    </section>

    <div class="mt-8 space-y-6">
        @foreach ($skills as $category => $categorySkills)
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">{{ $category }}</h3>

                <div class="mt-4 space-y-2">
                    @foreach ($categorySkills as $skill)
                        <form method="POST" action="{{ route('admin.cv.skills.update', $skill) }}" class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-100 p-3">
                            @csrf
                            @method('PATCH')

                            <input name="name" value="{{ $skill->name }}" class="rounded-lg border-gray-300 text-sm">
                            <input name="category" value="{{ $skill->category }}" placeholder="Catégorie" class="rounded-lg border-gray-300 text-sm">

                            <select name="is_active" class="rounded-lg border-gray-300 text-sm">
                                <option value="1" @selected($skill->is_active)>Active</option>
                                <option value="0" @selected(! $skill->is_active)>Désactivée</option>
                            </select>

                            <button class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700">
                                Enregistrer
                            </button>

                            <button
                                type="button"
                                onclick="if(confirm('Supprimer cette compétence du catalogue ?')) document.getElementById('del-skill-{{ $skill->id }}').submit();"
                                class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600"
                            >
                                Supprimer
                            </button>
                        </form>

                        <form id="del-skill-{{ $skill->id }}" method="POST" action="{{ route('admin.cv.skills.destroy', $skill) }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection

'@
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/cv/skills/index.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/cv/skills/index.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\layouts\admin.blade.php"
$content6 = @'
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
                    href="{{ route('admin.cv.skills.index') }}"
                    class="{{ request()->routeIs('admin.cv.skills.*')
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
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    Catalogue de compétences (CV)
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
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/admin.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/admin.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\resources\views\student\cv\_section-skills.blade.php"
$content7 = @'
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

        <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-3 md:grid-cols-4">
            @php
                $suggestedSkills = \App\Models\SkillSuggestion::where('is_active', true)
                    ->orderBy('category')
                    ->orderBy('sort_order')
                    ->pluck('name');
                $alreadyHave = $profile->skills->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
            @endphp

            @foreach ($suggestedSkills as $suggestedSkill)
                @unless (in_array(mb_strtolower($suggestedSkill), $alreadyHave, true))
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="skills[]" value="{{ $suggestedSkill }}" class="rounded border-gray-300">
                        {{ $suggestedSkill }}
                    </label>
                @endunless
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
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-skills.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-skills.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\routes\cv.php"
$content8 = @'
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

        Route::get('/catalogue/skills', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'index'])->name('skills.index');
        Route::post('/catalogue/skills', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'store'])->name('skills.store');
        Route::patch('/catalogue/skills/{skill}', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'update'])->name('skills.update');
        Route::delete('/catalogue/skills/{skill}', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'destroy'])->name('skills.destroy');
    });

'@
$dir8 = Split-Path $path8 -Parent
if (-not (Test-Path $dir8)) { New-Item -ItemType Directory -Path $dir8 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/cv.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/cv.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
