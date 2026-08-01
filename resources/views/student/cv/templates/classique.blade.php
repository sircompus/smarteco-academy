@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle classique')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-10 text-sm print:border-0 print:p-0 print:shadow-none">

        <div class="flex items-center gap-6 border-b-2 border-gray-800 pb-6">
            @if ($profile->photo_url)
                <img src="{{ $profile->photo_url }}" class="h-24 w-24 rounded-full object-cover">
            @endif

            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $profile->full_name }}</h1>
                @if ($profile->headline)
                    <p class="mt-1 text-base text-gray-600">{{ $profile->headline }}</p>
                @endif

                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                    @if ($profile->email) <span>{{ $profile->email }}</span> @endif
                    @if ($profile->phone) <span>{{ $profile->phone }}</span> @endif
                    @if ($profile->address) <span>{{ $profile->address }}</span> @endif
                    @if ($profile->linkedin_url) <span>{{ $profile->linkedin_url }}</span> @endif
                </div>
            </div>
        </div>

        @if (filled($profile->effective_summary))
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Profil</h2>
                <p class="mt-2 leading-6 text-gray-700">{{ $profile->effective_summary }}</p>
            </div>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Expérience professionnelle</h2>
                <div class="mt-2 space-y-4">
                    @foreach ($profile->experiences as $exp)
                        <div>
                            <div class="flex items-baseline justify-between">
                                <p class="font-semibold text-gray-900">{{ $exp->position }} — {{ $exp->company }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}
                                </p>
                            </div>
                            @if ($exp->location)
                                <p class="text-xs text-gray-400">{{ $exp->location }}</p>
                            @endif
                            @if ($exp->description)
                                <p class="mt-1 text-gray-700">{{ $exp->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($profile->educations->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Formation</h2>
                <div class="mt-2 space-y-3">
                    @foreach ($profile->educations as $edu)
                        <div>
                            <div class="flex items-baseline justify-between">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }} — {{ $edu->institution }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}
                                </p>
                            </div>
                            @if ($edu->field_of_study)
                                <p class="text-xs text-gray-400">{{ $edu->field_of_study }}</p>
                            @endif
                            @if ($edu->description)
                                <p class="mt-1 text-gray-700">{{ $edu->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 grid grid-cols-2 gap-6">
            @if ($profile->skills->isNotEmpty())
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Compétences</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->skills as $skill)
                            <li>{{ $skill->name }} — <span class="text-xs text-gray-400">{{ $skill->level_label }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($profile->languages->isNotEmpty())
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Langues</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — <span class="text-xs text-gray-400">{{ $lang->level_label }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if ($profile->certifications->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">Certifications</h2>
                <ul class="mt-2 space-y-1 text-gray-700">
                    @foreach ($profile->certifications as $cert)
                        <li>
                            {{ $cert->name }}
                            @if ($cert->issuer) — {{ $cert->issuer }} @endif
                            @if ($cert->date_obtained) <span class="text-xs text-gray-400">({{ $cert->date_obtained->format('Y') }})</span> @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
