@extends('layouts.admin')

@section('title', 'Reçu de paiement')
@section('page-title', 'Reçu de paiement')

@push('styles')
    <style>
        @media print {
            @page {
                size: A5;
                margin: 10mm;
            }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-end print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-sm print:mx-0 print:max-w-none print:border-0 print:p-0 print:shadow-none">
        <div class="text-center">
            <img
                src="{{ asset('images/smarteco-logo.png') }}"
                alt="SmartEco Academy"
                class="mx-auto h-16 w-auto"
            >

            <h1 class="mt-4 text-lg font-bold uppercase tracking-wide text-gray-900">
                Reçu de paiement
            </h1>
        </div>

        <div class="mt-6 flex items-center justify-between border-y border-gray-200 py-3 text-xs text-gray-500">
            <span>Reçu N° REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
            <span>{{ $payment->paid_at->format('d/m/Y') }}</span>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Reçu de</p>
                <p class="mt-1 font-medium text-gray-900">{{ $enrollment->user->name }}</p>
                <p class="text-xs text-gray-500">{{ $enrollment->user->email }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Pour</p>
                <p class="mt-1 font-medium text-gray-900">{{ $enrollment->training->title }}</p>
                @if ($enrollment->session->isMonthly())
                    <p class="text-xs text-gray-500">Facturation mensuelle</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Encaissé par</p>
                <p class="mt-1 font-medium text-gray-900">
                    {{ $payment->recordedBy?->name ?? 'Non renseigné' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Reçu imprimé par</p>
                <p class="mt-1 font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-3 py-2">Description</th>
                        <th class="px-3 py-2 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-gray-100">
                        <td class="px-3 py-2">
                            Versement — {{ $enrollment->training->title }}
                            @if ($payment->note)
                                <br><span class="text-xs text-gray-400">{{ $payment->note }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-semibold">
                            {{ number_format($payment->amount, 2) }} DH
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-5 space-y-1 text-right">
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

        <div class="mt-8 border-t border-gray-200 pt-4 text-center text-xs text-gray-400">
            <p>SmartEco Academy — Document généré automatiquement, valable comme preuve de paiement.</p>
        </div>
    </div>
@endsection
