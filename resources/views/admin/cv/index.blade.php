@extends('layouts.admin')

@section('title', 'CV & Portfolios des étudiants')
@section('page-title', 'CV & Portfolios des étudiants')

@section('content')
    <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-4">Étudiant</th>
                    <th class="px-6 py-4">CV rempli</th>
                    <th class="px-6 py-4">Portfolio public</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-6 py-4">
                            {{ $user->name }}
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                        </td>

                        <td class="px-6 py-4">
                            @if ($user->cvProfile)
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Oui</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">Pas encore</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if ($user->cvProfile?->is_public)
                                <a href="{{ $user->cvProfile->public_url }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:underline">
                                    Voir le lien public
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Privé</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <a href="{{ route('admin.cv.show', $user) }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                                Consulter
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            Aucun étudiant trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
