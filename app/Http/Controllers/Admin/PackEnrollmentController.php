<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentReminderMail;
use App\Models\PackEnrollment;
use App\Models\PackPayment;
use App\Models\PackPaymentReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackEnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = PackEnrollment::query()
            ->with(['user', 'pack', 'payments', 'reminders']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('unpaid')) {
            $query->where('status', 'active')
                ->whereNotNull('amount_due')
                ->where('amount_due', '>', 0);
        }

        $enrollments = $query->latest()->paginate(20)->withQueryString();

        if ($request->boolean('unpaid')) {
            $enrollments->setCollection(
                $enrollments->getCollection()->filter(
                    fn (PackEnrollment $enrollment) => ! $enrollment->isFullyPaid()
                )
            );
        }

        return view('admin.centre.pack-enrollments.index', [
            'enrollments' => $enrollments,
            'statusFilter' => $request->string('status')->toString(),
            'unpaidFilter' => $request->boolean('unpaid'),
        ]);
    }

    public function storePayment(Request $request, PackEnrollment $packEnrollment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        PackPayment::create([
            'uuid' => (string) Str::uuid(),
            'pack_enrollment_id' => $packEnrollment->id,
            'recorded_by' => Auth::id(),
            'amount' => $data['amount'],
            'paid_at' => $data['paid_at'],
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Versement enregistré.');
    }

    public function sendReminder(PackEnrollment $packEnrollment): RedirectResponse
    {
        abort_if(
            ! $packEnrollment->requiresPayment() || $packEnrollment->isFullyPaid(),
            422,
            'Cette inscription ne nécessite pas de relance (déjà soldée ou gratuite).'
        );

        Mail::to($packEnrollment->user->email)
            ->send(new PaymentReminderMail($packEnrollment));

        PackPaymentReminder::create([
            'uuid' => (string) Str::uuid(),
            'pack_enrollment_id' => $packEnrollment->id,
            'sent_by' => Auth::id(),
            'amount_remaining_at_time' => $packEnrollment->amount_remaining,
            'sent_at' => now(),
        ]);

        return back()->with('success', "Relance envoyée à {$packEnrollment->user->name}.");
    }

    public function togglePause(PackEnrollment $packEnrollment): RedirectResponse
    {
        if ($packEnrollment->isPaused()) {
            $packEnrollment->resume();
            $message = 'Le compteur mensuel a repris.';
        } else {
            $packEnrollment->pause();
            $message = 'Le compteur mensuel est en pause — le temps qui passe ne sera pas facturé.';
        }

        return back()->with('success', $message);
    }
}
