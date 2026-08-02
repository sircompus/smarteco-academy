@extends('layouts.student')

@section('title', 'Packs semestres & modules')
@section('page-title', 'Packs semestres & modules')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($packs as $pack)
            @php
                $enrollment = $myEnrollments->get($pack->id);
            @endphp

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {{ $pack->type === 'semestre' ? 'Pack semestre' : 'Pack module' }}
                </span>

                <h2 class="mt-3 text-lg font-bold text-gray-900">
                    {{ $pack->name }}
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    @if ($pack->isTypeSemestre())
                        {{ $pack->semester?->program?->level?->name }}
                        — {{ $pack->semester?->program?->name }}
                        — {{ $pack->semester?->name }}
                        <br>
                        {{ $pack->subjects()->count() }} module(s) inclus
                    @else
                        {{ $pack->subject?->semester?->program?->level?->name }}
                        — {{ $pack->subject?->name }}
                    @endif
                </p>

                @if ($pack->description)
                    <p class="mt-3 text-sm text-gray-600">
                        {{ $pack->description }}
                    </p>
                @endif

                <p class="mt-4 text-xl font-bold text-gray-900">
                    {{ $pack->price ? number_format($pack->price, 2).' DH' : 'Gratuit' }}
                </p>

                <div class="mt-5">
                    @if (! $enrollment)
                        <form method="POST" action="{{ route('student.packs.enroll', $pack) }}">
                            @csrf
                            <button class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white">
                                S’inscrire
                            </button>
                        </form>
                    @elseif ($enrollment->status === 'en_attente')
                        <span class="block w-full rounded-lg bg-amber-50 px-4 py-3 text-center text-sm font-semibold text-amber-700">
                            En attente de validation
                        </span>
                    @elseif ($enrollment->status === 'active')
                        <span class="block w-full rounded-lg bg-green-50 px-4 py-3 text-center text-sm font-semibold text-green-700">
                            Inscription active
                        </span>
                    @else
                        <span class="block w-full rounded-lg bg-gray-100 px-4 py-3 text-center text-sm font-semibold text-gray-500">
                            Demande refusée
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-10 text-center shadow-sm">
                <p class="text-gray-500">
                    Aucun pack disponible pour le moment.
                </p>
            </div>
        @endforelse
    </div>
@endsection
