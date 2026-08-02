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

    public function toggleNavigation(Request $request, User $user): RedirectResponse
    {
        $profile = $this->profileFor($user);

        $profile->update([
            'show_in_navigation' => $request->boolean('show_in_navigation'),
        ]);

        return $this->toSection($user, 'exports', 'Préférence mise à jour.');
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

        $categoriesByName = \App\Models\SkillSuggestion::pluck('category', 'name');

        foreach ($namesToAdd->unique() as $name) {
            if (in_array(mb_strtolower($name), $existingNames, true)) {
                continue;
            }

            $profile->skills()->create([
                'name' => $name,
                'category' => $categoriesByName[$name] ?? null,
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
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/CvBuilderController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/CvBuilderController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Http\Controllers\Student\CvController.php"
$content1 = @'
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CvCertification;
use App\Models\CvEducation;
use App\Models\CvExperience;
use App\Models\CvLanguage;
use App\Models\CvProfile;
use App\Models\CvSkill;
use App\Models\PortfolioProject;
use App\Services\AtsScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CvController extends Controller
{
    public function edit(): View
    {
        $profile = $this->currentProfile();

        $profile->load([
            'educations', 'experiences', 'skills',
            'languages', 'certifications', 'projects',
        ]);

        $ats = app(AtsScoreService::class)->evaluate($profile);

        return view('student.cv.edit', [
            'profile' => $profile,
            'ats' => $ats,
        ]);
    }

    private function currentProfile(): CvProfile
    {
        return CvProfile::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'uuid' => (string) Str::uuid(),
                'full_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ]
        );
    }

    /**
     * Redirige vers le CV builder en restant sur la section concernée
     * (ancre HTML), au lieu de revenir tout en haut de la page.
     */
    private function toSection(string $anchor, string $message): RedirectResponse
    {
        return redirect(route('student.cv.edit').'#'.$anchor)->with('success', $message);
    }

    // --- Profil principal ---

    public function updateProfile(Request $request): RedirectResponse
    {
        $profile = $this->currentProfile();

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

        return $this->toSection('profile-info', 'Profil mis à jour.');
    }

    public function togglePublic(Request $request): RedirectResponse
    {
        $profile = $this->currentProfile();

        if (! $profile->is_public && ! $profile->public_slug) {
            $profile->public_slug = Str::slug($profile->full_name ?: 'portfolio').'-'.Str::lower(Str::random(6));
        }

        $profile->is_public = ! $profile->is_public;
        $profile->save();

        return $this->toSection(
            'exports',
            $profile->is_public
                ? 'Ton portfolio est maintenant public : '.$profile->public_url
                : 'Ton portfolio est de nouveau privé.'
        );
    }

    public function toggleNavigation(Request $request): RedirectResponse
    {
        $profile = $this->currentProfile();

        $profile->update([
            'show_in_navigation' => $request->boolean('show_in_navigation'),
        ]);

        return $this->toSection('exports', 'Préférence mise à jour.');
    }

    // --- Formation ---

    public function storeEducation(Request $request): RedirectResponse
    {
        $data = $this->validateEducation($request);
        $profile = $this->currentProfile();

        $profile->educations()->create($data + ['sort_order' => $profile->educations()->count()]);

        return $this->toSection('educations', 'Formation ajoutée.');
    }

    public function updateEducation(Request $request, CvEducation $education): RedirectResponse
    {
        $this->authorizeOwnership($education->profile);

        $education->update($this->validateEducation($request));

        return $this->toSection('educations', 'Formation mise à jour.');
    }

    public function destroyEducation(CvEducation $education): RedirectResponse
    {
        $this->authorizeOwnership($education->profile);
        $education->delete();

        return $this->toSection('educations', 'Formation supprimée.');
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

    public function storeExperience(Request $request): RedirectResponse
    {
        $data = $this->validateExperience($request);
        $profile = $this->currentProfile();

        $profile->experiences()->create($data + ['sort_order' => $profile->experiences()->count()]);

        return $this->toSection('experiences', 'Expérience ajoutée.');
    }

    public function updateExperience(Request $request, CvExperience $experience): RedirectResponse
    {
        $this->authorizeOwnership($experience->profile);

        $experience->update($this->validateExperience($request));

        return $this->toSection('experiences', 'Expérience mise à jour.');
    }

    public function destroyExperience(CvExperience $experience): RedirectResponse
    {
        $this->authorizeOwnership($experience->profile);
        $experience->delete();

        return $this->toSection('experiences', 'Expérience supprimée.');
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

    // --- Compétences (sélection multiple par cases à cocher) ---

    public function storeSkill(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:100'],
            'level' => ['required', 'in:debutant,intermediaire,avance,expert'],
            'custom_skill' => ['nullable', 'string', 'max:100'],
        ]);

        $profile = $this->currentProfile();
        $existingNames = $profile->skills()->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();

        $namesToAdd = collect($data['skills'] ?? []);

        if (! empty($data['custom_skill'])) {
            $namesToAdd->push($data['custom_skill']);
        }

        $count = 0;

        $categoriesByName = \App\Models\SkillSuggestion::pluck('category', 'name');

        foreach ($namesToAdd->unique() as $name) {
            if (in_array(mb_strtolower($name), $existingNames, true)) {
                continue; // déjà ajoutée, on évite les doublons
            }

            $profile->skills()->create([
                'name' => $name,
                'category' => $categoriesByName[$name] ?? null,
                'level' => $data['level'],
                'sort_order' => $profile->skills()->count(),
            ]);

            $count++;
        }

        return $this->toSection(
            'skills',
            $count > 0 ? "{$count} compétence(s) ajoutée(s)." : 'Aucune nouvelle compétence à ajouter (déjà présentes).'
        );
    }

    public function destroySkill(CvSkill $skill): RedirectResponse
    {
        $this->authorizeOwnership($skill->profile);
        $skill->delete();

        return $this->toSection('skills', 'Compétence supprimée.');
    }

    // --- Langues (sélection multiple par cases à cocher, avec niveau par langue) ---

    public function storeLanguage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'languages' => ['nullable', 'array'],
            'languages.*.checked' => ['nullable', 'boolean'],
            'languages.*.level' => ['nullable', 'in:debutant,intermediaire,courant,bilingue,natif'],
            'custom_language' => ['nullable', 'string', 'max:100'],
            'custom_language_level' => ['nullable', 'in:debutant,intermediaire,courant,bilingue,natif'],
        ]);

        $profile = $this->currentProfile();
        $existingNames = $profile->languages()->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();

        $count = 0;

        foreach ($data['languages'] ?? [] as $name => $entry) {
            if (empty($entry['checked'])) {
                continue;
            }

            if (in_array(mb_strtolower($name), $existingNames, true)) {
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

        return $this->toSection(
            'languages',
            $count > 0 ? "{$count} langue(s) ajoutée(s)." : 'Aucune nouvelle langue à ajouter (déjà présentes).'
        );
    }

    public function destroyLanguage(CvLanguage $language): RedirectResponse
    {
        $this->authorizeOwnership($language->profile);
        $language->delete();

        return $this->toSection('languages', 'Langue supprimée.');
    }

    // --- Certifications ---

    public function storeCertification(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'issuer' => ['nullable', 'string', 'max:150'],
            'date_obtained' => ['nullable', 'date'],
            'credential_url' => ['nullable', 'url', 'max:255'],
        ]);

        $profile = $this->currentProfile();
        $profile->certifications()->create($data + ['sort_order' => $profile->certifications()->count()]);

        return $this->toSection('certifications', 'Certification ajoutée.');
    }

    public function destroyCertification(CvCertification $certification): RedirectResponse
    {
        $this->authorizeOwnership($certification->profile);
        $certification->delete();

        return $this->toSection('certifications', 'Certification supprimée.');
    }

    // --- Projets (portfolio) ---

    public function storeProject(Request $request): RedirectResponse
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

        $profile = $this->currentProfile();
        $profile->projects()->create($data + ['sort_order' => $profile->projects()->count()]);

        return $this->toSection('projects', 'Projet ajouté.');
    }

    public function updateProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $this->authorizeOwnership($project->profile);

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

        return $this->toSection('projects', 'Projet mis à jour.');
    }

    public function destroyProject(PortfolioProject $project): RedirectResponse
    {
        $this->authorizeOwnership($project->profile);
        $project->delete();

        return $this->toSection('projects', 'Projet supprimé.');
    }

    // --- Rendus imprimables ---

    public function showCv(): View
    {
        $profile = $this->currentProfile();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications']);

        $view = $profile->cv_template === 'moderne' ? 'student.cv.templates.moderne' : 'student.cv.templates.classique';

        return view($view, ['profile' => $profile]);
    }

    public function showAts(): View
    {
        $profile = $this->currentProfile();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications']);

        return view('student.cv.templates.ats', ['profile' => $profile]);
    }

    private function authorizeOwnership(CvProfile $profile): void
    {
        abort_unless($profile->user_id === Auth::id(), 403);
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Student/CvController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Student/CvController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\app\Models\CvSkill.php"
$content2 = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvSkill extends Model
{
    use HasFactory;

    public const LEVELS = [
        'debutant' => 'Débutant',
        'intermediaire' => 'Intermédiaire',
        'avance' => 'Avancé',
        'expert' => 'Expert',
    ];

    protected $fillable = ['cv_profile_id', 'name', 'category', 'level', 'sort_order'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'cv_profile_id');
    }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVELS[$this->level] ?? $this->level;
    }

    public function getLevelPercentAttribute(): int
    {
        return match ($this->level) {
            'debutant' => 25,
            'intermediaire' => 50,
            'avance' => 75,
            'expert' => 100,
            default => 50,
        };
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvSkill.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvSkill.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\database\migrations\2026_08_01_180000_add_category_to_cv_skills.php"
$content3 = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_skills', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('cv_skills', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};

'@
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/migrations/2026_08_01_180000_add_category_to_cv_skills.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/migrations/2026_08_01_180000_add_category_to_cv_skills.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\database\seeders\AliBahtitCvSeeder.php"
$content4 = @'
<?php

namespace Database\Seeders;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AliBahtitCvSeeder extends Seeder
{
    /**
     * Se lance à la demande :
     *   php artisan db:seed --class=AliBahtitCvSeeder
     *
     * Remplit le profil CV/Portfolio du compte sircompus@gmail.com
     * avec les vraies données extraites de son CV, rend le portfolio
     * public et active l'icône dans le menu étudiant.
     */
    public function run(): void
    {
        $user = User::whereRaw('LOWER(email) = ?', ['sircompus@gmail.com'])->first();

        if (! $user) {
            $this->command?->error('Compte sircompus@gmail.com introuvable.');

            return;
        }

        $existingSlug = CvProfile::where('user_id', $user->id)->value('public_slug');

        $profile = CvProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'uuid' => (string) Str::uuid(),
                'full_name' => 'Ali Bahtit',
                'headline' => "Candidat au doctorat en Management, Systèmes d'Information et Intelligence Artificielle",
                'email' => 'sircompus@gmail.com',
                'phone' => '06 63 62 83 76',
                'address' => 'Tétouan, Maroc',
                'summary' => "Candidat au doctorat disposant d'un parcours pluridisciplinaire en économie-gestion, management des systèmes d'information, leadership managérial augmenté par l'intelligence artificielle et transformation numérique. Les travaux de recherche menés sur l'industrie 4.0, la migration des systèmes vers le cloud via Google Cloud Platform et la veille stratégique augmentée traduisent une trajectoire scientifique cohérente centrée sur l'innovation, l'aide à la décision et la performance organisationnelle. Plus de vingt ans d'expérience en informatique, maintenance, logiciels de gestion, formation et accompagnement des utilisateurs.",
                'cv_template' => 'moderne',
                'portfolio_template' => 'elegant',
                'is_public' => true,
                'show_in_navigation' => true,
                'public_slug' => $existingSlug ?? 'ali-bahtit-'.Str::lower(Str::random(6)),
            ]
        );

        // On repart propre pour éviter les doublons si on relance le seeder.
        $profile->educations()->delete();
        $profile->experiences()->delete();
        $profile->skills()->delete();
        $profile->languages()->delete();

        // --- Formation (de la plus récente à la plus ancienne) ---
        $educations = [
            [
                'institution' => "Master en Leadership managérial augmenté par les outils d'intelligence artificielle",
                'degree' => 'Master',
                'field_of_study' => "Leadership augmenté, aide à la décision, pilotage de la performance, innovation et conduite du changement",
                'start_date' => '2024-09-01',
                'end_date' => '2026-06-30',
                'is_current' => true,
            ],
            [
                'institution' => "Master spécialisé en Management des systèmes d'information",
                'degree' => 'Master spécialisé',
                'field_of_study' => 'Gouvernance des SI, alignement stratégique, transformation numérique et management de la performance',
                'start_date' => '2022-09-01',
                'end_date' => '2024-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'Licence en économie, parcours Gestion',
                'degree' => 'Licence',
                'field_of_study' => 'Économie, parcours Gestion',
                'start_date' => '2019-09-01',
                'end_date' => '2020-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'EPSIEL, Fès',
                'degree' => 'DCESS',
                'field_of_study' => 'Électronique et informatique industrielle',
                'start_date' => '2001-09-01',
                'end_date' => '2003-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'Licence en informatique électronique',
                'degree' => 'Licence',
                'field_of_study' => 'Système de réseaux',
                'start_date' => '1997-09-01',
                'end_date' => '2001-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'Baccalauréat en sciences expérimentales',
                'degree' => 'Baccalauréat',
                'field_of_study' => 'Sciences expérimentales',
                'start_date' => '1996-09-01',
                'end_date' => '1997-06-30',
                'is_current' => false,
            ],
        ];

        foreach ($educations as $i => $education) {
            $profile->educations()->create($education + ['sort_order' => $i]);
        }

        // --- Expérience (académique, pédagogique et professionnelle) ---
        $experiences = [
            [
                'company' => 'Centres partenaires et accompagnement universitaire',
                'position' => 'Formateur et intervenant en support académique',
                'location' => 'Tétouan',
                'start_date' => '2019-01-01',
                'end_date' => null,
                'is_current' => true,
                'description' => "Conception et animation de cours de support du semestre S1 au semestre S6 ainsi que pour des parcours de master en management des systèmes d'information et en ressources humaines. Enseignement et accompagnement en économie, gestion financière, contrôle de gestion, finance et outils numériques. Préparation de supports structurés, d'exercices pratiques et de dispositifs d'accompagnement individualisé. Mobilisation raisonnée des outils d'intelligence artificielle pour la recherche, la synthèse et la personnalisation des apprentissages.",
            ],
            [
                'company' => 'Divers centres de formation, Région Nord',
                'position' => 'Formateur en informatique, programmation et logiciels de gestion',
                'location' => 'Région Nord',
                'start_date' => '2005-01-01',
                'end_date' => '2023-12-31',
                'is_current' => false,
                'description' => 'Animation de formations en R, Python, PHP, HTML, JEE, bases de données, bureautique avancée et logiciels de gestion. Formation continue auprès de centres de formation, associations, structures professionnelles et établissements partenaires. Adaptation des contenus aux besoins opérationnels et accompagnement des utilisateurs dans l\'adoption des outils numériques.',
            ],
            [
                'company' => 'Cabinet fiduciaire & Visitec SARL',
                'position' => 'Expériences complémentaires en gestion et environnement professionnel',
                'location' => 'Tétouan',
                'start_date' => '2015-01-01',
                'end_date' => '2019-12-31',
                'is_current' => false,
                'description' => 'Immersion en cabinet fiduciaire : comptabilité, fiscalité et gestion administrative. Stage au sein de Visitec SARL : compréhension de l\'environnement opérationnel des centres de visite technique.',
            ],
            [
                'company' => 'BEGIN TO INFORMATIQUE',
                'position' => 'Technicien et responsable maintenance informatique, électronique industrielle et réseaux',
                'location' => 'Région Nord',
                'start_date' => '1999-01-01',
                'end_date' => '2017-12-31',
                'is_current' => false,
                'description' => 'Responsabilité du service maintenance et gestion d\'interventions préventives et correctives. Maintenance de systèmes informatiques, électroniques et industriels, réseaux et équipements automatisés. Gestion de contrats de maintenance, coordination des interventions et formation des utilisateurs. Interventions auprès d\'établissements publics, de centres techniques, d\'instituts de formation et d\'organisations de la région Nord.',
            ],
        ];

        foreach ($experiences as $i => $experience) {
            $profile->experiences()->create($experience + ['sort_order' => $i]);
        }

        // --- Compétences ---
        $skills = [
            // IA et analyse
            'IA générative appliquée au management' => ['Intelligence artificielle et analyse', 'expert'],
            "Analyse et synthèse de l'information" => ['Intelligence artificielle et analyse', 'expert'],
            'Veille stratégique' => ['Intelligence artificielle et analyse', 'expert'],
            'Machine learning' => ['Intelligence artificielle et analyse', 'avance'],
            'Business intelligence' => ['Intelligence artificielle et analyse', 'avance'],
            'Power BI' => ['Intelligence artificielle et analyse', 'avance'],
            'R (langage statistique)' => ['Intelligence artificielle et analyse', 'avance'],
            'Python' => ['Intelligence artificielle et analyse', 'avance'],
            // SI et cloud
            'Gouvernance des SI' => ["Systèmes d'information et cloud", 'expert'],
            'Transformation numérique' => ["Systèmes d'information et cloud", 'expert'],
            'Google Cloud Platform' => ["Systèmes d'information et cloud", 'avance'],
            'Docker' => ["Systèmes d'information et cloud", 'intermediaire'],
            'Kubernetes' => ["Systèmes d'information et cloud", 'intermediaire'],
            'SQL Server' => ["Systèmes d'information et cloud", 'avance'],
            'Bases de données' => ["Systèmes d'information et cloud", 'expert'],
            // Développement
            'PHP' => ['Développement et outils numériques', 'avance'],
            'HTML' => ['Développement et outils numériques', 'avance'],
            'JEE' => ['Développement et outils numériques', 'intermediaire'],
            'Excel' => ['Développement et outils numériques', 'expert'],
            'Word' => ['Développement et outils numériques', 'expert'],
            'PowerPoint' => ['Développement et outils numériques', 'expert'],
            'MS Project' => ['Développement et outils numériques', 'avance'],
            // Logiciels de gestion
            'SAGE SAARI' => ['Logiciels de gestion', 'avance'],
            'SAGE i7' => ['Logiciels de gestion', 'avance'],
            'CIEL' => ['Logiciels de gestion', 'intermediaire'],
            'EBP' => ['Logiciels de gestion', 'intermediaire'],
            // Infrastructure
            'Réseaux' => ['Infrastructure et maintenance', 'expert'],
            'Électromécanique' => ['Infrastructure et maintenance', 'avance'],
            'Électronique industrielle' => ['Infrastructure et maintenance', 'avance'],
            // Professionnelles
            'Rigueur, autonomie, persévérance et sens des responsabilités' => ['Compétences professionnelles', 'expert'],
            "Capacité d'analyse, de synthèse, d'organisation et de résolution de problèmes" => ['Compétences professionnelles', 'expert'],
            'Pédagogie, communication, vulgarisation et accompagnement du changement' => ['Compétences professionnelles', 'expert'],
            'Travail en équipe, relation avec les parties prenantes et orientation résultats' => ['Compétences professionnelles', 'expert'],
        ];

        $i = 0;
        foreach ($skills as $name => [$category, $level]) {
            $profile->skills()->create([
                'name' => $name,
                'category' => $category,
                'level' => $level,
                'sort_order' => $i,
            ]);
            $i++;
        }

        // --- Langues ---
        $languages = [
            'Arabe' => 'natif',
            'Français' => 'courant',
            'Espagnol' => 'intermediaire',
            'Anglais' => 'intermediaire',
        ];

        $i = 0;
        foreach ($languages as $name => $level) {
            $profile->languages()->create([
                'name' => $name,
                'level' => $level,
                'sort_order' => $i,
            ]);
            $i++;
        }

        $this->command?->info('Profil CV de Ali Bahtit rempli avec succès.');
        $this->command?->info('Lien public : '.$profile->public_url);
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/seeders/AliBahtitCvSeeder.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/seeders/AliBahtitCvSeeder.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\resources\views\portfolio\show.blade.php"
$content5 = @'
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

                    @php
                        $skillsByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
                    @endphp

                    @foreach ($skillsByCategory as $category => $categorySkills)
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $category }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($categorySkills as $skill)
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    {{ $skill->name }}
                                </span>
                            @endforeach
                        </div>
                    @endforeach
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
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/portfolio/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/portfolio/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\student\cv\_section-skills.blade.php"
$content6 = @'
<section id="skills" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Compétences</h2>

    @if ($profile->skills->isNotEmpty())
        @php
            $addedByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
        @endphp

        @foreach ($addedByCategory as $category => $categorySkills)
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-400">{{ $category }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($categorySkills as $skill)
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
        @endforeach
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
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/_section-skills.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/_section-skills.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\resources\views\student\cv\templates\ats.blade.php"
$content7 = @'
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
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/ats.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/ats.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\resources\views\student\cv\templates\classique.blade.php"
$content8 = @'
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

                    @php
                        $skillsByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
                    @endphp

                    @foreach ($skillsByCategory as $category => $categorySkills)
                        <p class="mt-2 text-xs font-semibold text-indigo-600">{{ $category }}</p>
                        <ul class="mt-1 space-y-1 text-gray-700">
                            @foreach ($categorySkills as $skill)
                                <li>{{ $skill->name }}</li>
                            @endforeach
                        </ul>
                    @endforeach
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
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/classique.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/classique.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path9 = "C:\laragon\www\SEA\resources\views\student\cv\templates\moderne.blade.php"
$content9 = @'
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
try {
    [System.IO.File]::WriteAllText($path9, $content9, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/templates/moderne.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/templates/moderne.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
