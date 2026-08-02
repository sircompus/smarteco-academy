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
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Admin/CvBuilderController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Admin/CvBuilderController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Http\Controllers\PublicPortfolioController.php"
$content1 = @'
<?php

namespace App\Http\Controllers;

use App\Models\CvProfile;
use Illuminate\View\View;

class PublicPortfolioController extends Controller
{
    public function show(string $slug): View
    {
        $profile = CvProfile::query()
            ->where('public_slug', $slug)
            ->where('is_public', true)
            ->with(['user.profile', 'educations', 'experiences', 'skills', 'languages', 'certifications', 'projects'])
            ->firstOrFail();

        return view('portfolio.show', ['profile' => $profile]);
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/PublicPortfolioController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/PublicPortfolioController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\app\Http\Controllers\Student\CvController.php"
$content2 = @'
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

        foreach ($namesToAdd->unique() as $name) {
            if (in_array(mb_strtolower($name), $existingNames, true)) {
                continue; // déjà ajoutée, on évite les doublons
            }

            $profile->skills()->create([
                'name' => $name,
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
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Http/Controllers/Student/CvController.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Http/Controllers/Student/CvController.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\app\Models\CvProfile.php"
$content3 = @'
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
        'show_in_navigation',
        'public_slug',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'show_in_navigation' => 'boolean',
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
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Models/CvProfile.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Models/CvProfile.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\database\migrations\2026_08_01_170000_add_show_in_navigation_to_cv_profiles.php"
$content4 = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_profiles', function (Blueprint $table) {
            $table->boolean('show_in_navigation')->default(false)->after('is_public');
        });
    }

    public function down(): void
    {
        Schema::table('cv_profiles', function (Blueprint $table) {
            $table->dropColumn('show_in_navigation');
        });
    }
};

'@
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/migrations/2026_08_01_170000_add_show_in_navigation_to_cv_profiles.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/migrations/2026_08_01_170000_add_show_in_navigation_to_cv_profiles.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\resources\views\layouts\student.blade.php"
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
        @yield('title', 'Espace étudiant') — SmartEco Academy
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
    class="min-h-screen bg-gray-100 text-gray-900"
>
    {{-- Arrière-plan mobile --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Menu latéral étudiant --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-xl transition-transform duration-300 lg:translate-x-0 print:hidden"
    >
        {{-- Logo --}}
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
            <a
                href="{{ route('student.dashboard') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white"
                >
                    SE
                </div>

                <div>
                    <p class="font-bold text-gray-900">
                        SmartEco Academy
                    </p>

                    <p class="text-xs text-gray-500">
                        Espace étudiant
                    </p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Principal
            </p>

            <div class="space-y-1">
                {{-- Tableau de bord --}}
                <a
                    href="{{ route('student.dashboard') }}"
                    class="{{ request()->routeIs('student.dashboard')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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

                    Tableau de bord
                </a>

                {{-- Cours du module Centre --}}
                <a
                    href="{{ route('student.courses.index') }}"
                    class="{{ request()->routeIs('student.courses.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A8.968 8.968 0 003 6.253v13A8.968 8.968 0 017.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5A8.968 8.968 0 0121 6.253v13A8.968 8.968 0 0016.5 18c-1.746 0-3.332.477-4.5 1.253"
                        />
                    </svg>

                    Mes cours
                </a>

                {{-- Packs (semestres / modules) --}}
                <a
                    href="{{ route('student.packs.index') }}"
                    class="{{ request()->routeIs('student.packs.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                    href="{{ route('student.library.index') }}"
                    class="{{ request()->routeIs('student.library.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Carrière
            </p>

            <div class="space-y-1">
                {{-- CV & Portfolio --}}
                <a
                    href="{{ route('student.cv.edit') }}"
                    class="{{ request()->routeIs('student.cv.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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

                    Mon CV & Portfolio
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Formation
            </p>

            <div class="space-y-1">
                {{-- Inscriptions --}}
                <a
    href="{{ route('student.registrations.index') }}"
    class="{{ request()->routeIs('student.registrations.*')
        ? 'bg-indigo-50 text-indigo-700'
        : 'text-gray-700 hover:bg-gray-100' }}
        flex items-center rounded-lg px-4 py-3 text-sm font-medium"
>
    Mes inscriptions
</a>

