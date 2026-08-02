@extends('layouts.admin')

@section('title', 'Gestion des inscriptions')
@section('page-title', 'Gestion des inscriptions')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-sm font-medium">Statut</label>

                <select name="status" class="mt-1 block rounded-lg border-gray-300" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>

                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </section>

    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-4">Référence</th>
                    <th class="px-6 py-4">Étudiant</th>
                    <th class="px-6 py-4">Filière</th>
                    <th class="px-6 py-4">Année</th>
                    <th class="px-6 py-4">Statut</th>
                    <th class="px-6 py-4">Date</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($registrations as $registration)
                    <tr class="cursor-pointer hover:bg-gray-50" onclick="window.location='{{ route('admin.registrations.show', $registration) }}'">
                        <td class="px-6 py-4 font-medium">{{ $registration->reference }}</td>

                        <td class="px-6 py-4">
                            {{ $registration->first_name }} {{ $registration->last_name }}
                            <p class="text-xs text-gray-400">{{ $registration->user?->email }}</p>
                        </td>

                        <td class="px-6 py-4">{{ $registration->program?->name ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $registration->academic_year }}</td>

                        <td class="px-6 py-4">
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {{ $registration->status_label }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-gray-400">
                            {{ $registration->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            Aucune demande d’inscription pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $registrations->links() }}
    </div>
@endsection
