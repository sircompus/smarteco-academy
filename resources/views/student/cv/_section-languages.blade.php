<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Langues</h2>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($profile->languages as $language)
            <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                <span class="font-medium text-indigo-700">{{ $language->name }}</span>
                <span class="text-xs text-indigo-400">({{ $language->level_label }})</span>
                <form method="POST" action="{{ route('student.cv.languages.destroy', $language) }}">
                    @csrf @method('DELETE')
                    <button class="text-indigo-400 hover:text-red-600">×</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.languages.store') }}" class="mt-4 flex flex-wrap gap-2">
        @csrf
        <select name="name" class="rounded-lg border-gray-300" required>
            <option value="">Choisir une langue</option>
            <option value="Arabe">Arabe</option>
            <option value="Français">Français</option>
            <option value="Anglais">Anglais</option>
            <option value="Espagnol">Espagnol</option>
            <option value="Allemand">Allemand</option>
            <option value="Autre">Autre</option>
        </select>
        <select name="level" class="rounded-lg border-gray-300">
            <option value="debutant">Débutant</option>
            <option value="intermediaire" selected>Intermédiaire</option>
            <option value="courant">Courant</option>
            <option value="bilingue">Bilingue</option>
            <option value="natif">Langue maternelle</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter</button>
    </form>
</section>
