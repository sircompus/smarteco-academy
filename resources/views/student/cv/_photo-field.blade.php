<div
    class="md:col-span-2"
    x-data="{
        preview: @js($profile->photo_url),
        removed: false,
        selectPhoto(event) {
            const file = event.target.files[0];

            if (! file) {
                return;
            }

            this.removed = false;
            this.preview = URL.createObjectURL(file);
        },
        removePhoto() {
            this.preview = null;
            this.removed = true;
            this.$refs.photoInput.value = '';
        }
    }"
>
    <label class="text-sm font-medium text-gray-800">
        Photo du CV
    </label>

    <div class="mt-2 flex flex-wrap items-center gap-5 rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div
            class="flex h-32 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-white"
        >
            <template x-if="preview">
                <img
                    :src="preview"
                    alt="Aperçu de la photo"
                    class="h-full w-full object-cover"
                >
            </template>

            <template x-if="! preview">
                <span class="px-3 text-center text-xs text-gray-400">
                    Emplacement facultatif de la photo
                </span>
            </template>
        </div>

        <div class="min-w-0 flex-1">
            <input
                x-ref="photoInput"
                type="file"
                name="photo"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                @change="selectPhoto($event)"
            >

            <input
                type="hidden"
                name="remove_photo"
                :value="removed ? 1 : 0"
            >

            <div class="mt-3 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50"
                    x-show="preview"
                    @click="removePhoto()"
                >
                    Supprimer la photo
                </button>
            </div>

            <p class="mt-3 text-xs leading-5 text-gray-500">
                Photo facultative. Formats acceptés : JPG, JPEG, PNG ou
                WebP. Taille maximale : 4 Mo. La version ATS reste sans
                photo.
            </p>
        </div>
    </div>
</div>
