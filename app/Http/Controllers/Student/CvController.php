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

        return back()->with('success', 'Profil mis à jour.');
    }

    public function togglePublic(Request $request): RedirectResponse
    {
        $profile = $this->currentProfile();

        if (! $profile->is_public && ! $profile->public_slug) {
            $profile->public_slug = Str::slug($profile->full_name ?: 'portfolio').'-'.Str::lower(Str::random(6));
        }

        $profile->is_public = ! $profile->is_public;
        $profile->save();

        return back()->with(
            'success',
            $profile->is_public
                ? 'Ton portfolio est maintenant public : '.$profile->public_url
                : 'Ton portfolio est de nouveau privé.'
        );
    }

    // --- Formation ---

    public function storeEducation(Request $request): RedirectResponse
    {
        $data = $this->validateEducation($request);
        $profile = $this->currentProfile();

        $profile->educations()->create($data + ['sort_order' => $profile->educations()->count()]);

        return back()->with('success', 'Formation ajoutée.');
    }

    public function updateEducation(Request $request, CvEducation $education): RedirectResponse
    {
        $this->authorizeOwnership($education->profile);

        $education->update($this->validateEducation($request));

        return back()->with('success', 'Formation mise à jour.');
    }

    public function destroyEducation(CvEducation $education): RedirectResponse
    {
        $this->authorizeOwnership($education->profile);
        $education->delete();

        return back()->with('success', 'Formation supprimée.');
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

        return back()->with('success', 'Expérience ajoutée.');
    }

    public function updateExperience(Request $request, CvExperience $experience): RedirectResponse
    {
        $this->authorizeOwnership($experience->profile);

        $experience->update($this->validateExperience($request));

        return back()->with('success', 'Expérience mise à jour.');
    }

    public function destroyExperience(CvExperience $experience): RedirectResponse
    {
        $this->authorizeOwnership($experience->profile);
        $experience->delete();

        return back()->with('success', 'Expérience supprimée.');
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

    public function storeSkill(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'in:debutant,intermediaire,avance,expert'],
        ]);

        $profile = $this->currentProfile();
        $profile->skills()->create($data + ['sort_order' => $profile->skills()->count()]);

        return back()->with('success', 'Compétence ajoutée.');
    }

    public function destroySkill(CvSkill $skill): RedirectResponse
    {
        $this->authorizeOwnership($skill->profile);
        $skill->delete();

        return back()->with('success', 'Compétence supprimée.');
    }

    // --- Langues ---

    public function storeLanguage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'in:debutant,intermediaire,courant,bilingue,natif'],
        ]);

        $profile = $this->currentProfile();
        $profile->languages()->create($data + ['sort_order' => $profile->languages()->count()]);

        return back()->with('success', 'Langue ajoutée.');
    }

    public function destroyLanguage(CvLanguage $language): RedirectResponse
    {
        $this->authorizeOwnership($language->profile);
        $language->delete();

        return back()->with('success', 'Langue supprimée.');
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

        return back()->with('success', 'Certification ajoutée.');
    }

    public function destroyCertification(CvCertification $certification): RedirectResponse
    {
        $this->authorizeOwnership($certification->profile);
        $certification->delete();

        return back()->with('success', 'Certification supprimée.');
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

        return back()->with('success', 'Projet ajouté.');
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

        return back()->with('success', 'Projet mis à jour.');
    }

    public function destroyProject(PortfolioProject $project): RedirectResponse
    {
        $this->authorizeOwnership($project->profile);
        $project->delete();

        return back()->with('success', 'Projet supprimé.');
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