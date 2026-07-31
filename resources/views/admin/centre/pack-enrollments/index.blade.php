@extends('layouts.admin')

@section('title', 'Inscriptions & paiements')
@section('page-title', 'Inscriptions & paiements')

@section('content')
    @if (session('success'))
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            <span>{{ session('success') }}</span>

            @if (session('receiptUrl'))
                <a
                    href="{{ session('receiptUrl') }}"
                    target="_blank"
                    class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700"
                >
                    Voir / Imprimer le reçu
                </a>
            @endif
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

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-sm font-medium">Statut</label>
                <select name="status" class="mt-1 block rounded-lg border-gray-300" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" @selected($statusFilter === 'en_attente')>En attente</option>
                    <option value="active" @selected($statusFilter === 'active')>Active</option>
                    <option value="annulee" @selected($statusFilter === 'annulee')>Annulée</option>
                </select>
            </div>

            <label class="flex items-center gap-2 pb-2 text-sm">
                <input
                    type="checkbox"
                    name="unpaid"
                    value="1"
                    onchange="this.form.submit()"
                    @checked($unpaidFilter)
                    class="rounded border-gray-300"
                >
                Impayés uniquement (à relancer)
            </label>
        </form>
    </section>

    <div class="mt-8 space-y-4">
        @forelse ($enrollments as $enrollment)
            <div class="rounded-2xl bg-white p-6 shadow-sm" x-data="{ open: false }">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-bold text-gray-900">{{ $enrollment->user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $enrollment->user->email }}</p>

                        <p class="mt-2 text-sm text-gray-600">
                            {{ $enrollment->pack->name }}
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                            {{ $enrollment->status_label }}
                        </span>

                        <p class="mt-2 text-xs text-gray-400">
                            {{ $enrollment->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    @if (! $enrollment->requiresPayment())
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">
                            Pack gratuit — aucun paiement requis
                        </span>
                    @else
                        @if ($enrollment->pack->isMonthly())
                            <p class="mb-2 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                <span>
                                    Facturation mensuelle — {{ $enrollment->monthsElapsed() }} mois écoulé(s)
                                    depuis le {{ ($enrollment->activated_at ?? $enrollment->created_at)->format('d/m/Y') }}
                                    × {{ number_format($enrollment->pack->price, 2) }} DH/mois
                                </span>

                                @if ($enrollment->isPaused())
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 font-semibold text-blue-700">
                                        En pause depuis le {{ $enrollment->paused_at->format('d/m/Y') }}
                                    </span>
                                @endif

                                <form method="POST" action="{{ route('admin.centre.pack-enrollments.toggle-pause', $enrollment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="font-semibold text-indigo-600 hover:underline">
                                        {{ $enrollment->isPaused() ? 'Reprendre le compteur' : 'Mettre en pause (vacances/interruption)' }}
                                    </button>
                                </form>
                            </p>
                        @endif

                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span>
                                Montant dû{{ $enrollment->pack->isMonthly() ? ' (cumulé)' : '' }} :
                                <strong>{{ number_format($enrollment->current_amount_due, 2) }} DH</strong>
                            </span>

                            <span class="text-green-700">
                                Versé : <strong>{{ number_format($enrollment->amount_paid, 2) }} DH</strong>
                            </span>

                            <span class="{{ $enrollment->isFullyPaid() ? 'text-green-700' : 'text-amber-700' }}">
                                Restant : <strong>{{ number_format($enrollment->amount_remaining, 2) }} DH</strong>
                            </span>

                            @if ($enrollment->isFullyPaid())
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                    Soldé
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.centre.pack-enrollments.reminder', $enrollment) }}">
                                    @csrf
                                    <button class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                        Envoyer une relance
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if ($enrollment->reminders->isNotEmpty())
                            <p class="mt-2 text-xs text-gray-400">
                                Dernière relance :
                                {{ $enrollment->lastReminder()->sent_at->format('d/m/Y') }}
                                ({{ $enrollment->reminders->count() }} au total)
                            </p>
                        @endif

                        <button
                            type="button"
                            @click="open = ! open"
                            class="mt-3 text-sm font-semibold text-indigo-600 hover:underline"
                        >
                            <span x-show="!open">Voir l'historique et ajouter un versement</span>
                            <span x-show="open" x-cloak>Masquer</span>
                        </button>

                        <div x-show="open" x-cloak class="mt-4 space-y-4">
                            @if ($enrollment->payments->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach ($enrollment->payments as $payment)
                                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2 text-sm">
                                            <span>
                                                {{ $payment->paid_at->format('d/m/Y') }}
                                                @if ($payment->note)
                                                    — {{ $payment->note }}
                                                @endif
                                            </span>

                                            <div class="flex items-center gap-3">
                                                <span class="font-semibold">{{ number_format($payment->amount, 2) }} DH</span>

                                                <a
                                                    href="{{ route('admin.centre.pack-enrollments.payments.receipt', [$enrollment, $payment]) }}"
                                                    target="_blank"
                                                    class="text-xs font-semibold text-indigo-600 hover:underline"
                                                >
                                                    Reçu
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('admin.centre.pack-enrollments.payments.store', $enrollment) }}"
                                class="flex flex-wrap items-end gap-3"
                            >
                                @csrf

                                <div>
                                    <label class="text-xs font-medium text-gray-500">Montant (DH)</label>
                                    <input type="number" step="0.01" name="amount" class="mt-1 block w-32 rounded-lg border-gray-300" required>
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-gray-500">Date</label>
                                    <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" class="mt-1 block rounded-lg border-gray-300" required>
                                </div>

                                <div class="flex-1 min-w-[160px]">
                                    <label class="text-xs font-medium text-gray-500">Note (optionnel)</label>
                                    <input name="note" placeholder="Ex : 1ère tranche" class="mt-1 block w-full rounded-lg border-gray-300">
                                </div>

                                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                                    Ajouter le versement
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @if ($enrollment->status === 'en_attente')
                    <div class="mt-4 flex gap-2 border-t border-gray-100 pt-4">
                        <form method="POST" action="{{ route('admin.centre.pack-enrollments.status', $enrollment) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="active">
                            <button class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white">
                                Valider l'inscription
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.centre.pack-enrollments.status', $enrollment) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="annulee">
                            <button class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700">
                                Refuser
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
                <p class="text-gray-500">Aucune inscription pour le moment.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $enrollments->links() }}
    </div>
@endsection
