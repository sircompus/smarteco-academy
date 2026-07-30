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
        $packs = Pack::query()
            ->with([
                'semester.program.level',
                'subject.semester.program.level',
            ])
            ->get()
            ->sortBy(function ($pack) {
                $semesterNumber = $pack->isTypeSemestre()
                    ? $pack->semester?->number
                    : $pack->subject?->semester?->number;

                $levelOrder = $pack->isTypeSemestre()
                    ? $pack->semester?->program?->level?->sort_order
                    : $pack->subject?->semester?->program?->level?->sort_order;

                $programName = $pack->isTypeSemestre()
                    ? $pack->semester?->program?->name
                    : $pack->subject?->semester?->program?->name;

                $typeOrder = $pack->isTypeSemestre() ? 0 : 1;

                $subjectOrder = $pack->isTypeSemestre()
                    ? 0
                    : ($pack->subject?->sort_order ?? 0);

                return sprintf(
                    '%03d-%03d-%s-%d-%03d',
                    $semesterNumber ?? 999,
                    $levelOrder ?? 999,
                    $programName ?? '',
                    $typeOrder,
                    $subjectOrder
                );
            })
            ->values();

        return view('admin.centre.packs.index', [
            'packs' => $packs,

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
            'price' => ['required', 'numeric', 'min:0.01'],
        ]);

        Pack::create([
            'uuid' => (string) Str::uuid(),
            'type' => $data['type'],
            'semester_id' => $data['type'] === 'semestre' ? $data['semester_id'] : null,
            'subject_id' => $data['type'] === 'module' ? $data['subject_id'] : null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Le pack a été créé.');
    }

    public function edit(Pack $pack): View
    {
        return view('admin.centre.packs.edit', [
            'pack' => $pack->load(['semester.program.level', 'subject.semester.program.level']),
        ]);
    }

    public function update(Request $request, Pack $pack): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'is_active' => ['required', 'boolean'],
        ]);

        $pack->update($data);

        return redirect()
            ->route('admin.centre.packs.index')
            ->with('success', 'Le pack a été mis à jour.');
    }

    public function destroy(Pack $pack): RedirectResponse
    {
        $activeCount = $pack->enrollments()->where('status', 'active')->count();

        $pack->delete();

        $message = $activeCount > 0
            ? "Le pack a été supprimé (attention : {$activeCount} étudiant(s) avaient une inscription active dessus)."
            : 'Le pack a été supprimé.';

        return redirect()
            ->route('admin.centre.packs.index')
            ->with('success', $message);
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

    /**
     * Génère automatiquement un pack "semestre" pour chaque semestre actif
     * et un pack "module" pour chaque matière active, sans dupliquer
     * les packs déjà créés manuellement.
     */
    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_semester_price' => ['required', 'numeric', 'min:0.01'],
            'default_module_price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $semesterCount = 0;
        $moduleCount = 0;

        foreach (Semester::with('program.level')->where('is_active', true)->get() as $semester) {
            $pack = Pack::firstOrNew([
                'type' => 'semestre',
                'semester_id' => $semester->id,
            ]);

            if (! $pack->exists) {
                $pack->uuid = (string) Str::uuid();
                $pack->name = trim(sprintf(
                    '%s — %s — %s',
                    $semester->program?->level?->name,
                    $semester->program?->name,
                    $semester->name
                ), ' —');
                $pack->price = $data['default_semester_price'];
                $pack->is_active = true;
                $pack->save();
                $semesterCount++;
            }
        }

        foreach (Subject::with('semester.program.level')->where('is_active', true)->get() as $subject) {
            $pack = Pack::firstOrNew([
                'type' => 'module',
                'subject_id' => $subject->id,
            ]);

            if (! $pack->exists) {
                $pack->uuid = (string) Str::uuid();
                $pack->name = trim(sprintf(
                    '%s — %s — %s',
                    $subject->semester?->program?->level?->name,
                    $subject->semester?->name,
                    $subject->name
                ), ' —');
                $pack->price = $data['default_module_price'];
                $pack->is_active = true;
                $pack->save();
                $moduleCount++;
            }
        }

        return back()->with(
            'success',
            "Génération automatique terminée : {$semesterCount} pack(s) semestre et {$moduleCount} pack(s) module créés (les packs existants n'ont pas été dupliqués)."
        );
    }

    public function destroyBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pack_ids' => ['required', 'array'],
            'pack_ids.*' => ['exists:packs,id'],
        ]);

        $count = Pack::whereIn('id', $data['pack_ids'])->count();

        Pack::whereIn('id', $data['pack_ids'])->delete();

        return back()->with('success', "{$count} pack(s) supprimé(s).");
    }
}
