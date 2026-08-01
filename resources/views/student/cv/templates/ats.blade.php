@extends($layout ?? 'layouts.student')

@section('title', 'Mon CV — Version ATS')
@section('page-title', 'Mon CV — Version ATS')

@push('styles')
    <style>
        @media print {
            @page { size: A4; margin: 20mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-center print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-gray-800 px-5 py-3 text-sm font-semibold text-white">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-10 font-mono text-sm leading-6 text-gray-900 print:border-0 print:p-0 print:shadow-none">

        <p class="mb-4 rounded-lg bg-amber-50 p-3 font-sans text-xs text-amber-700 print:hidden">
            Cette version simplifiée (une seule colonne, sans image, sans mise en forme complexe)
            est optimisée pour être correctement lue par les logiciels de tri automatique (ATS).
        </p>

        <h1 class="text-lg font-bold uppercase">{{ $profile->full_name }}</h1>
        @if ($profile->headline)
            <p>{{ $profile->headline }}</p>
        @endif

        <p class="mt-2">
            @if ($profile->email) {{ $profile->email }} @endif
            @if ($profile->phone) | {{ $profile->phone }} @endif
            @if ($profile->address) | {{ $profile->address }} @endif
        </p>

        @if ($profile->linkedin_url) <p>LinkedIn : {{ $profile->linkedin_url }}</p> @endif
        @if ($profile->github_url) <p>GitHub : {{ $profile->github_url }}</p> @endif

        @if (filled($profile->effective_summary))
            <h2 class="mt-6 font-bold uppercase">PROFIL</h2>
            <p>{{ $profile->effective_summary }}</p>
        @endif

        @if ($profile->experiences->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">EXPERIENCE PROFESSIONNELLE</h2>
            @foreach ($profile->experiences as $exp)
                <p class="mt-3 font-bold">{{ $exp->position }} - {{ $exp->company }}</p>
                <p>
                    {{ $exp->start_date?->format('m/Y') }} -
                    {{ $exp->is_current ? 'Present' : $exp->end_date?->format('m/Y') }}
                    @if ($exp->location) | {{ $exp->location }} @endif
                </p>
                @if ($exp->description)
                    <p>{{ $exp->description }}</p>
                @endif
            @endforeach
        @endif

        @if ($profile->educations->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">FORMATION</h2>
            @foreach ($profile->educations as $edu)
                <p class="mt-3 font-bold">{{ $edu->degree }} - {{ $edu->institution }}</p>
                <p>
                    {{ $edu->field_of_study }} |
                    {{ $edu->start_date?->format('Y') }} - {{ $edu->is_current ? 'Present' : $edu->end_date?->format('Y') }}
                </p>
                @if ($edu->description)
                    <p>{{ $edu->description }}</p>
                @endif
            @endforeach
        @endif

        @if ($profile->skills->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">COMPETENCES</h2>
            @foreach ($profile->skills as $skill)
                <p>- {{ $skill->name }}</p>
            @endforeach
        @endif

        @if ($profile->languages->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">LANGUES</h2>
            <p>
                @foreach ($profile->languages as $lang)
                    {{ $lang->name }} ({{ $lang->level_label }}){{ ! $loop->last ? ', ' : '' }}
                @endforeach
            </p>
        @endif

        @if ($profile->certifications->isNotEmpty())
            <h2 class="mt-6 font-bold uppercase">CERTIFICATIONS</h2>
            @foreach ($profile->certifications as $cert)
                <p>{{ $cert->name }} @if ($cert->issuer) - {{ $cert->issuer }} @endif @if ($cert->date_obtained) ({{ $cert->date_obtained->format('Y') }}) @endif</p>
            @endforeach
        @endif
    </div>
@endsection
