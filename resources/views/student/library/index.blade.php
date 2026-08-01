@extends('layouts.student')

@section('title', 'Bibliothèque de ressources')
@section('page-title', 'Bibliothèque de ressources')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold">Choisir un module</h2>

        <form method="GET" action="{{ route('student.library.index') }}" class="mt-4">
            <select
                name="subject_id"
                onchange="this.form.submit()"
                class="block w-full max-w-xl rounded-lg border-gray-300"
            >
                <option value="">Choisir un module</option>

                @foreach ($subjects as $subject)
                    <option
                        value="{{ $subject->id }}"
                        @selected($selectedSubject && $selectedSubject->id === $subject->id)
                    >
                        {{ $subject->compact_label }} — {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </section>

    @if ($selectedSubject)
        @unless ($hasAccess)
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Tu n'as pas encore accès à ce module. Inscris-toi au pack correspondant
                depuis <a href="{{ route('student.packs.index') }}" class="font-semibold underline">Packs (semestres / modules)</a>
                pour débloquer les téléchargements.
            </div>
        @endunless

        <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">{{ $selectedSubject->name }}</h2>

            <div class="mt-4 space-y-6">
                @foreach (\App\Models\AcademicResource::TYPES as $typeKey => $typeLabel)
                    @if ($resourcesByType->has($typeKey))
                        <div>
                            <h3 class="text-sm font-bold text-gray-700">{{ $typeLabel }}</h3>

                            <div class="mt-2 space-y-2">
                                @foreach ($resourcesByType->get($typeKey) as $resource)
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-3 {{ $hasAccess ? '' : 'opacity-60' }}">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $resource->title }}</p>
                                            <p class="text-xs text-gray-400">
                                                {{ $resource->size_for_humans }}
                                                @if ($resource->professor_name)
                                                    · Prof. {{ $resource->professor_name }}
                                                @endif
                                            </p>
                                        </div>

                                        @if ($hasAccess)
                                            <a
                                                href="{{ $resource->download_url }}"
                                                target="_blank"
                                                class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white"
                                            >
                                                Télécharger
                                            </a>
                                        @else
                                            <span class="text-xs font-semibold text-gray-400">🔒 Verrouillé</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @if ($resourcesByType->isEmpty())
                    <p class="text-sm text-gray-400">Aucun document disponible pour ce module pour le moment.</p>
                @endif
            </div>
        </section>
    @endif
@endsection
