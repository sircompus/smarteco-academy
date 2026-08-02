$controllerPath = "C:\laragon\www\SEA\app\Http\Controllers\Admin\PaymentReportController.php"
$viewPath = "C:\laragon\www\SEA\resources\views\admin\centre\reports\index.blade.php"

$controllerContent = @'
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

'@

$viewContent = @'
@extends('layouts.admin')

@section('title', 'État financier')
@section('page-title', 'État financier — Centre de formation')

@push('styles')
    <style>
        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
@endpush

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm print:hidden">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-sm font-medium">Grouper par</label>
                <select name="group_by" class="mt-1 block rounded-lg border-gray-300" onchange="this.form.submit()">
                    <option value="filiere" @selected($groupBy === 'filiere')>Filière</option>
                    <option value="semestre" @selected($groupBy === 'semestre')>Semestre</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Filière (optionnel)</label>
                <select name="program_id" class="mt-1 block rounded-lg border-gray-300" onchange="this.form.submit()">
                    <option value="">Toutes les filières</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected($programId === $program->id)>
                            {{ $program->level?->name }} — {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="button" onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Imprimer / Enregistrer en PDF
            </button>
        </form>
    </section>

    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-8 print:border-0 print:p-0 print:shadow-none">
        {{-- En-tête --}}
        <div class="flex items-center justify-between border-b-2 border-indigo-600 pb-5">
            <div class="flex items-center gap-3">
                <img
                    src="{{ asset('images/smarteco-logo.png') }}"
                    alt="SmartEco Academy"
                    class="h-12 w-auto"
                >

                <div>
                    <p class="text-lg font-bold text-gray-900">SmartEco Academy</p>
                    <p class="text-xs text-gray-500">Centre de formation</p>
                </div>
            </div>

            <div class="text-right">
                <h1 class="text-base font-extrabold uppercase tracking-wide text-gray-900">
                    État financier
                </h1>
                <p class="text-xs text-gray-500">
                    {{ $groupBy === 'semestre' ? 'Par semestre' : 'Par filière' }}
                    @if ($programId)
                        — {{ $programs->firstWhere('id', $programId)?->name }}
                    @endif
                </p>
                <p class="text-xs text-gray-400">
                    Généré le {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->name }}
                </p>
            </div>
        </div>

        {{-- Détail par groupe --}}
        <div class="mt-6 space-y-8">
            @forelse ($rows as $label => $row)
                <div class="break-inside-avoid">
                    <div class="flex items-center justify-between rounded-t-lg bg-indigo-600 px-4 py-2 text-white">
                        <h2 class="text-sm font-bold">{{ $label }}</h2>
                        <span class="text-xs">{{ $row['count'] }} inscription(s)</span>
                    </div>

                    <table class="w-full border border-t-0 border-gray-200 text-left text-xs">
                        <thead class="bg-gray-50 uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2">Étudiant</th>
                                <th class="px-3 py-2">Pack</th>
                                <th class="px-3 py-2 text-right">Dû (cumulé)</th>
                                <th class="px-3 py-2 text-right">Versé</th>
                                <th class="px-3 py-2 text-right">Restant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($row['enrollments'] as $enrollment)
                                <tr>
                                    <td class="px-3 py-2">
                                        {{ $enrollment->user->name }}
                                        <p class="text-[10px] text-gray-400">{{ $enrollment->user->email }}</p>
                                    </td>
                                    <td class="px-3 py-2">{{ $enrollment->pack->name }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($enrollment->current_amount_due, 2) }} DH</td>
                                    <td class="px-3 py-2 text-right text-green-700">{{ number_format($enrollment->amount_paid, 2) }} DH</td>
                                    <td class="px-3 py-2 text-right {{ $enrollment->amount_remaining > 0 ? 'text-amber-700' : 'text-gray-400' }}">
                                        {{ number_format($enrollment->amount_remaining, 2) }} DH
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 bg-gray-50 font-bold">
                                <td class="px-3 py-2" colspan="2">Sous-total</td>
                                <td class="px-3 py-2 text-right">{{ number_format($row['total_due'], 2) }} DH</td>
                                <td class="px-3 py-2 text-right text-green-700">{{ number_format($row['total_paid'], 2) }} DH</td>
                                <td class="px-3 py-2 text-right {{ $row['total_remaining'] > 0 ? 'text-amber-700' : 'text-gray-400' }}">
                                    {{ number_format($row['total_remaining'], 2) }} DH
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @empty
                <p class="py-10 text-center text-gray-500">
                    Aucune inscription active pour le moment.
                </p>
            @endforelse
        </div>

        {{-- Total général --}}
        @if ($rows->isNotEmpty())
            <div class="mt-8 flex justify-end">
                <div class="w-80 rounded-xl bg-indigo-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Total général</p>

                    <div class="mt-2 space-y-1 text-sm">
                        <p class="flex justify-between text-gray-600">
                            <span>{{ $grandTotal['count'] }} inscription(s)</span>
                            <span>Dû : <strong>{{ number_format($grandTotal['total_due'], 2) }} DH</strong></span>
                        </p>
                        <p class="flex justify-between text-gray-600">
                            <span>Versé</span>
                            <span class="font-semibold text-green-700">{{ number_format($grandTotal['total_paid'], 2) }} DH</span>
                        </p>
                        <p class="flex justify-between border-t border-indigo-100 pt-1 text-base font-bold {{ $grandTotal['total_remaining'] > 0 ? 'text-amber-700' : 'text-green-700' }}">
                            <span>Restant</span>
                            <span>{{ number_format($grandTotal['total_remaining'], 2) }} DH</span>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-10 border-t border-gray-200 pt-4 text-center text-[10px] text-gray-400">
            SmartEco Academy — Document généré automatiquement.
        </div>
    </div>
@endsection

'@

[System.IO.File]::WriteAllText($controllerPath, $controllerContent, [System.Text.UTF8Encoding]::new($false))
[System.IO.File]::WriteAllText($viewPath, $viewContent, [System.Text.UTF8Encoding]::new($false))

Write-Host "Fichiers ecrits avec succes (UTF-8)." -ForegroundColor Green
Select-String -Path $viewPath -Pattern "smarteco-logo"
Select-String -Path $controllerPath -Pattern "enrollments"
