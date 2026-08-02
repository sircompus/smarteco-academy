@extends('layouts.admin')

@section('title', 'Comptes Prof / Superviseur / Admin')
@section('page-title', 'Comptes Prof / Superviseur / Admin')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($pendingStudents->isNotEmpty())
        <section class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="text-lg font-bold text-amber-900">
                Comptes en attente de validation ({{ $pendingStudents->count() }})
            </h2>

            <p class="mt-1 text-sm text-amber-700">
                Ces étudiants/stagiaires se sont inscrits mais ne peuvent pas encore se connecter.
            </p>

            <div class="mt-4 space-y-2">
                @foreach ($pendingStudents as $student)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4">
                        <div>
                            <p class="font-medium">{{ $student->name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $student->email }}
                                · inscrit le {{ $student->created_at->format('d/m/Y') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.users.toggle-active', $student) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white">
                                Valider ce compte
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Créer un compte</h2>

        <p class="mt-1 text-sm text-gray-500">
            Les comptes Étudiant et Stagiaire se créent via l'inscription publique.
            Cette page sert uniquement à créer des comptes Admin, Superviseur ou Professeur.
        </p>

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
            @csrf

            <div>
                <label class="text-sm font-medium">Nom complet</label>
                <input name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="text-sm font-medium">Adresse e-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
            </div>

            <div>
                <label class="text-sm font-medium">Mot de passe</label>
                <input type="password" name="password" class="mt-1 block w-full rounded-lg border-gray-300" required minlength="8">
            </div>

            <div>
                <label class="text-sm font-medium">Rôle</label>
                <select name="role" class="mt-1 block w-full rounded-lg border-gray-300" required>
                    <option value="">Choisir un rôle</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                    Créer le compte
                </button>
            </div>
        </form>
    </section>

    <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="border-b border-gray-100 p-6">
            <h2 class="text-lg font-bold">Tous les comptes</h2>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-4">Nom</th>
                    <th class="px-6 py-4">E-mail</th>
                    <th class="px-6 py-4">Rôles</th>
                    <th class="px-6 py-4">Statut</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-6 py-4 font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>

                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ $role->display_name }}

                                        @if (in_array($role->name, ['admin', 'superviseur', 'professeur']))
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.roles.destroy', [$user, $role]) }}"
                                                onsubmit="return confirm('Retirer ce rôle à {{ $user->name }} ?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-indigo-400 hover:text-indigo-700">×</button>
                                            </form>
                                        @endif
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Aucun rôle</span>
                                @endforelse
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $user->is_active ? 'Actif' : 'Désactivé' }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700">
                                    {{ $user->is_active ? 'Désactiver' : 'Réactiver' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-6">
            {{ $users->links() }}
        </div>
    </section>
@endsection
