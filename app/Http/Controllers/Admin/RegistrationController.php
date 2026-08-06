<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Services\RegistrationEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Registration::query()
            ->with([
                'user',
                'level',
                'program',
            ])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.registrations.index', [
            'registrations' => $query->paginate(20),
            'statuses' => Registration::STATUSES,
        ]);
    }

    public function show(Registration $registration): View
    {
        $registration->load([
            'user',
            'level',
            'program',
            'documents',
            'histories.changedBy',
            'reviewer',
        ]);

        return view('admin.registrations.show', [
            'registration' => $registration,
            'statuses' => Registration::STATUSES,
        ]);
    }

    public function updateStatus(
        Request $request,
        Registration $registration,
        RegistrationEmailService $emailService
    ): RedirectResponse {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Registration::STATUS_UNDER_REVIEW,
                    Registration::STATUS_INCOMPLETE,
                    Registration::STATUS_ACCEPTED,
                    Registration::STATUS_REJECTED,
                    Registration::STATUS_SUSPENDED,
                ]),
            ],
            'comment' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ]);

        $transitions = [
            Registration::STATUS_SUBMITTED => [
                Registration::STATUS_UNDER_REVIEW,
                Registration::STATUS_INCOMPLETE,
                Registration::STATUS_REJECTED,
            ],

            Registration::STATUS_UNDER_REVIEW => [
                Registration::STATUS_INCOMPLETE,
                Registration::STATUS_ACCEPTED,
                Registration::STATUS_REJECTED,
                Registration::STATUS_SUSPENDED,
            ],

            Registration::STATUS_INCOMPLETE => [
                Registration::STATUS_UNDER_REVIEW,
                Registration::STATUS_REJECTED,
            ],

            Registration::STATUS_ACCEPTED => [
                Registration::STATUS_SUSPENDED,
            ],

            Registration::STATUS_SUSPENDED => [
                Registration::STATUS_UNDER_REVIEW,
                Registration::STATUS_ACCEPTED,
                Registration::STATUS_REJECTED,
            ],
        ];

        $allowedStatuses =
            $transitions[$registration->status] ?? [];

        abort_unless(
            in_array(
                $data['status'],
                $allowedStatuses,
                true
            ),
            422,
            'Cette transition de statut n’est pas autorisée.'
        );

        if (
            in_array(
                $data['status'],
                [
                    Registration::STATUS_INCOMPLETE,
                    Registration::STATUS_REJECTED,
                    Registration::STATUS_SUSPENDED,
                ],
                true
            )
            && empty($data['comment'])
        ) {
            return back()->withErrors([
                'comment' => 'Un motif est obligatoire pour ce statut.',
            ]);
        }

        if (
            $data['status'] === Registration::STATUS_ACCEPTED
            && $registration->documents()
                ->where('is_verified', false)
                ->exists()
        ) {
            return back()->withErrors([
                'status' => 'Tous les documents doivent être validés avant l’acceptation.',
            ]);
        }

        $statusHistory = DB::transaction(function () use (
            $registration,
            $request,
            $data
        ) {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => $data['status'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'decision_reason' => $data['comment'] ?? null,
            ]);

            return $registration->histories()->create([
                'from_status' => $oldStatus,
                'to_status' => $data['status'],
                'changed_by' => $request->user()->id,
                'comment' => $data['comment'] ?? null,
            ]);
        });

        $registration->refresh();

        if (in_array($data['status'], [
            Registration::STATUS_UNDER_REVIEW,
            Registration::STATUS_INCOMPLETE,
            Registration::STATUS_ACCEPTED,
            Registration::STATUS_REJECTED,
        ], true)) {
            $emailService->sendStatusChanged(
                $registration,
                $statusHistory,
                $data['status']
            );
        }

        return back()->with(
            'success',
            'Le statut de la demande a été modifié.'
        );
    }

    public function verifyDocument(
        Request $request,
        RegistrationDocument $document
    ): RedirectResponse {
        $data = $request->validate([
            'is_verified' => [
                'required',
                'boolean',
            ],
            'admin_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $verified = (bool) $data['is_verified'];

        $document->update([
            'is_verified' => $verified,
            'verified_at' => $verified ? now() : null,
            'verified_by' => $verified
                ? $request->user()->id
                : null,
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        return back()->with(
            'success',
            'Le document a été examiné.'
        );
    }
}
