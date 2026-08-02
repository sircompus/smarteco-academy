$path0 = "C:\laragon\www\SEA\app\Http\Controllers\Admin\CvBuilderController.php"
$content0 = @'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CvCertification;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvProfile;
use App\Models\CvSkill;
use App\Models\PortfolioProject;
use App\Models\User;
use App\Services\AtsScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CvBuilderController extends Controller
{
    public function edit(User $user): View
    {
        $profile = $this->profileFor($user);

        $profile->load([
            'educations', 'experiences', 'skills',
            'languages', 'certifications', 'projects',
        ]);

        $ats = app(AtsScoreService::class)->evaluate($profile);

        return view('student.cv.edit', [
            'profile' => $profile,
            'ats' => $ats,
            'targetUser' => $user,
            'routePrefix' => 'admin.cv.builder',
            'layout' => 'layouts.admin',
        ]);
    }

    private function profileFor(User $user): CvProfile
    {
        return CvProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'uuid' => (string) Str::uuid(),
                'full_name' => $user->name,
                'email' => $user->email,
            ]
        );
    }

    private function toSection(User $user, string $anchor, string $message): RedirectResponse
    {
        return redirect(route('admin.cv.builder.edit', $user).'#'.$anchor)->with('success', $message);
    }

    // --- Profil principal ---

    public function updateProfile(Request $request, User $user): RedirectResponse
    {
        $profile = $this->profileFor($user);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'headline' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:200'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'cv_template' => ['required', 'in:classique,moderne'],
            'portfolio_template' => ['required', 'in:elegant'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('cv-photos', 'public');
        }

        $profile->update($data);

        return $this->toSection($user, 'profile-info', 'Profil mis à jour.');
    }

    public function togglePublic(User $user): RedirectResponse
    {
        $profile = $this->profileFor($user);

        if (! $profile->is_public && ! $profile->public_slug) {
            $profile->public_slug = Str::slug($profile->full_name ?: 'portfolio').'-'.Str::lower(Str::random(6));
        }

        $profile->is_public = ! $profile->is_public;
        $profile->save();

        return $this->toSection(
            $user,
            'exports',
            $profile->is_public ? 'Portfolio rendu public : '.$profile->public_url : 'Portfolio rendu privé.'
        );
    }

    // --- Formation ---

    public function storeEducation(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateEducation($request);
        $profile = $this->profileFor($user);

        $profile->educations()->create($data + ['sort_order' => $profile->educations()->count()]);

        return $this->toSection($user, 'educations', 'Formation ajoutée.');
    }

    public function updateEducation(Request $request, CvEducation $education): RedirectResponse
    {
        $education->update($this->validateEducation($request));

        return $this->toSection($education->profile->user, 'educations', 'Formation mise à jour.');
    }

    public function destroyEducation(CvEducation $education): RedirectResponse
    {
        $user = $education->profile->user;
        $education->delete();

        return $this->toSection($user, 'educations', 'Formation supprimée.');
    }

    private function validateEducation(Request $request): array
    {
        return $request->validate([
            'institution' => ['required', 'string', 'max:150'],
            'degree' => ['nullable', 'string', 'max:150'],
            'field_of_study' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    // --- Expérience ---

    public function storeExperience(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateExperience($request);
        $profile = $this->profileFor($user);

        $profile->experiences()->create($data + ['sort_order' => $profile->experiences()->count()]);

        return $this->toSection($user, 'experiences', 'Expérience ajoutée.');
    }

    public function updateExperience(Request $request, CvExperience $experience): RedirectResponse
    {
        $experience->update($this->validateExperience($request));

        return $this->toSection($experience->profile->user, 'experiences', 'Expérience mise à jour.');
    }

    public function destroyExperience(CvExperience $experience): RedirectResponse
    {
        $user = $experience->profile->user;
        $experience->delete();

        return $this->toSection($user, 'experiences', 'Expérience supprimée.');
    }

    private function validateExperience(Request $request): array
    {
        return $request->validate([
            'company' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    // --- Compétences ---

    public function storeSkill(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:100'],
            'level' => ['required', 'in:debutant,intermediaire,avance,expert'],
            'custom_skill' => ['nullable', 'string', 'max:100'],
        ]);

        $profile = $this->profileFor($user);
        $existingNames = $profile->skills()->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();

        $namesToAdd = collect($data['skills'] ?? []);

        if (! empty($data['custom_skill'])) {
            $namesToAdd->push($data['custom_skill']);
        }

        $count = 0;

        foreach ($namesToAdd->unique() as $name) {
            if (in_array(mb_strtolower($name), $existingNames, true)) {
                continue;
            }

            $profile->skills()->create([
                'name' => $name,
                'level' => $data['level'],
                'sort_order' => $profile->skills()->count(),
            ]);

            $count++;
        }

        return $this->toSection($user, 'skills', "{$count} compétence(s) ajoutée(s).");
    }

    public function destroySkill(CvSkill $skill): RedirectResponse
    {
        $user = $skill->profile->user;
        $skill->delete();

        return $this->toSection($user, 'skills', 'Compétence supprimée.');
    }

    // --- Langues ---

    public function storeLanguage(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'languages' => ['nullable', 'array'],
            'languages.*.checked' => ['nullable', 'boolean'],
            'languages.*.level' => ['nullable', 'in:debutant,intermediaire,courant,bilingue,natif'],
            'custom_language' => ['nullable', 'string', 'max:100'],
            'custom_language_level' => ['nullable', 'in:debutant,intermediaire,courant,bilingue,natif'],
        ]);

        $profile = $this->profileFor($user);
        $existingNames = $profile->languages()->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();

        $count = 0;

        foreach ($data['languages'] ?? [] as $name => $entry) {
            if (empty($entry['checked']) || in_array(mb_strtolower($name), $existingNames, true)) {
                continue;
            }

            $profile->languages()->create([
                'name' => $name,
                'level' => $entry['level'] ?? 'intermediaire',
                'sort_order' => $profile->languages()->count(),
            ]);

            $count++;
        }

        if (! empty($data['custom_language']) && ! in_array(mb_strtolower($data['custom_language']), $existingNames, true)) {
            $profile->languages()->create([
                'name' => $data['custom_language'],
                'level' => $data['custom_language_level'] ?? 'intermediaire',
                'sort_order' => $profile->languages()->count(),
            ]);

            $count++;
        }

        return $this->toSection($user, 'languages', "{$count} langue(s) ajoutée(s).");
    }

    public function destroyLanguage(CvLanguage $language): RedirectResponse
    {
        $user = $language->profile->user;
        $language->delete();

        return $this->toSection($user, 'languages', 'Langue supprimée.');
    }

    // --- Certifications ---

    public function storeCertification(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'issuer' => ['nullable', 'string', 'max:150'],
            'date_obtained' => ['nullable', 'date'],
            'credential_url' => ['nullable', 'url', 'max:255'],
        ]);

        $profile = $this->profileFor($user);
        $profile->certifications()->create($data + ['sort_order' => $profile->certifications()->count()]);

        return $this->toSection($user, 'certifications', 'Certification ajoutée.');
    }

    public function destroyCertification(CvCertification $certification): RedirectResponse
    {
        $user = $certification->profile->user;
        $certification->delete();

        return $this->toSection($user, 'certifications', 'Certification supprimée.');
    }

    // --- Projets ---

    public function storeProject(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('portfolio-projects', 'public');
        }

        $profile = $this->profileFor($user);
        $profile->projects()->create($data + ['sort_order' => $profile->projects()->count()]);

        return $this->toSection($user, 'projects', 'Projet ajouté.');
    }

    public function updateProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('portfolio-projects', 'public');
        }

        $project->update($data);

        return $this->toSection($project->profile->user, 'projects', 'Projet mis à jour.');
    }

    public function destroyProject(PortfolioProject $project): RedirectResponse
    {
        $user = $project->profile->user;
        $project->delete();

        return $this->toSection($user, 'projects', 'Projet supprimé.');
    }
}

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/CvBuilderController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/CvBuilderController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\resources\views\admin\cv\show.blade.php"
$content1 = @'
@extends('layouts.admin')

