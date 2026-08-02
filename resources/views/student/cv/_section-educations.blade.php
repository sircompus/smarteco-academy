<section id="educations" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Formation</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->educations as $education)
            <form
                method="POST"
                action="{{ route("{$routePrefix}.educations.update", $education) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="institution" value="{{ $education->institution }}" placeholder="Établissement" class="rounded-lg border-gray-300" required>
                <input name="degree" value="{{ $education->degree }}" placeholder="Diplôme" class="rounded-lg border-gray-300">
                <input name="field_of_study" value="{{ $education->field_of_study }}" placeholder="Domaine" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $education->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $education->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2">{{ $education->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($education->is_current)>
                    En cours
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette formation ?')) document.getElementById('del-edu-{{ $education->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-edu-{{ $education->id }}" method="POST" action="{{ route("{$routePrefix}.educations.destroy", $education) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.educations.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="institution" placeholder="Établissement" class="rounded-lg border-gray-300" required>
        <input name="degree" placeholder="Diplôme" class="rounded-lg border-gray-300">
        <input name="field_of_study" placeholder="Domaine" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="2" placeholder="Description (optionnel)" class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            En cours
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une formation
        </button>
    </form>
</section>
