<section id="skills" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Compétences</h2>

    @if ($profile->skills->isNotEmpty())
        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-400">Déjà ajoutées</p>
        <div class="mt-2 flex flex-wrap gap-2">
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
    @endif

    <form method="POST" action="{{ route('student.cv.skills.store') }}" class="mt-5 rounded-xl border border-dashed border-gray-300 p-4">
        @csrf

        <p class="text-sm font-medium text-gray-700">
            Coche toutes les compétences qui te concernent, choisis un niveau, puis valide en une seule fois.
        </p>

        @php
            $alreadyHave = $profile->skills->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();

            $suggestedByCategory = \App\Models\SkillSuggestion::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($s) => $s->category ?: 'Autres');
        @endphp

        <div class="mt-3 space-y-5">
            @foreach ($suggestedByCategory as $category => $categorySkills)
                @php
                    $visibleSkills = $categorySkills->filter(
                        fn ($s) => ! in_array(mb_strtolower($s->name), $alreadyHave, true)
                    );
                @endphp

                @if ($visibleSkills->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-indigo-600">{{ $category }}</p>

                        <div class="mt-2 grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 md:grid-cols-3">
                            @foreach ($visibleSkills as $suggestedSkill)
                                <label class="flex items-start gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="skills[]" value="{{ $suggestedSkill->name }}" class="mt-0.5 rounded border-gray-300">
                                    <span>{{ $suggestedSkill->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">Niveau (appliqué à tout ce que tu coches)</label>
                <select name="level" class="mt-1 block rounded-lg border-gray-300">
                    <option value="debutant">Débutant</option>
                    <option value="intermediaire" selected>Intermédiaire</option>
                    <option value="avance">Avancé</option>
                    <option value="expert">Expert</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500">Autre compétence (non listée)</label>
                <input name="custom_skill" placeholder="Ex : Une compétence spécifique" class="mt-1 block w-full rounded-lg border-gray-300">
            </div>

            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Ajouter les compétences cochées
            </button>
        </div>
    </form>
</section>