@section('title', 'CV de ' . $targetUser->name)
@section('page-title', 'CV de ' . $targetUser->name)

@section('content')
    @if (! $profile)
        <section class="rounded-2xl bg-white p-10 text-center shadow-sm">
            <p class="text-gray-500">
                {{ $targetUser->name }} n'a pas encore commencé son CV.
            </p>

            <a href="{{ route('admin.cv.builder.edit', $targetUser) }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Créer son CV à sa place
            </a>
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
                <a href="{{ route('admin.cv.builder.edit', $targetUser) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white">
                    Modifier à sa place
                </a>
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
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/admin/cv/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/admin/cv/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\resources\views\student\cv\_section-certifications.blade.php"
$content2 = @'
<section id="certifications" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Certifications</h2>

    <div class="mt-4 space-y-2">
        @foreach ($profile->certifications as $certification)
            <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3">
                <div>
                    <p class="text-sm font-medium">{{ $certification->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $certification->issuer }}
                        @if ($certification->date_obtained)
                            · {{ $certification->date_obtained->format('m/Y') }}
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route(\"{$routePrefix}.certifications.destroy\", $certification) }}">
                    @csrf @method('DELETE')
                    <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">Supprimer</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.certifications.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="name" placeholder="Nom de la certification" class="rounded-lg border-gray-300" required>
        <input name="issuer" placeholder="Organisme" class="rounded-lg border-gray-300">
        <input type="date" name="date_obtained" class="rounded-lg border-gray-300">
        <input name="credential_url" placeholder="Lien (optionnel)" class="rounded-lg border-gray-300">
        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">+ Ajouter</button>
    </form>
</section>

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-certifications.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-certifications.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\resources\views\student\cv\_section-educations.blade.php"
$content3 = @'
<section id="educations" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Formation</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->educations as $education)
            <form
                method="POST"
                action="{{ route(\"{$routePrefix}.educations.update\", $education) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="institution" value="{{ $education->institution }}" placeholder="Établissement" class="rounded-lg border-gray-300" required>
                <input name="degree" value="{{ $education->degree }}" placeholder="Diplôme" class="rounded-lg border-gray-300">
                <input name="field_of_study" value="{{ $education->field_of_study }}" placeholder="Domaine" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $education->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $education->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2">{{ $education->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($education->is_current)>
                    En cours
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette formation ?')) document.getElementById('del-edu-{{ $education->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-edu-{{ $education->id }}" method="POST" action="{{ route(\"{$routePrefix}.educations.destroy\", $education) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.educations.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="institution" placeholder="Établissement" class="rounded-lg border-gray-300" required>
        <input name="degree" placeholder="Diplôme" class="rounded-lg border-gray-300">
        <input name="field_of_study" placeholder="Domaine" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            En cours
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une formation
        </button>
    </form>
</section>

'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-educations.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-educations.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\resources\views\student\cv\_section-experiences.blade.php"
$content4 = @'
<section id="experiences" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Expérience professionnelle</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->experiences as $experience)
            <form
                method="POST"
                action="{{ route(\"{$routePrefix}.experiences.update\", $experience) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="company" value="{{ $experience->company }}" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
                <input name="position" value="{{ $experience->position }}" placeholder="Poste" class="rounded-lg border-gray-300" required>
                <input name="location" value="{{ $experience->location }}" placeholder="Lieu" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $experience->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $experience->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2">{{ $experience->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($experience->is_current)>
                    Poste actuel
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette expérience ?')) document.getElementById('del-exp-{{ $experience->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-exp-{{ $experience->id }}" method="POST" action="{{ route(\"{$routePrefix}.experiences.destroy\", $experience) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.experiences.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="company" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
        <input name="position" placeholder="Poste" class="rounded-lg border-gray-300" required>
        <input name="location" placeholder="Lieu" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            Poste actuel
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une expérience
        </button>
    </form>
</section>

'@
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-experiences.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-experiences.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\resources\views\student\cv\_section-languages.blade.php"
$content5 = @'
<section id="languages" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Langues</h2>

    @if ($profile->languages->isNotEmpty())
        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-400">Déjà ajoutées</p>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($profile->languages as $language)
                <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                    <span class="font-medium text-indigo-700">{{ $language->name }}</span>
                    <span class="text-xs text-indigo-400">({{ $language->level_label }})</span>
                    <form method="POST" action="{{ route("{$routePrefix}.languages.destroy", $language) }}">
                        @csrf @method('DELETE')
                        <button class="text-indigo-400 hover:text-red-600">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route("{$routePrefix}.languages.store", $storeParams) }}" class="mt-5 rounded-xl border border-dashed border-gray-300 p-4">
        @csrf

        <p class="text-sm font-medium text-gray-700">
            Coche les langues que tu parles et choisis ton niveau pour chacune.
        </p>

        @php
            $fixedLanguages = ['Arabe', 'Français', 'Anglais', 'Espagnol', 'Allemand'];
            $alreadyHaveLang = $profile->languages->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
        @endphp

        <div class="mt-3 space-y-2">
            @foreach ($fixedLanguages as $fixedLanguage)
                @unless (in_array(mb_strtolower($fixedLanguage), $alreadyHaveLang, true))
                    <div class="flex items-center gap-3">
                        <label class="flex w-32 items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="languages[{{ $fixedLanguage }}][checked]" value="1" class="rounded border-gray-300">
                            {{ $fixedLanguage }}
                        </label>

                        <select name="languages[{{ $fixedLanguage }}][level]" class="rounded-lg border-gray-300 text-sm">
                            <option value="debutant">Débutant</option>
                            <option value="intermediaire" selected>Intermédiaire</option>
                            <option value="courant">Courant</option>
                            <option value="bilingue">Bilingue</option>
                            <option value="natif">Langue maternelle</option>
                        </select>
                    </div>
                @endunless
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4">
            <div>
                <label class="text-xs font-medium text-gray-500">Autre langue</label>
                <input name="custom_language" placeholder="Ex : Italien" class="mt-1 block rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500">Niveau</label>
                <select name="custom_language_level" class="mt-1 block rounded-lg border-gray-300">
                    <option value="debutant">Débutant</option>
                    <option value="intermediaire" selected>Intermédiaire</option>
                    <option value="courant">Courant</option>
                    <option value="bilingue">Bilingue</option>
                    <option value="natif">Langue maternelle</option>
                </select>
            </div>

            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Ajouter les langues cochées
            </button>
        </div>
    </form>
</section>

'@
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-languages.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-languages.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\student\cv\_section-projects.blade.php"
$content6 = @'
<section id="projects" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Projets (Portfolio)</h2>
    <p class="mt-1 text-sm text-gray-500">Ces projets apparaissent sur ton portfolio public.</p>

    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ($profile->projects as $project)
            <form
                method="POST"
                action="{{ route(\"{$routePrefix}.projects.update\", $project) }}"
                enctype="multipart/form-data"
                class="space-y-2 rounded-xl border border-gray-100 p-4"
            >
                @csrf
                @method('PATCH')

                @if ($project->image_url)
                    <img src="{{ $project->image_url }}" class="h-32 w-full rounded-lg object-cover">
                @endif

                <input type="file" name="image" accept="image/*" class="w-full text-xs">
                <input name="title" value="{{ $project->title }}" placeholder="Titre du projet" class="w-full rounded-lg border-gray-300" required>
                <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border-gray-300">{{ $project->description }}</textarea>
                <input name="tags" value="{{ $project->tags }}" placeholder="Tags séparés par virgule" class="w-full rounded-lg border-gray-300">
                <input name="project_url" value="{{ $project->project_url }}" placeholder="Lien du projet" class="w-full rounded-lg border-gray-300">
                <input name="repo_url" value="{{ $project->repo_url }}" placeholder="Lien du code (optionnel)" class="w-full rounded-lg border-gray-300">

                <div class="flex items-center gap-2 pt-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>
                    <button
                        type="button"
                        onclick="if(confirm('Supprimer ce projet ?')) document.getElementById('del-proj-{{ $project->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-proj-{{ $project->id }}" method="POST" action="{{ route(\"{$routePrefix}.projects.destroy\", $project) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.projects.store", $storeParams) }}" enctype="multipart/form-data" class="mt-4 space-y-2 rounded-xl border border-dashed border-gray-300 p-4">
        @csrf
        <input type="file" name="image" accept="image/*" class="w-full text-xs">
        <input name="title" placeholder="Titre du projet" class="w-full rounded-lg border-gray-300" required>
        <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border-gray-300"></textarea>
        <input name="tags" placeholder="Tags séparés par virgule (ex : Excel, Marketing)" class="w-full rounded-lg border-gray-300">
        <input name="project_url" placeholder="Lien du projet" class="w-full rounded-lg border-gray-300">
        <input name="repo_url" placeholder="Lien du code (optionnel)" class="w-full rounded-lg border-gray-300">
        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter un projet</button>
    </form>
</section>

'@
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-projects.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-projects.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
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
                    <form method="POST" action="{{ route("{$routePrefix}.skills.destroy", $skill) }}">
                        @csrf @method('DELETE')
                        <button class="text-indigo-400 hover:text-red-600">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route("{$routePrefix}.skills.store", $storeParams) }}" class="mt-5 rounded-xl border border-dashed border-gray-300 p-4">
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
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-skills.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-skills.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\resources\views\student\cv\edit.blade.php"
$content8 = @'
@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV & Portfolio')
@section('page-title', 'Mon CV & Portfolio')

@section('content')
    @php
        $routePrefix = $routePrefix ?? 'student.cv';
        $storeParams = isset($targetUser) ? [$targetUser] : [];
    @endphp

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
                <a href="{{ route($targetUser ?? false ? 'admin.cv.download.cv' : 'student.cv.download.cv', $storeParams) }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Voir / Imprimer mon CV
                </a>

                <a href="{{ route($targetUser ?? false ? 'admin.cv.download.ats' : 'student.cv.download.ats', $storeParams) }}" target="_blank" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                    Version ATS
                </a>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 rounded-xl bg-gray-50 p-4">
            <form method="POST" action="{{ route("{$routePrefix}.public.toggle", $storeParams) }}">
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
            action="{{ route("{$routePrefix}.profile.update", $storeParams) }}"
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

    @include('student.cv._section-educations', ['profile' => $profile, 'routePrefix' => $routePrefix, 'storeParams' => $storeParams])
    @include('student.cv._section-experiences', ['profile' => $profile, 'routePrefix' => $routePrefix, 'storeParams' => $storeParams])
    @include('student.cv._section-skills', ['profile' => $profile, 'routePrefix' => $routePrefix, 'storeParams' => $storeParams])
    @include('student.cv._section-languages', ['profile' => $profile, 'routePrefix' => $routePrefix, 'storeParams' => $storeParams])
    @include('student.cv._section-certifications', ['profile' => $profile, 'routePrefix' => $routePrefix, 'storeParams' => $storeParams])
    @include('student.cv._section-projects', ['profile' => $profile, 'routePrefix' => $routePrefix, 'storeParams' => $storeParams])
@endsection

'@
$dir8 = Split-Path $path8 -Parent
if (-not (Test-Path $dir8)) { New-Item -ItemType Directory -Path $dir8 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/edit.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/edit.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path9 = "C:\laragon\www\SEA\routes\cv.php"
$content9 = @'
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

// --- Espace admin : édition complète du CV d'un étudiant à sa place ---
Route::middleware(['auth', 'verified', 'role:admin,superviseur'])
    ->prefix('admin/cv')
    ->name('admin.cv.builder.')
    ->group(function () {
        Route::get('/{user}/edit', [\App\Http\Controllers\Admin\CvBuilderController::class, 'edit'])->name('edit');
        Route::patch('/{user}/profile', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/{user}/public', [\App\Http\Controllers\Admin\CvBuilderController::class, 'togglePublic'])->name('public.toggle');

        Route::post('/{user}/educations', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeEducation'])->name('educations.store');
        Route::patch('/educations/{education}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateEducation'])->name('educations.update');
        Route::delete('/educations/{education}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyEducation'])->name('educations.destroy');

        Route::post('/{user}/experiences', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeExperience'])->name('experiences.store');
        Route::patch('/experiences/{experience}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateExperience'])->name('experiences.update');
        Route::delete('/experiences/{experience}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyExperience'])->name('experiences.destroy');

        Route::post('/{user}/skills', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeSkill'])->name('skills.store');
        Route::delete('/skills/{skill}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroySkill'])->name('skills.destroy');

        Route::post('/{user}/languages', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeLanguage'])->name('languages.store');
        Route::delete('/languages/{language}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyLanguage'])->name('languages.destroy');

        Route::post('/{user}/certifications', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeCertification'])->name('certifications.store');
        Route::delete('/certifications/{certification}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyCertification'])->name('certifications.destroy');

        Route::post('/{user}/projects', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeProject'])->name('projects.store');
        Route::patch('/projects/{project}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyProject'])->name('projects.destroy');
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
$dir9 = Split-Path $path9 -Parent
if (-not (Test-Path $dir9)) { New-Item -ItemType Directory -Path $dir9 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path9, $content9, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/cv.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/cv.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
