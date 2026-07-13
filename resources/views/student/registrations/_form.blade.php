@csrf

@if (isset($registration))
    @method('PATCH')
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium">Niveau</label>

        <select
            name="academic_level_id"
            class="mt-1 block w-full rounded-lg border-gray-300"
            required
        >
            <option value="">Choisir le niveau</option>

            @foreach ($levels as $level)
                <option
                    value="{{ $level->id }}"
                    @selected(
                        old(
                            'academic_level_id',
                            $registration->academic_level_id ?? null
                        ) == $level->id
                    )
                >
                    {{ $level->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">Filière</label>

        <select
            name="academic_program_id"
            class="mt-1 block w-full rounded-lg border-gray-300"
            required
        >
            <option value="">Choisir la filière</option>

            @foreach ($programs as $program)
                <option
                    value="{{ $program->id }}"
                    @selected(
                        old(
                            'academic_program_id',
                            $registration->academic_program_id ?? null
                        ) == $program->id
                    )
                >
                    {{ $program->level->name }}
                    — {{ $program->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">Année universitaire</label>

        <input
            name="academic_year"
            value="{{ old(
                'academic_year',
                $registration->academic_year ?? date('Y').'-'.(date('Y') + 1)
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
            placeholder="2026-2027"
            required
        >
    </div>

    <div></div>

    <div>
        <label class="text-sm font-medium">Prénom</label>

        <input
            name="first_name"
            value="{{ old(
                'first_name',
                $registration->first_name
                    ?? $profile?->first_name
                    ?? ''
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
            required
        >
    </div>

    <div>
        <label class="text-sm font-medium">Nom</label>

        <input
            name="last_name"
            value="{{ old(
                'last_name',
                $registration->last_name
                    ?? $profile?->last_name
                    ?? ''
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
            required
        >
    </div>

    <div>
        <label class="text-sm font-medium">Téléphone</label>

        <input
            name="phone"
            value="{{ old(
                'phone',
                $registration->phone
                    ?? $profile?->phone
                    ?? ''
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
        >
    </div>

    <div>
        <label class="text-sm font-medium">Date de naissance</label>

        <input
            type="date"
            name="birth_date"
            value="{{ old(
                'birth_date',
                isset($registration)
                    ? $registration->birth_date?->format('Y-m-d')
                    : $profile?->birth_date?->format('Y-m-d')
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
        >
    </div>

    <div>
        <label class="text-sm font-medium">Genre</label>

        <select
            name="gender"
            class="mt-1 block w-full rounded-lg border-gray-300"
        >
            <option value="">Sélectionner</option>
            <option value="homme" @selected(old('gender', $registration->gender ?? $profile?->gender) === 'homme')>
                Homme
            </option>
            <option value="femme" @selected(old('gender', $registration->gender ?? $profile?->gender) === 'femme')>
                Femme
            </option>
            <option value="autre" @selected(old('gender', $registration->gender ?? $profile?->gender) === 'autre')>
                Autre
            </option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">Ville</label>

        <input
            name="city"
            value="{{ old(
                'city',
                $registration->city ?? $profile?->city ?? ''
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
        >
    </div>

    <div>
        <label class="text-sm font-medium">Pays</label>

        <input
            name="country"
            value="{{ old(
                'country',
                $registration->country ?? $profile?->country ?? ''
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
        >
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium">Adresse</label>

        <input
            name="address"
            value="{{ old(
                'address',
                $registration->address ?? $profile?->address ?? ''
            ) }}"
            class="mt-1 block w-full rounded-lg border-gray-300"
        >
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium">Note complémentaire</label>

        <textarea
            name="student_note"
            rows="4"
            class="mt-1 block w-full rounded-lg border-gray-300"
        >{{ old('student_note', $registration->student_note ?? '') }}</textarea>
    </div>
</div>

@if ($errors->any())
    <div class="mt-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<button
    class="mt-6 rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white"
>
    Enregistrer la demande
</button>