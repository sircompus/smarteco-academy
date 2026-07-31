@extends('layouts.admin')

@section('title', 'Reçu de paiement')
@section('page-title', 'Reçu de paiement')

@section('content')
    <div class="mb-6 flex justify-end print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-10 print:border-0 print:p-0 print:shadow-none">
        <div class="flex items-start justify-between border-b border-gray-200 pb-6">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">
                    SE
                </div>

                <div>
                    <p class="text-lg font-bold text-gray-900">SmartEco Academy</p>
                    <p class="text-xs text-gray-500">Apprendre, créer et progresser</p>
                </div>
            </div>

            <div class="text-right">
                <p class="text-sm font-semibold text-gray-900">
                    Reçu N° REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ $payment->paid_at->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-6 text-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Reçu de</p>
                <p class="mt-1 font-medium text-gray-900">{{ $enrollment->user->name }}</p>
                <p class="text-gray-500">{{ $enrollment->user->email }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Pour</p>
                <p class="mt-1 font-medium text-gray-900">{{ $enrollment->training->title }}</p>
                @if ($enrollment->session->isMonthly())
                    <p class="text-gray-500">Facturation mensuelle</p>
                @endif
            </div>
        </div>

        <div class="mt-8 overflow-hidden rounded-xl border border-gray-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3">
                            Versement — {{ $enrollment->training->title }}
                            @if ($payment->note)
                                <br><span class="text-xs text-gray-400">{{ $payment->note }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            {{ number_format($payment->amount, 2) }} DH
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 space-y-1 text-right text-sm">
            <p class="text-gray-500">
                Montant dû total (cumulé) :
                <span class="font-medium text-gray-900">{{ number_format($enrollment->current_amount_due, 2) }} DH</span>
            </p>
            <p class="text-gray-500">
                Total versé à ce jour :
                <span class="font-medium text-gray-900">{{ number_format($enrollment->amount_paid, 2) }} DH</span>
            </p>
            <p class="text-base font-bold {{ $enrollment->isFullyPaid() ? 'text-green-700' : 'text-amber-700' }}">
                Solde restant : {{ number_format($enrollment->amount_remaining, 2) }} DH
            </p>
        </div>

        <div class="mt-10 border-t border-gray-200 pt-6 text-xs text-gray-400">
            <p>Reçu généré le {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->name }}.</p>
            <p class="mt-1">SmartEco Academy — Document généré automatiquement, valable comme preuve de paiement.</p>
        </div>
    </div>
@endsection
