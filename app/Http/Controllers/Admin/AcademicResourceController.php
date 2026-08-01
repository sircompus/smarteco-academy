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
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedSubject = null;
        $resourcesByType = collect();

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByType = $selectedSubject->resources()
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('type');
            }
        }

        return view('admin.centre.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByType' => $resourcesByType,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'type' => ['required', 'in:cours,td,examen,resume'],
            'professor_name' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store('academic-resources/'.$data['subject_id'], 'public');

        AcademicResource::create([
            'uuid' => (string) Str::uuid(),
            'subject_id' => $data['subject_id'],
            'type' => $data['type'],
            'professor_name' => $data['professor_name'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'is_published' => true,
            'sort_order' => AcademicResource::where('subject_id', $data['subject_id'])->count(),
        ]);

        return redirect()
            ->route('admin.centre.library.index', ['subject_id' => $data['subject_id']])
            ->with('success', 'Le document a été mis en ligne.');
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
}
