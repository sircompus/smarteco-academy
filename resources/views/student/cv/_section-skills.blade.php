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
        <input name="name" list="skills-suggestions" placeholder="Ex : Excel, Comptabilité..." class="rounded-lg border-gray-300" required>

        <datalist id="skills-suggestions">
            {{-- Bureautique / gestion --}}
            <option value="Excel avancé">
            <option value="Word">
            <option value="PowerPoint">
            <option value="Sage Comptabilité">
            <option value="SAP">
            <option value="Comptabilité générale">
            <option value="Comptabilité analytique">
            <option value="Fiscalité">
            <option value="Contrôle de gestion">
            <option value="Analyse financière">
            <option value="Gestion budgétaire">
            <option value="Audit">
            {{-- Commerce / marketing --}}
            <option value="Marketing digital">
            <option value="Négociation commerciale">
            <option value="Relation client">
            <option value="Étude de marché">
            <option value="Réseaux sociaux">
            <option value="SEO / référencement">
            {{-- RH --}}
            <option value="Gestion de la paie">
            <option value="Recrutement">
            <option value="Droit du travail">
            <option value="Gestion des conflits">
            {{-- Économétrie / data --}}
            <option value="Statistiques">
            <option value="Analyse de données">
            <option value="Python">
            <option value="R (langage statistique)">
            <option value="SPSS">
            <option value="Économétrie appliquée">
            {{-- Transversal --}}
            <option value="Gestion de projet">
            <option value="Travail d'équipe">
            <option value="Communication">
            <option value="Rédaction professionnelle">
            <option value="Intelligence artificielle (bases)">
            <option value="Anglais des affaires">
        </datalist>

        <select name="level" class="rounded-lg border-gray-300">
            <option value="debutant">Débutant</option>
            <option value="intermediaire" selected>Intermédiaire</option>
            <option value="avance">Avancé</option>
            <option value="expert">Expert</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold text-white">+ Ajouter</button>
    </form>
</section>
