<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TrainingPaymentReminderMail;
use App\Models\TrainingEnrollment;
use App\Models\TrainingPayment;
use App\Models\TrainingPaymentReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TrainingEnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = TrainingEnrollment::query()
            ->with(['user', 'training', 'session', 'payments', 'reminders']);

        if ($request->boolean('unpaid')) {
            $query->whereNotNull('amount_due')->where('amount_due', '>', 0);
        }

        $enrollments = $query->latest()->paginate(20)->withQueryString();

        if ($request->boolean('unpaid')) {
            $enrollments->setCollection(
                $enrollments->getCollection()->filter(
                    fn (TrainingEnrollment $enrollment) => ! $enrollment->isFullyPaid()
                )
            );
        }

        return view('admin.trainings.enrollments.index', [
            'enrollments' => $enrollments,
            'unpaidFilter' => $request->boolean('unpaid'),
        ]);
    }

    public function storePayment(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        TrainingPayment::create([
            'uuid' => (string) Str::uuid(),
            'training_enrollment_id' => $enrollment->id,
            'recorded_by' => Auth::id(),
            'amount' => $data['amount'],
            'paid_at' => $data['paid_at'],
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Versement enregistré.');
    }

    public function sendReminder(TrainingEnrollment $enrollment): RedirectResponse
    {
        abort_if(
            ! $enrollment->requiresPayment() || $enrollment->isFullyPaid(),
            422,
            'Cette inscription ne nécessite pas de relance (déjà soldée ou gratuite).'
        );

        Mail::to($enrollment->user->email)
            ->send(new TrainingPaymentReminderMail($enrollment));

        TrainingPaymentReminder::create([
            'uuid' => (string) Str::uuid(),
            'training_enrollment_id' => $enrollment->id,
            'sent_by' => Auth::id(),
            'amount_remaining_at_time' => $enrollment->amount_remaining,
            'sent_at' => now(),
        ]);

        return back()->with('success', "Relance envoyée à {$enrollment->user->name}.");
    }

    public function togglePause(TrainingEnrollment $enrollment): RedirectResponse
    {
        if ($enrollment->isPaused()) {
            $enrollment->resume();
            $message = 'Le compteur mensuel a repris.';
        } else {
            $enrollment->pause();
            $message = 'Le compteur mensuel est en pause — le temps qui passe ne sera pas facturé.';
        }

        return back()->with('success', $message);
    }

    public function receipt(TrainingEnrollment $enrollment, TrainingPayment $payment): View
    {
        abort_unless($payment->training_enrollment_id === $enrollment->id, 404);

        return view('admin.trainings.enrollments.receipt', [
            'enrollment' => $enrollment->load('user', 'training', 'session'),
            'payment' => $payment,
        ]);
    }
}
