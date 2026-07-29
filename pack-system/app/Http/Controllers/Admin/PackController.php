<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\PackEnrollment;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackController extends Controller
{
    public function index(): View
    {
        return view('admin.centre.packs.index', [
            'packs' => Pack::query()
                ->with([
                    'semester.program.level',
                    'subject.semester.program.level',
                ])
                ->orderBy('sort_order')
                ->latest()
                ->get(),

            'semesters' => Semester::query()
                ->with('program.level')
                ->orderBy('sort_order')
                ->orderBy('number')
                ->get(),

            'subjects' => Subject::query()
                ->with('semester.program.level')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'pendingEnrollments' => PackEnrollment::query()
                ->with(['user', 'pack'])
                ->where('status', 'en_attente')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:semestre,module'],
            'semester_id' => ['nullable', 'required_if:type,semestre', 'exists:semesters,id'],
            'subject_id' => ['nullable', 'required_if:type,module', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        Pack::create([
            'uuid' => (string) Str::uuid(),
            'type' => $data['type'],
            'semester_id' => $data['type'] === 'semestre' ? $data['semester_id'] : null,
            'subject_id' => $data['type'] === 'module' ? $data['subject_id'] : null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Le pack a été créé.');
    }

    public function updateEnrollmentStatus(
        Request $request,
        PackEnrollment $packEnrollment
    ): RedirectResponse {
        $data = $request->validate([
            'status' => ['required', 'in:en_attente,active,annulee'],
        ]);

        $packEnrollment->update([
            'status' => $data['status'],
            'activated_at' => $data['status'] === 'active' ? now() : null,
        ]);

        return back()->with('success', 'Le statut de l’inscription a été mis à jour.');
    }
}
