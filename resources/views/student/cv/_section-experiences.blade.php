<section id="experiences" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Expérience professionnelle</h2>

    <div class="mt-4 space-y-4">
        @foreach ($profile->experiences as $experience)
            <form
                method="POST"
                action="{{ route(\"{$routePrefix}.experiences.update\", $experience) }}"
                class="grid gap-3 rounded-xl border border-gray-100 p-4 md:grid-cols-2"
            >
                @csrf
                @method('PATCH')

                <input name="company" value="{{ $experience->company }}" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
                <input name="position" value="{{ $experience->position }}" placeholder="Poste" class="rounded-lg border-gray-300" required>
                <input name="location" value="{{ $experience->location }}" placeholder="Lieu" class="rounded-lg border-gray-300">

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ $experience->start_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                    <input type="date" name="end_date" value="{{ $experience->end_date?->format('Y-m-d') }}" class="rounded-lg border-gray-300">
                </div>

                <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2">{{ $experience->description }}</textarea>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" @checked($experience->is_current)>
                    Poste actuel
                </label>

                <div class="flex items-center gap-2 md:col-span-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Enregistrer</button>

                    <button
                        type="button"
                        onclick="if(confirm('Supprimer cette expérience ?')) document.getElementById('del-exp-{{ $experience->id }}').submit();"
                        class="rounded-lg bg-red-50 px-4 py-2 text-xs font-semibold text-red-600"
                    >
                        Supprimer
                    </button>
                </div>
            </form>

            <form id="del-exp-{{ $experience->id }}" method="POST" action="{{ route(\"{$routePrefix}.experiences.destroy\", $experience) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    <form method="POST" action="{{ route("{$routePrefix}.experiences.store", $storeParams) }}" class="mt-4 grid gap-3 rounded-xl border border-dashed border-gray-300 p-4 md:grid-cols-2">
        @csrf
        <input name="company" placeholder="Entreprise" class="rounded-lg border-gray-300" required>
        <input name="position" placeholder="Poste" class="rounded-lg border-gray-300" required>
        <input name="location" placeholder="Lieu" class="rounded-lg border-gray-300">

        <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start_date" class="rounded-lg border-gray-300">
            <input type="date" name="end_date" class="rounded-lg border-gray-300">
        </div>

        <textarea name="description" rows="3" placeholder="Missions, réalisations..." class="rounded-lg border-gray-300 md:col-span-2"></textarea>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_current" value="1">
            Poste actuel
        </label>

        <button class="w-fit rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white md:col-span-2">
            + Ajouter une expérience
        </button>
    </form>
</section>
