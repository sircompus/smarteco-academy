<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
   public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            'unique:' . User::class,
        ],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = DB::transaction(function () use ($request): User {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->lower()->toString(),
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->profile()->create([
            'first_name' => $request->string('name')->toString(),
        ]);

        $studentRole = Role::query()
            ->where('name', 'etudiant')
            ->where('is_active', true)
            ->firstOrFail();

        $user->roles()->attach($studentRole->id);

        return $user;
    });

    event(new Registered($user));

    Auth::login($user);

    return redirect()->route('student.dashboard');
}
}