                {{-- Formations --}}
                <a
                    href="{{ route('student.trainings.index') }}"
                    class="{{ request()->routeIs('student.trainings.*')
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-700 hover:bg-gray-100' }}
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
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"
                        />
                    </svg>

                    Mes formations
                </a>

                {{-- Examens --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
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
                            d="M9 5h6m-6 4h6m-6 4h4m-8-9h14a2 2 0 012 2v14H3V6a2 2 0 012-2z"
                        />
                    </svg>

                    Mes examens
                </a>

                {{-- Projets --}}
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
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
                            d="M3 7h18M5 7v12h14V7M9 11h6"
                        />
                    </svg>

                    Mes projets
                </a>
            </div>

            <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Services
            </p>

            <div class="space-y-1">
                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    CV ATS
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Portfolio
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Community
                </a>
            </div>
        </nav>

        {{-- Profils mis en avant (fondateur / équipe) --}}
        @php
            $featuredProfiles = \App\Models\CvProfile::where('is_public', true)
                ->where('show_in_navigation', true)
                ->get();
        @endphp

        @if ($featuredProfiles->isNotEmpty())
            <div class="border-t border-gray-200 px-4 py-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Notre équipe</p>

                <div class="flex flex-wrap gap-2">
                    @foreach ($featuredProfiles as $featured)
                        <a
                            href="{{ $featured->public_url }}"
                            target="_blank"
                            title="{{ $featured->full_name }} — CV, portfolio & biographie"
                            class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border-2 border-indigo-100 bg-indigo-50 font-semibold text-indigo-700 transition hover:border-indigo-400"
                        >
                            @if ($featured->photo_url)
                                <img src="{{ $featured->photo_url }}" class="h-full w-full object-cover" alt="{{ $featured->full_name }}">
                            @else
                                {{ strtoupper(substr($featured->full_name ?: '?', 0, 1)) }}
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Profil étudiant --}}
        <div class="border-t border-gray-200 p-4">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-lg p-3 transition hover:bg-gray-100"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700"
                >
                    @if (auth()->user()->profile?->avatar_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile->avatar_path) }}" class="h-full w-full rounded-full object-cover" alt="Avatar">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-gray-500">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Zone principale --}}
    <div class="min-h-screen lg:pl-64 print:pl-0">
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
                        @yield('page-title', 'Tableau de bord')
                    </h1>

                    <p class="hidden text-xs text-gray-500 sm:block">
                        Bienvenue sur votre espace personnel
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Notifications --}}
                <button
                    type="button"
                    class="relative rounded-lg p-2 text-gray-600 transition hover:bg-gray-100"
                    title="Notifications"
                >
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
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>

                    @if (auth()->user()->unreadNotifications()->count() > 0)
                        <span
                            class="absolute right-0 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                        >
                            {{ auth()->user()->unreadNotifications()->count() }}
                        </span>
                    @endif
                </button>

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
                        class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 sm:px-4"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Messages --}}
        @if (session('success'))
            <div
                class="mx-4 mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 sm:mx-6"
            >
                {{ session('success') }}
            </div>
        @endif

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
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/layouts/student.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/layouts/student.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\resources\views\portfolio\show.blade.php"
$content6 = @'
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
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/portfolio/show.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/portfolio/show.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\resources\views\student\cv\edit.blade.php"
$content7 = @'
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

        @if ($profile->is_public)
            <form method="POST" action="{{ route("{$routePrefix}.navigation.toggle", $storeParams) }}" class="mt-4">
                @csrf
                @method('PATCH')
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="show_in_navigation"
                        value="1"
                        onchange="this.form.submit()"
                        @checked($profile->show_in_navigation)
                        class="rounded border-gray-300"
                    >
                    Afficher mon icône (photo) dans le menu étudiant, avec accès à mon CV/portfolio/biographie
                </label>
            </form>
        @endif
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
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/cv/edit.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/cv/edit.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
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
        Route::patch('/navigation', [CvController::class, 'toggleNavigation'])->name('navigation.toggle');

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
        Route::patch('/{user}/navigation', [\App\Http\Controllers\Admin\CvBuilderController::class, 'toggleNavigation'])->name('navigation.toggle');

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
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/cv.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/cv.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
