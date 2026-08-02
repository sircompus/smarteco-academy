<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\PackEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $packs = Pack::query()
            ->where('is_active', true)
            ->with([
                'semester.program.level',
                'subject.semester.program.level',
            ])
            ->orderBy('sort_order')
            ->get();

        $myEnrollments = $user->packEnrollments()
            ->with('pack')
            ->get()
            ->keyBy('pack_id');

        return view('student.packs.index', [
            'packs' => $packs,
            'myEnrollments' => $myEnrollments,
        ]);
    }

    public function enroll(Pack $pack): RedirectResponse
    {
        $user = Auth::user();

        $existing = PackEnrollment::query()
            ->where('user_id', $user->id)
            ->where('pack_id', $pack->id)
            ->first();

        if ($existing) {
            return back()->with('success', 'Vous avez déjà une demande pour ce pack.');
        }

        PackEnrollment::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'pack_id' => $pack->id,
            'amount_due' => $pack->price,
            'status' => 'en_attente',
        ]);

        return back()->with(
            'success',
            'Votre demande d’inscription a été envoyée, en attente de validation.'
        );
    }
}
