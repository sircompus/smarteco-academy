@php
    $selectedTemplate = old(
        'cv_template',
        $profile->cv_template ?: 'classique'
    );

    $isAdminBuilder = isset($targetUser);

    $classicPreviewParameters = $isAdminBuilder
        ? [
            'user' => $targetUser,
            'template' => 'classique',
        ]
        : ['template' => 'classique'];

    $modernPreviewParameters = $isAdminBuilder
        ? [
            'user' => $targetUser,
            'template' => 'moderne',
        ]
        : ['template' => 'moderne'];

    $atsPreviewParameters = $isAdminBuilder
        ? ['user' => $targetUser]
        : [];

    $cvPreviewRoute = $isAdminBuilder
        ? 'admin.cv.download.cv'
        : 'student.cv.download.cv';

    $atsPreviewRoute = $isAdminBuilder
        ? 'admin.cv.download.ats'
        : 'student.cv.download.ats';

    $classicPreviewUrl = route(
        $cvPreviewRoute,
        $classicPreviewParameters
    );

    $modernPreviewUrl = route(
        $cvPreviewRoute,
        $modernPreviewParameters
    );

    $atsPreviewUrl = route(
        $atsPreviewRoute,
        $atsPreviewParameters
    );
@endphp

<div
    class="md:col-span-2"
    data-cv-template-selector
    data-cv-live-preview
    x-data="{
        selected: @js($selectedTemplate),
        previewMode: @js($selectedTemplate),
        previewOpen: true,
        classicUrl: @js($classicPreviewUrl),
        modernUrl: @js($modernPreviewUrl),
        atsUrl: @js($atsPreviewUrl),
        selectTemplate(template) {
            this.selected = template;
            this.previewMode = template;
        },
        showAts() {
            this.previewMode = 'ats';
            this.previewOpen = true;
        },
        previewUrl() {
            if (this.previewMode === 'ats') {
                return this.atsUrl;
            }

            return this.previewMode === 'moderne'
                ? this.modernUrl
                : this.classicUrl;
        },
        previewLabel() {
            if (this.previewMode === 'ats') {
                return 'ATS';
            }

            return this.previewMode === 'moderne'
                ? 'Moderne'
                : 'Classique';
        }
    }"
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-gray-900">
                Choisir la mise en page du CV
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Sélectionnez un modèle et contrôlez immédiatement son rendu
                dans l’aperçu intégré.
            </p>
        </div>

        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
            Times New Roman
        </span>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <label
            class="group cursor-pointer rounded-2xl border-2 p-4 transition"
            :class="selected === 'classique'
                ? 'border-indigo-600 bg-indigo-50/60 shadow-sm'
                : 'border-gray-200 bg-white hover:border-indigo-300'"
            @click="selectTemplate('classique')"
        >
            <input
                type="radio"
                name="cv_template"
                value="classique"
                class="sr-only"
                x-model="selected"
                @checked($selectedTemplate === 'classique')
            >

            <div class="mx-auto aspect-[210/297] w-full max-w-[165px] overflow-hidden rounded-md border border-gray-300 bg-white p-3 shadow-sm">
                <div class="flex items-start justify-between gap-2 border-b border-gray-800 pb-2">
                    <div class="flex-1">
                        <div class="h-2.5 w-4/5 bg-gray-900"></div>
                        <div class="mt-1 h-1.5 w-3/5 bg-gray-500"></div>
                        <div class="mt-2 h-1 w-full bg-gray-300"></div>
                        <div class="mt-1 h-1 w-4/5 bg-gray-300"></div>
                    </div>

                    <div class="h-12 w-10 border border-gray-300 bg-gray-100"></div>
                </div>

                @foreach (range(1, 3) as $section)
                    <div class="mt-3">
                        <div class="h-1.5 w-2/5 bg-gray-800"></div>
                        <div class="mt-1.5 h-1 w-full bg-gray-300"></div>
                        <div class="mt-1 h-1 w-full bg-gray-300"></div>
                        <div class="mt-1 h-1 w-5/6 bg-gray-300"></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <div>
                    <p class="font-bold text-gray-900">
                        Classique
                    </p>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Une colonne, sobre, photo facultative et présentation
                        académique.
                    </p>
                </div>

                <span
                    class="hidden rounded-full bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white"
                    :class="{ '!inline-flex': selected === 'classique' }"
                >
                    Choisi
                </span>
            </div>
        </label>

        <label
            class="group cursor-pointer rounded-2xl border-2 p-4 transition"
            :class="selected === 'moderne'
                ? 'border-indigo-600 bg-indigo-50/60 shadow-sm'
                : 'border-gray-200 bg-white hover:border-indigo-300'"
            @click="selectTemplate('moderne')"
        >
            <input
                type="radio"
                name="cv_template"
                value="moderne"
                class="sr-only"
                x-model="selected"
                @checked($selectedTemplate === 'moderne')
            >

            <div class="mx-auto grid aspect-[210/297] w-full max-w-[165px] grid-cols-[34%_66%] overflow-hidden rounded-md border border-gray-300 bg-white shadow-sm">
                <div class="bg-indigo-700 p-2">
                    <div class="mx-auto h-10 w-10 rounded-full border-2 border-white/40 bg-indigo-300"></div>
                    <div class="mt-3 h-1.5 w-full bg-white"></div>
                    <div class="mt-1 h-1 w-4/5 bg-indigo-200"></div>

                    @foreach (range(1, 3) as $section)
                        <div class="mt-4">
                            <div class="h-1 w-4/5 bg-white"></div>
                            <div class="mt-1.5 h-1 w-full bg-indigo-300"></div>
                            <div class="mt-1 h-1 w-5/6 bg-indigo-300"></div>
                        </div>
                    @endforeach
                </div>

                <div class="p-2">
                    <div class="h-1.5 w-2/5 bg-indigo-700"></div>
                    <div class="mt-2 h-1 w-full bg-gray-300"></div>
                    <div class="mt-1 h-1 w-full bg-gray-300"></div>

                    @foreach (range(1, 3) as $section)
                        <div class="mt-4">
                            <div class="h-1.5 w-1/2 bg-indigo-700"></div>
                            <div class="mt-1.5 h-1 w-full bg-gray-300"></div>
                            <div class="mt-1 h-1 w-full bg-gray-300"></div>
                            <div class="mt-1 h-1 w-4/5 bg-gray-300"></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <div>
                    <p class="font-bold text-gray-900">
                        Moderne
                    </p>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Deux colonnes, accent indigo, photo facultative et
                        présentation visuelle.
                    </p>
                </div>

                <span
                    class="hidden rounded-full bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white"
                    :class="{ '!inline-flex': selected === 'moderne' }"
                >
                    Choisi
                </span>
            </div>
        </label>

        <article
            class="rounded-2xl border-2 p-4 transition"
            :class="previewMode === 'ats'
                ? 'border-green-600 bg-green-50/60 shadow-sm'
                : 'border-gray-200 bg-gray-50'"
        >
            <div class="mx-auto aspect-[210/297] w-full max-w-[165px] overflow-hidden rounded-md border border-gray-300 bg-white p-3 shadow-sm">
                <div class="h-2.5 w-4/5 bg-black"></div>
                <div class="mt-1 h-1.5 w-3/5 bg-gray-600"></div>
                <div class="mt-2 h-px w-full bg-black"></div>

                @foreach (range(1, 4) as $section)
                    <div class="mt-3">
                        <div class="h-1.5 w-1/2 bg-black"></div>
                        <div class="mt-1.5 h-1 w-full bg-gray-400"></div>
                        <div class="mt-1 h-1 w-full bg-gray-400"></div>
                        <div class="mt-1 h-1 w-5/6 bg-gray-400"></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-bold text-gray-900">
                        ATS
                    </p>

                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                        Recrutement
                    </span>
                </div>

                <p class="mt-1 text-xs leading-5 text-gray-500">
                    Une colonne, sans photo ni décoration. Cette version
                    reste indépendante du modèle principal enregistré.
                </p>
            </div>

            <button
                type="button"
                class="mt-4 inline-flex rounded-lg border border-green-200 bg-white px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-50"
                @click="showAts()"
            >
                Prévisualiser la version ATS
            </button>
        </article>
    </div>

    <section
        class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-gray-100"
        data-cv-preview-panel
    >
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 py-3">
            <div>
                <p class="font-bold text-gray-900">
                    Aperçu instantané
                    <span
                        class="ml-1 text-indigo-700"
                        x-text="previewLabel()"
                    ></span>
                </p>

                <p class="text-xs text-gray-500">
                    L’aperçu utilise les informations actuellement
                    enregistrées.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                    @click="previewOpen = ! previewOpen"
                    x-text="previewOpen ? 'Masquer' : 'Afficher'"
                ></button>

                <a
                    :href="previewUrl()"
                    target="_blank"
                    rel="noopener"
                    class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
                >
                    Ouvrir en grand
                </a>
            </div>
        </div>

        <div
            x-show="previewOpen"
            x-transition.opacity
            class="p-3 sm:p-5"
        >
            <div class="mx-auto max-w-[920px] overflow-hidden rounded-xl border border-gray-300 bg-white shadow-sm">
                <iframe
                    :src="previewUrl()"
                    :title="'Aperçu du CV ' + previewLabel()"
                    class="h-[760px] w-full bg-white"
                    loading="lazy"
                    data-cv-preview-frame
                ></iframe>
            </div>
        </div>
    </section>

    @error('cv_template')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>
