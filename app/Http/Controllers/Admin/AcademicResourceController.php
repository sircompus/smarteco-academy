<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicResource;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicResourceController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->with('semester.program.level')
            ->where('is_active', true)
            ->get()
            ->sortBy(function ($subject) {
                return sprintf(
                    '%03d-%s-%03d-%03d',
                    $subject->semester?->program?->level?->sort_order ?? 999,
                    $subject->semester?->program?->name ?? '',
                    $subject->semester?->number ?? 999,
                    $subject->sort_order ?? 999
                );
            })
            ->values();

        $selectedSubject = null;
        $resourcesByProfessor = collect();

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByProfessor = $selectedSubject->resources()
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy(fn ($resource) => $resource->professor_name ?: 'Professeur non renseigné')
                    ->map(fn ($group) => $group->groupBy('type'))
                    ->sortKeys();
            }
        }

        return view('admin.centre.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByProfessor' => $resourcesByProfessor,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'type' => ['required', 'in:cours,td,examen,resume'],
            'professor_name' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:20480'],
        ]);

        $count = 0;
        $sortOrder = AcademicResource::where('subject_id', $data['subject_id'])->count();

        foreach ($request->file('files') as $file) {
            $path = $file->store('academic-resources/'.$data['subject_id'], 'public');

            // Si un seul fichier ET un titre saisi -> on l'utilise.
            // Sinon (plusieurs fichiers, ou pas de titre) -> nom du fichier.
            $title = (count($request->file('files')) === 1 && ! empty($data['title']))
                ? $data['title']
                : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            AcademicResource::create([
                'uuid' => (string) Str::uuid(),
                'subject_id' => $data['subject_id'],
                'type' => $data['type'],
                'professor_name' => $data['professor_name'] ?? null,
                'title' => $title,
                'description' => $data['description'] ?? null,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
                'is_published' => true,
                'sort_order' => $sortOrder,
            ]);

            $sortOrder++;
            $count++;
        }

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $data['subject_id']])
            ->with('success', "{$count} fichier(s) mis en ligne.");
    }

    public function destroy(AcademicResource $resource): RedirectResponse
    {
        $subjectId = $resource->subject_id;

        Storage::disk($resource->disk)->delete($resource->path);
        $resource->delete();

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $subjectId])
            ->with('success', 'Le document a été supprimé.');
    }

    public function edit(AcademicResource $resource): View
    {
        return view('admin.centre.library.edit', [
            'resource' => $resource,
        ]);
    }

    public function update(Request $request, AcademicResource $resource): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:cours,td,examen,resume'],
            'professor_name' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $resource->update($data);

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $resource->subject_id])
            ->with('success', 'Le document a été mis à jour.');
    }
}
