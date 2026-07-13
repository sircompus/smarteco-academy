<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $registrations = Registration::query()
            ->where('user_id', $request->user()->id)
            ->with(['level', 'program'])
            ->latest()
            ->get();

        return view('student.registrations.index', [
            'registrations' => $registrations,
        ]);
    }

    public function create(Request $request): View
    {
        return view('student.registrations.create', [
            'levels' => AcademicLevel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),

            'programs' => AcademicProgram::query()
                ->where('is_active', true)
                ->with('level')
                ->orderBy('sort_order')
                ->get(),

            'profile' => $request->user()->profile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRegistration($request);

        $this->ensureProgramBelongsToLevel($data);

        $registration = DB::transaction(function () use (
            $data,
            $request
        ): Registration {
            $registration = Registration::create([
                ...$data,
                'user_id' => $request->user()->id,
                'status' => Registration::STATUS_DRAFT,
            ]);

            $registration->histories()->create([
                'from_status' => null,
                'to_status' => Registration::STATUS_DRAFT,
                'changed_by' => $request->user()->id,
                'comment' => 'Création de la demande.',
            ]);

            return $registration;
        });

        return redirect()
            ->route('student.registrations.show', $registration)
            ->with('success', 'Votre demande a été créée.');
    }

    public function show(
        Request $request,
        Registration $registration
    ): View {
        $this->ensureOwner($request, $registration);

        $registration->load([
            'level',
            'program',
            'documents',
            'histories.changedBy',
        ]);

        return view('student.registrations.show', [
            'registration' => $registration,
        ]);
    }

    public function edit(
        Request $request,
        Registration $registration
    ): View {
        $this->ensureOwner($request, $registration);

        abort_unless($registration->canBeEdited(), 403);

        return view('student.registrations.edit', [
            'registration' => $registration,

            'levels' => AcademicLevel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),

            'programs' => AcademicProgram::query()
                ->where('is_active', true)
                ->with('level')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function update(
        Request $request,
        Registration $registration
    ): RedirectResponse {
        $this->ensureOwner($request, $registration);

        abort_unless($registration->canBeEdited(), 403);

        $data = $this->validateRegistration($request);

        $this->ensureProgramBelongsToLevel($data);

        $registration->update($data);

        return redirect()
            ->route('student.registrations.show', $registration)
            ->with('success', 'Votre demande a été mise à jour.');
    }

    public function submit(
        Request $request,
        Registration $registration
    ): RedirectResponse {
        $this->ensureOwner($request, $registration);

        abort_unless($registration->canBeSubmitted(), 403);

        $requiredDocuments = [
            'identity',
            'diploma',
            'transcript',
        ];

        $uploadedDocuments = $registration
            ->documents()
            ->pluck('type')
            ->all();

        $missingDocuments = array_diff(
            $requiredDocuments,
            $uploadedDocuments
        );

        if (! empty($missingDocuments)) {
            return back()->withErrors([
                'documents' => 'Veuillez déposer la pièce d’identité, le diplôme et le relevé de notes.',
            ]);
        }

        DB::transaction(function () use (
            $registration,
            $request
        ): void {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => Registration::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'decision_reason' => null,
            ]);

            $registration->histories()->create([
                'from_status' => $oldStatus,
                'to_status' => Registration::STATUS_SUBMITTED,
                'changed_by' => $request->user()->id,
                'comment' => 'Soumission du dossier par l’étudiant.',
            ]);
        });

        return back()->with(
            'success',
            'Votre demande a été soumise.'
        );
    }

    private function validateRegistration(Request $request): array
    {
        return $request->validate([
            'academic_level_id' => [
                'required',
                'exists:academic_levels,id',
            ],
            'academic_program_id' => [
                'required',
                'exists:academic_programs,id',
            ],
            'academic_year' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{4}$/',
            ],
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'gender' => [
                'nullable',
                'in:homme,femme,autre',
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'country' => [
                'nullable',
                'string',
                'max:100',
            ],
            'student_note' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ]);
    }

    private function ensureProgramBelongsToLevel(array $data): void
    {
        $valid = AcademicProgram::query()
            ->where('id', $data['academic_program_id'])
            ->where(
                'academic_level_id',
                $data['academic_level_id']
            )
            ->where('is_active', true)
            ->exists();

        abort_unless(
            $valid,
            422,
            'La filière ne correspond pas au niveau sélectionné.'
        );
    }

    private function ensureOwner(
        Request $request,
        Registration $registration
    ): void {
        abort_unless(
            $registration->user_id === $request->user()->id,
            403
        );
    }
}