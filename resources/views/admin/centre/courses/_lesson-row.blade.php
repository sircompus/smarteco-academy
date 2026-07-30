<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-4">
    <div>
        <p class="font-medium">{{ $lesson->title }}</p>

        <p class="text-xs text-gray-400">
            @if ($lesson->duration_minutes)
                {{ $lesson->duration_minutes }} min
            @endif

            @if ($lesson->is_preview)
                · Aperçu gratuit
            @endif

            @if ($lesson->video_url)
                · Vidéo
            @endif
        </p>
    </div>

    <div class="flex items-center gap-2">
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $lesson->is_published ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $lesson->is_published ? 'Publiée' : 'Brouillon' }}
        </span>

        <form method="POST" action="{{ route('admin.centre.lessons.toggle-publish', $lesson) }}">
            @csrf
            @method('PATCH')
            <button class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700">
                {{ $lesson->is_published ? 'Dépublier' : 'Publier' }}
            </button>
        </form>

        <form
            method="POST"
            action="{{ route('admin.centre.lessons.destroy', $lesson) }}"
            onsubmit="return confirm('Supprimer cette leçon ?');"
        >
            @csrf
            @method('DELETE')
            <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">
                Supprimer
            </button>
        </form>
    </div>
</div>
