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
