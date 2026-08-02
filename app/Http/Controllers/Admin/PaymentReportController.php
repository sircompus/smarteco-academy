<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\PackEnrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReportController extends Controller
{
    public function index(Request $request): View
    {
        $groupBy = $request->string('group_by')->toString() ?: 'filiere';
        $programId = $request->integer('program_id') ?: null;

        $query = PackEnrollment::query()
            ->where('status', 'active')
            ->with([
                'user',
                'pack.semester.program.level',
                'pack.subject.semester.program.level',
            ]);

        if ($programId) {
            $query->whereHas('pack', function ($q) use ($programId) {
                $q->where(function ($q) use ($programId) {
                    $q->whereHas('semester', fn ($q) => $q->where('academic_program_id', $programId));
                })->orWhere(function ($q) use ($programId) {
                    $q->whereHas('subject.semester', fn ($q) => $q->where('academic_program_id', $programId));
                });
            });
        }

        $enrollments = $query->get();

        $rows = $enrollments
            ->groupBy(function (PackEnrollment $enrollment) use ($groupBy) {
                if ($groupBy === 'semestre') {
                    $semester = $enrollment->pack->isTypeSemestre()
                        ? $enrollment->pack->semester
                        : $enrollment->pack->subject?->semester;

                    return $semester
                        ? $semester->program?->level?->name.' — '.$semester->program?->name.' — '.$semester->name
                        : 'Non classé';
                }

                $program = $enrollment->pack->isTypeSemestre()
                    ? $enrollment->pack->semester?->program
                    : $enrollment->pack->subject?->semester?->program;

                return $program
                    ? $program->level?->name.' — '.$program->name
                    : 'Non classé';
            })
            ->map(function ($group) {
                return [
                    'enrollments' => $group->values(),
                    'count' => $group->count(),
                    'total_due' => $group->sum(fn (PackEnrollment $e) => $e->current_amount_due),
                    'total_paid' => $group->sum(fn (PackEnrollment $e) => $e->amount_paid),
                    'total_remaining' => $group->sum(fn (PackEnrollment $e) => $e->amount_remaining),
                ];
            })
            ->sortKeys();

        $grandTotal = [
            'count' => $enrollments->count(),
            'total_due' => $enrollments->sum(fn (PackEnrollment $e) => $e->current_amount_due),
            'total_paid' => $enrollments->sum(fn (PackEnrollment $e) => $e->amount_paid),
            'total_remaining' => $enrollments->sum(fn (PackEnrollment $e) => $e->amount_remaining),
        ];

        return view('admin.centre.reports.index', [
            'rows' => $rows,
            'grandTotal' => $grandTotal,
            'groupBy' => $groupBy,
            'programId' => $programId,
            'programs' => AcademicProgram::with('level')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}
