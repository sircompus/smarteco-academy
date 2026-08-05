<div
    class="md:col-span-2"
    data-cv-save-experience
    x-data="{
        dirty: false,
        saving: false,
        markDirty() {
            this.dirty = true;
        },
        submitForm() {
            this.saving = true;
            this.dirty = false;
            this.$root.closest('form').requestSubmit();
        }
    }"
    x-init="
        const form = $root.closest('form');

        form.addEventListener('input', () => markDirty());
        form.addEventListener('change', () => markDirty());

        window.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key === 's') {
                event.preventDefault();
                submitForm();
            }
        });

        window.addEventListener('beforeunload', (event) => {
            if (! dirty || saving) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });
    "
>
    <div
        class="sticky bottom-4 z-20 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-xl backdrop-blur"
    >
        <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900">
                Enregistrer le profil CV
            </p>

            <p
                class="mt-1 text-xs"
                :class="dirty ? 'text-amber-700' : 'text-gray-500'"
            >
                <span x-show="dirty">
                    Modifications non enregistrées.
                </span>

                <span x-show="! dirty">
                    Les informations enregistrées alimentent les aperçus.
                </span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span
                x-show="dirty"
                x-transition.opacity
                class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800"
            >
                À enregistrer
            </span>

            <button
                type="button"
                class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="saving"
                @click="submitForm()"
            >
                <span x-show="! saving">
                    Enregistrer et actualiser l’aperçu
                </span>

                <span x-show="saving">
                    Enregistrement…
                </span>
            </button>
        </div>
    </div>

    <p class="mt-2 text-right text-xs text-gray-400">
        Raccourci clavier : Ctrl + S
    </p>
</div>
