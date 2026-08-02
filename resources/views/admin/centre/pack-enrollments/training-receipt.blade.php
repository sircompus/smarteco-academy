@extends('layouts.admin')

@section('title', 'Reçu de paiement')
@section('page-title', 'Reçu de paiement')

@push('styles')
    <style>
        @media print {
            @page {
                size: 148mm 210mm;
                margin: 10mm;
            }
        }

        .receipt-a5 {
            width: 148mm;
            min-height: 210mm;
        }

        @media print {
            .receipt-a5 {
                width: auto;
                min-height: auto;
            }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="receipt-a5 mx-auto flex flex-col rounded-2xl border border-gray-200 bg-white p-8 text-sm print:mx-0 print:rounded-none print:border-0 print:p-0 print:shadow-none">

        {{-- En-tête : logo à gauche / titre au centre / n° + date à droite --}}
        <div class="grid grid-cols-3 items-center gap-2 border-b-2 border-indigo-600 pb-4">
            <div>
                <img
                    src="{{ asset('images/smarteco-logo.png') }}"
                    alt="SmartEco Academy"
                    class="h-12 w-auto"
                >
            </div>

            <div class="text-center">
                <h1 class="text-base font-extrabold uppercase tracking-wide text-gray-900">
                    Reçu de<br>paiement
                </h1>
            </div>

            <div class="text-right text-[11px] text-gray-500">
                <p class="font-semibold text-gray-700">
                    N° REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                </p>
                <p>{{ $payment->paid_at->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Corps : une seule colonne (format étroit) --}}
        <div class="mt-6 space-y-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">Reçu de</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $enrollment->user->name }}</p>
                <p class="text-xs text-gray-500">{{ $enrollment->user->email }}</p>
            </div>

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">Pour</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $enrollment->training->title }}</p>
                @if ($enrollment->session->isMonthly())
                    <p class="text-xs text-gray-500">Facturation mensuelle</p>
                @endif
            </div>

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">Encaissé par</p>
                <p class="mt-1 font-semibold text-gray-900">
                    {{ $payment->recordedBy?->name ?? 'Non renseigné' }}
                </p>
            </div>
        </div>

        {{-- Détail du versement --}}
        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-indigo-600 text-xs uppercase text-white">
                    <tr>
                        <th class="px-3 py-2">Description</th>
                        <th class="px-3 py-2 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-3 py-3">
                            Versement — {{ $enrollment->training->title }}
                            @if ($payment->note)
                                <br><span class="text-xs text-gray-400">{{ $payment->note }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right font-semibold">
                            {{ number_format($payment->amount, 2) }} DH
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Totaux + solde --}}
        <div class="mt-6 space-y-1 text-right text-sm">
            <p class="text-gray-500">
                Montant dû (cumulé) :
                <span class="font-medium text-gray-900">{{ number_format($enrollment->current_amount_due, 2) }} DH</span>
            </p>
            <p class="text-gray-500">
                Total versé :
                <span class="font-medium text-gray-900">{{ number_format($enrollment->amount_paid, 2) }} DH</span>
            </p>

            <div class="mt-2 inline-block rounded-lg {{ $enrollment->isFullyPaid() ? 'bg-green-50' : 'bg-amber-50' }} px-4 py-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide {{ $enrollment->isFullyPaid() ? 'text-green-700' : 'text-amber-700' }}">
                    Solde restant
                </p>
                <p class="text-lg font-extrabold {{ $enrollment->isFullyPaid() ? 'text-green-700' : 'text-amber-700' }}">
                    {{ number_format($enrollment->amount_remaining, 2) }} DH
                </p>
            </div>
        </div>

        {{-- Pied de page --}}
        <div class="mt-auto border-t border-gray-200 pt-4 text-center text-[11px] text-gray-400">
            <p class="font-medium text-gray-600">
                Reçu imprimé par {{ auth()->user()->name }} le {{ now()->format('d/m/Y à H:i') }}
            </p>
            <p class="mt-1">
                SmartEco Academy — Document généré automatiquement, valable comme preuve de paiement.
            </p>
        </div>
    </div>
@endsection
