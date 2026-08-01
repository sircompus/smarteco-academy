@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV')
@section('page-title', 'Mon CV — Modèle classique')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
        }

        .cv-classique, .cv-classique * {
            font-family: 'Times New Roman', Times, serif !important;
        }

        .cv-classique {
            font-size: 12pt;
            line-height: 1.5;
        }

        .cv-classique h1 {
            font-size: 16pt;
        }

        .cv-classique h2 {
            font-size: 16pt;
        }
    </style>
@endpush

@section('content')
    <div class="mb-4 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="cv-classique mx-auto max-w-3xl bg-white p-10 text-gray-900 print:p-0 print:shadow-none">

        <div class="border-b-2 border-gray-800 pb-4">
            <h1 class="font-bold text-gray-900">{{ $profile->full_name }}</h1>

            @if ($profile->headline)
                <p class="mt-1">{{ $profile->headline }}</p>
            @endif

            <p class="mt-2">
                @if ($profile->email) {{ $profile->email }} @endif
                @if ($profile->phone) — {{ $profile->phone }} @endif
                @if ($profile->address) — {{ $profile->address }} @endif
            </p>

            @if ($profile->linkedin_url)
                <p>{{ $profile->linkedin_url }}</p>
            @endif
        </div>

        @if (filled($profile->effective_summary))
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Profil</h2>
                <p class="mt-1">{{ $profile->effective_summary }}</p>
            </div>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Expérience professionnelle</h2>

                @foreach ($profile->experiences as $exp)
                    <div class="mt-2">
                        <p class="font-bold">
                            {{ $exp->position }} — {{ $exp->company }}
                            <span class="font-normal">
                                ({{ $exp->start_date?->format('m/Y') }} – {{ $exp->is_current ? 'Présent' : $exp->end_date?->format('m/Y') }})
                            </span>
                        </p>

                        @if ($exp->location)
                            <p>{{ $exp->location }}</p>
                        @endif

                        @if ($exp->description)
                            <p>{{ $exp->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($profile->educations->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Formation</h2>

                @foreach ($profile->educations as $edu)
                    <div class="mt-2">
                        <p class="font-bold">
                            {{ $edu->degree }} — {{ $edu->institution }}
                            <span class="font-normal">
                                ({{ $edu->start_date?->format('Y') }} – {{ $edu->is_current ? 'Présent' : $edu->end_date?->format('Y') }})
                            </span>
                        </p>

                        @if ($edu->field_of_study)
                            <p>{{ $edu->field_of_study }}</p>
                        @endif

                        @if ($edu->description)
                            <p>{{ $edu->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($profile->skills->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Compétences</h2>

                @php
                    $skillsByCategory = $profile->skills->groupBy(fn ($s) => $s->category ?: 'Autres');
                @endphp

                @foreach ($skillsByCategory as $category => $categorySkills)
                    <p class="mt-2 font-bold">{{ $category }} :</p>
                    <p>{{ $categorySkills->pluck('name')->implode(', ') }}</p>
                @endforeach
            </div>
        @endif

        @if ($profile->languages->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Langues</h2>
                <p>
                    @foreach ($profile->languages as $lang)
                        {{ $lang->name }} ({{ $lang->level_label }}){{ ! $loop->last ? ' — ' : '' }}
                    @endforeach
                </p>
            </div>
        @endif

        @if ($profile->certifications->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-bold uppercase text-gray-900">Certifications</h2>
                @foreach ($profile->certifications as $cert)
                    <p>
                        {{ $cert->name }}
                        @if ($cert->issuer) — {{ $cert->issuer }} @endif
                        @if ($cert->date_obtained) ({{ $cert->date_obtained->format('Y') }}) @endif
                    </p>
                @endforeach
            </div>
        @endif
    </div>
@endsection