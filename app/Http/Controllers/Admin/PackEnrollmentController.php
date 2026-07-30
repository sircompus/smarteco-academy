<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackEnrollment;
use App\Models\PackPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackEnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = PackEnrollment::query()
            ->with(['user', 'pack', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.centre.pack-enrollments.index', [
            'enrollments' => $query->latest()->paginate(20)->withQueryString(),
            'statusFilter' => $request->string('status')->toString(),
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
}
