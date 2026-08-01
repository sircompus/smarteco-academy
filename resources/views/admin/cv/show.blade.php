@extends('layouts.admin')

@section('title', 'CV de ' . $targetUser->name)
@section('page-title', 'CV de ' . $targetUser->name)

@section('content')
    @if (! $profile)
        <section class="rounded-2xl bg-white p-10 text-center shadow-sm">
            <p class="text-gray-500">
                {{ $targetUser->name }} n'a pas encore commencé son CV.
            </p>

            <a href="{{ route('admin.cv.builder.edit', $targetUser) }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Créer son CV à sa place
            </a>
        </section>
    @else
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-gray-900">{{ $profile->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $profile->headline }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="h-3 w-32 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full {{ $ats['score'] >= 80 ? 'bg-green-500' : ($ats['score'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                            style="width: {{ $ats['score'] }}%"
                        ></div>
                    </div>
                    <span class="font-bold {{ $ats['score'] >= 80 ? 'text-green-600' : ($ats['score'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ $ats['score'] }}/100
                    </span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.cv.builder.edit', $targetUser) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white">
                    Modifier à sa place
                </a>
                <a href="{{ route('admin.cv.download.cv', $targetUser) }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Voir / Imprimer le CV
                </a>
                <a href="{{ route('admin.cv.download.ats', $targetUser) }}" target="_blank" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                    Version ATS
                </a>
                @if ($profile->is_public)
                    <a href="{{ $profile->public_url }}" target="_blank" class="rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-700">
                        Portfolio public
                    </a>
                @endif
            </div>
        </section>

        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Formation ({{ $profile->educations->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @forelse ($profile->educations as $edu)
                        <li>{{ $edu->degree }} — {{ $edu->institution }}</li>
                    @empty
                        <li class="text-gray-400">Aucune</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Expérience ({{ $profile->experiences->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @forelse ($profile->experiences as $exp)
                        <li>{{ $exp->position }} — {{ $exp->company }}</li>
                    @empty
                        <li class="text-gray-400">Aucune</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Compétences ({{ $profile->skills->count() }})</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse ($profile->skills as $skill)
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $skill->name }}</span>
                    @empty
                        <span class="text-sm text-gray-400">Aucune</span>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Projets portfolio ({{ $profile->projects->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    @forelse ($profile->projects as $project)
                        <li>{{ $project->title }}</li>
                    @empty
                        <li class="text-gray-400">Aucun</li>
                    @endforelse
                </ul>
            </section>
        </div>
    @endif
@endsection
