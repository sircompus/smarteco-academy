<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Rôles que l'admin est autorisé à attribuer depuis ce formulaire.
     * (etudiant / stagiaire restent réservés à l'inscription publique.)
     */
    private const ASSIGNABLE_ROLES = ['admin', 'superviseur', 'professeur'];

    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->orderByDesc('created_at')
            ->paginate(20);

        $roles = Role::query()
            ->whereIn('name', self::ASSIGNABLE_ROLES)
            ->orderBy('name')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:'.implode(',', self::ASSIGNABLE_ROLES)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::where('name', $data['role'])->firstOrFail();
        $user->roles()->attach($role->id);

        return back()->with(
            'success',
            "Le compte {$user->name} a été créé avec le rôle « {$role->display_name} »."
        );
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with(
            'success',
            $user->is_active ? 'Compte réactivé.' : 'Compte désactivé.'
        );
    }

    public function destroyRole(User $user, Role $role): RedirectResponse
    {
        abort_if(
            ! in_array($role->name, self::ASSIGNABLE_ROLES, true),
            403,
            'Ce rôle ne peut pas être retiré depuis cette page.'
        );

        $user->roles()->detach($role->id);

        return back()->with('success', "Le rôle « {$role->display_name} » a été retiré.");
    }
}
