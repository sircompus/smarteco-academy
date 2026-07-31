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

    <div class="receipt-a5 mx-auto flex flex-col rounded-2xl border border-gray-200 bg-white text-sm print:mx-0 print:rounded-none print:border-0 print:shadow-none">

        {{-- Bandeau d'en-tête coloré --}}
        <div class="rounded-t-2xl bg-indigo-600 px-7 py-5 print:rounded-none">
            <div class="flex items-center justify-between">
                <img
                    src="{{ asset('images/smarteco-logo.png') }}"
                    alt="SmartEco Academy"
                    class="h-10 w-auto rounded bg-white/90 p-1"
                >

                <div class="text-right text-[10px] text-indigo-100">
                    <p class="font-semibold text-white">
                        N° REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                    </p>
                    <p>{{ $payment->paid_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <h1 class="mt-3 text-center text-lg font-extrabold uppercase tracking-[0.2em] text-white">
                Reçu de paiement
            </h1>
        </div>

        <div class="flex flex-1 flex-col px-7 py-6">
            {{-- Corps --}}
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-dashed border-gray-200 pb-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">Reçu de</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $enrollment->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $enrollment->user->email }}</p>
                    </div>

                    <div class="text-right">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">Encaissé par</p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $payment->recordedBy?->name ?? 'Non renseigné' }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">Pour</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $enrollment->pack->name }}</p>
                    @if ($enrollment->pack->isMonthly())
                        <p class="text-xs text-gray-500">Facturation mensuelle</p>
                    @endif
                </div>
            </div>

            {{-- Détail du versement --}}
            <div class="mt-5 overflow-hidden rounded-xl border border-gray-200">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[10px] uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2">Description</th>
                            <th class="px-3 py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-gray-100">
                            <td class="px-3 py-3">
                                Versement — {{ $enrollment->pack->name }}
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

            {{-- Totaux --}}
            <div class="mt-5 space-y-1.5 rounded-xl bg-gray-50 px-4 py-4 text-right">
                <p class="text-gray-500">
                    Montant dû (cumulé) :
                    <span class="font-medium text-gray-900">{{ number_format($enrollment->current_amount_due, 2) }} DH</span>
                </p>
                <p class="text-gray-500">
                    Total versé :
                    <span class="font-medium text-gray-900">{{ number_format($enrollment->amount_paid, 2) }} DH</span>
                </p>

                <div class="mt-2 flex items-center justify-end gap-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide {{ $enrollment->isFullyPaid() ? 'text-green-700' : 'text-amber-700' }}">
                        Solde restant
                    </p>
                    <p class="text-xl font-extrabold {{ $enrollment->isFullyPaid() ? 'text-green-700' : 'text-amber-700' }}">
                        {{ number_format($enrollment->amount_remaining, 2) }} DH
                    </p>
                </div>
            </div>

            {{-- Signature / cachet --}}
            <div class="mt-8 grid grid-cols-2 gap-6">
                <div class="text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Signature de l'étudiant</p>
                    <div class="mt-8 border-t border-gray-300"></div>
                </div>

                <div class="text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Cachet SmartEco Academy</p>
                    <div class="mt-8 border-t border-gray-300"></div>
                </div>
            </div>

            {{-- Pied de page --}}
            <div class="mt-auto pt-8 text-center text-[10px] text-gray-400">
                <p class="font-medium text-gray-500">
                    Reçu imprimé par {{ auth()->user()->name }} le {{ now()->format('d/m/Y à H:i') }}
                </p>
                <p class="mt-1">
                    SmartEco Academy — Document généré automatiquement, valable comme preuve de paiement.
                </p>
            </div>
        </div>
    </div>
@endsection
