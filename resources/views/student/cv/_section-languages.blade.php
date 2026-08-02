<section id="languages" class="mt-8 rounded-2xl bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold">Langues</h2>

    @if ($profile->languages->isNotEmpty())
        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-400">Déjà ajoutées</p>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($profile->languages as $language)
                <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm">
                    <span class="font-medium text-indigo-700">{{ $language->name }}</span>
                    <span class="text-xs text-indigo-400">({{ $language->level_label }})</span>
                    <form method="POST" action="{{ route("{$routePrefix}.languages.destroy", $language) }}">
                        @csrf @method('DELETE')
                        <button class="text-indigo-400 hover:text-red-600">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route("{$routePrefix}.languages.store", $storeParams) }}" class="mt-5 rounded-xl border border-dashed border-gray-300 p-4">
        @csrf

        <p class="text-sm font-medium text-gray-700">
            Coche les langues que tu parles et choisis ton niveau pour chacune.
        </p>

        @php
            $fixedLanguages = ['Arabe', 'Français', 'Anglais', 'Espagnol', 'Allemand'];
            $alreadyHaveLang = $profile->languages->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
        @endphp

        <div class="mt-3 space-y-2">
            @foreach ($fixedLanguages as $fixedLanguage)
                @unless (in_array(mb_strtolower($fixedLanguage), $alreadyHaveLang, true))
                    <div class="flex items-center gap-3">
                        <label class="flex w-32 items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="languages[{{ $fixedLanguage }}][checked]" value="1" class="rounded border-gray-300">
                            {{ $fixedLanguage }}
                        </label>

                        <select name="languages[{{ $fixedLanguage }}][level]" class="rounded-lg border-gray-300 text-sm">
                            <option value="debutant">Débutant</option>
                            <option value="intermediaire" selected>Intermédiaire</option>
                            <option value="courant">Courant</option>
                            <option value="bilingue">Bilingue</option>
                            <option value="natif">Langue maternelle</option>
                        </select>
                    </div>
                @endunless
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4">
            <div>
                <label class="text-xs font-medium text-gray-500">Autre langue</label>
                <input name="custom_language" placeholder="Ex : Italien" class="mt-1 block rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500">Niveau</label>
                <select name="custom_language_level" class="mt-1 block rounded-lg border-gray-300">
                    <option value="debutant">Débutant</option>
                    <option value="intermediaire" selected>Intermédiaire</option>
                    <option value="courant">Courant</option>
                    <option value="bilingue">Bilingue</option>
                    <option value="natif">Langue maternelle</option>
                </select>
            </div>

            <button class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                Ajouter les langues cochées
            </button>
        </div>
    </form>
</section>
