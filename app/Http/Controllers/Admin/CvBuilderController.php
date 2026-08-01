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
