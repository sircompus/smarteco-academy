@extends('layouts.admin')

@section('title', 'Inscriptions & paiements formations')
@section('page-title', 'Inscriptions & paiements formations')

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

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
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
                            {{ $enrollment->training->title }}
                            — {{ $enrollment->session->title }}
                        </p>
                    </div>

                    <p class="text-xs text-gray-400">
                        {{ $enrollment->created_at->format('d/m/Y') }}
                    </p>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    @if (! $enrollment->requiresPayment())
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">
                            Formation gratuite — aucun paiement requis
                        </span>
                    @else
                        @if ($enrollment->session->isMonthly())
                            <p class="mb-2 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                <span>
                                    Facturation mensuelle — {{ $enrollment->monthsElapsed() }} mois écoulé(s)
                                    depuis le {{ ($enrollment->enrolled_at ?? $enrollment->created_at)->format('d/m/Y') }}
                                    × {{ number_format($enrollment->session->price, 2) }} DH/mois
                                </span>

                                @if ($enrollment->isPaused())
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 font-semibold text-blue-700">
                                        En pause depuis le {{ $enrollment->paused_at->format('d/m/Y') }}
                                    </span>
                                @endif

                                <form method="POST" action="{{ route('admin.trainings.enrollments.toggle-pause', $enrollment) }}">
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
                                Montant dû{{ $enrollment->session->isMonthly() ? ' (cumulé)' : '' }} :
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
                                <form method="POST" action="{{ route('admin.trainings.enrollments.reminder', $enrollment) }}">
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
                                            <span class="font-semibold">{{ number_format($payment->amount, 2) }} DH</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('admin.trainings.enrollments.payments.store', $enrollment) }}"
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
                                    <input name="note" placeholder="Ex : mois de septembre" class="mt-1 block w-full rounded-lg border-gray-300">
                                </div>

                                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                                    Ajouter le versement
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
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
