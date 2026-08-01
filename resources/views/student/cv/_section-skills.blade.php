<section class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Compétences</h2>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($profile->skills as $skill)
            <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                <span class="font-medium text-indigo-700">{{ $skill->name }}</span>
                <span class="text-xs text-indigo-400">({{ $skill->level_label }})</span>
                <form method="POST" action="{{ route('student.cv.skills.destroy', $skill) }}">
                    @csrf @method('DELETE')
                    <button class="text-indigo-400 hover:text-red-600">×</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.cv.skills.store') }}" class="mt-4 flex flex-wrap gap-2">
        @csrf
        <input name="name" placeholder="Ex : Excel, Comptabilité..." class="rounded-lg border-gray-300" required>
        <select name="level" class="rounded-lg border-gray-300">
            <option value="debutant">Débutant</option>
            <option value="intermediaire" selected>Intermédiaire</option>
            <option value="avance">Avancé</option>
            <option value="expert">Expert</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter</button>
    </form>
</section>
