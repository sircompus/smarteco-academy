@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle moderne')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 0mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto grid max-w-3xl grid-cols-3 overflow-hidden rounded-2xl border border-gray-200 bg-white text-sm print:mx-0 print:max-w-none print:rounded-none print:border-0 print:shadow-none">

        {{-- Colonne latérale --}}
        <div class="col-span-1 bg-indigo-600 p-6 text-white">
            @if ($profile->photo_url)
                <img src="{{ $profile->photo_url }}" class="h-24 w-24 rounded-full border-4 border-white/30 object-cover">
            @endif

            <h1 class="mt-4 text-lg font-extrabold leading-tight">{{ $profile->full_name }}</h1>
            @if ($profile->headline)
                <p class="mt-1 text-xs text-indigo-100">{{ $profile->headline }}</p>
            @endif

            <div class="mt-6 space-y-1 text-xs text-indigo-100">
                @if ($profile->email) <p>{{ $profile->email }}</p> @endif
                @if ($profile->phone) <p>{{ $profile->phone }}</p> @endif
                @if ($profile->address) <p>{{ $profile->address }}</p> @endif
                @if ($profile->linkedin_url) <p class="break-all">{{ $profile->linkedin_url }}</p> @endif
            </div>

            @if ($profile->skills->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-white">Compétences</h2>
                    <div class="mt-2 space-y-2">
                        @foreach ($profile->skills as $skill)
                            <div>
                                <p class="text-xs text-indigo-100">{{ $skill->name }}</p>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                                    <div class="h-full bg-white" style="width: {{ $skill->level_percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->languages->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-white">Langues</h2>
                    <ul class="mt-2 space-y-1 text-xs text-indigo-100">
                        @foreach ($profile->languages as $lang)
                            <li>{{ $lang->name }} — {{ $lang->level_label }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Colonne principale --}}
        <div class="col-span-2 p-6">
            @if (filled($profile->effective_summary))
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Profil</h2>
                    <p class="mt-2 leading-6 text-gray-700">{{ $profile->effective_summary }}</p>
                </div>
            @endif

            @if ($profile->experiences->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Expérience</h2>
                    <div class="mt-2 space-y-4">
                        @foreach ($profile->experiences as $exp)
                            <div class="border-l-2 border-indigo-100 pl-3">
                                <p class="font-semibold text-gray-900">{{ $exp->position }}</p>
                                <p class="text-xs text-gray-500">{{ $exp->company }} · {{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }}</p>
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
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Formation</h2>
                    <div class="mt-2 space-y-3">
                        @foreach ($profile->educations as $edu)
                            <div class="border-l-2 border-indigo-100 pl-3">
                                <p class="font-semibold text-gray-900">{{ $edu->degree }}</p>
                                <p class="text-xs text-gray-500">{{ $edu->institution }} · {{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($profile->certifications->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-600">Certifications</h2>
                    <ul class="mt-2 space-y-1 text-gray-700">
                        @foreach ($profile->certifications as $cert)
                            <li>{{ $cert->name }} @if ($cert->issuer) — {{ $cert->issuer }} @endif</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
