@extends('layouts.admin')

@section('title', 'État financier')
@section('page-title', 'État financier — Centre de formation')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm print:hidden">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-sm font-medium">Grouper par</label>
                <select name="group_by" class="mt-1 block rounded-lg border-gray-300" onchange="this.form.submit()">
                    <option value="filiere" @selected($groupBy === 'filiere')>Filière</option>
                    <option value="semestre" @selected($groupBy === 'semestre')>Semestre</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Filière (optionnel)</label>
                <select name="program_id" class="mt-1 block rounded-lg border-gray-300" onchange="this.form.submit()">
                    <option value="">Toutes les filières</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected($programId === $program->id)>
                            {{ $program->level?->name }} — {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="button" onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Imprimer / Enregistrer en PDF
            </button>
        </form>
    </section>

    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-8 print:border-0 print:p-0 print:shadow-none">
        <div class="flex items-center justify-between border-b border-gray-200 pb-6">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">
                    SE
                </div>

                <div>
                    <p class="text-lg font-bold text-gray-900">SmartEco Academy</p>
                    <p class="text-xs text-gray-500">
                        État financier — {{ $groupBy === 'semestre' ? 'par semestre' : 'par filière' }}
                        — généré le {{ now()->format('d/m/Y à H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <table class="mt-6 w-full text-left text-sm">
            <thead class="border-b border-gray-200 text-xs uppercase text-gray-500">
                <tr>
                    <th class="py-3">{{ $groupBy === 'semestre' ? 'Semestre' : 'Filière' }}</th>
                    <th class="py-3 text-right">Inscriptions</th>
                    <th class="py-3 text-right">Total dû</th>
                    <th class="py-3 text-right">Total versé</th>
                    <th class="py-3 text-right">Total restant</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $label => $row)
                    <tr>
                        <td class="py-3 font-medium">{{ $label }}</td>
                        <td class="py-3 text-right">{{ $row['count'] }}</td>
                        <td class="py-3 text-right">{{ number_format($row['total_due'], 2) }} DH</td>
                        <td class="py-3 text-right text-green-700">{{ number_format($row['total_paid'], 2) }} DH</td>
                        <td class="py-3 text-right {{ $row['total_remaining'] > 0 ? 'text-amber-700' : 'text-gray-500' }}">
                            {{ number_format($row['total_remaining'], 2) }} DH
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">
                            Aucune inscription active pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr class="border-t-2 border-gray-300 font-bold">
                    <td class="py-4">Total général</td>
                    <td class="py-4 text-right">{{ $grandTotal['count'] }}</td>
                    <td class="py-4 text-right">{{ number_format($grandTotal['total_due'], 2) }} DH</td>
                    <td class="py-4 text-right text-green-700">{{ number_format($grandTotal['total_paid'], 2) }} DH</td>
                    <td class="py-4 text-right {{ $grandTotal['total_remaining'] > 0 ? 'text-amber-700' : 'text-gray-500' }}">
                        {{ number_format($grandTotal['total_remaining'], 2) }} DH
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
