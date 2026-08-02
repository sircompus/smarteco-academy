@php
    $unlocked = $hasAccess || $lesson->is_preview;
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-100 p-4 {{ $unlocked ? '' : 'opacity-60' }}">
    <div class="flex items-center gap-3">
        <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $unlocked ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-100 text-gray-400' }}">
            @if ($unlocked)
                @if ($lesson->video_url)
                    ▶
                @else
                    📄
                @endif
            @else
                🔒
            @endif
        </span>

        <div>
            <p class="text-sm font-medium text-gray-900">
                {{ $lesson->title }}
            </p>

            <p class="text-xs text-gray-400">
                @if ($lesson->duration_minutes)
                    {{ $lesson->duration_minutes }} min
                @endif

                @if ($lesson->is_preview)
                    · Aperçu gratuit
                @endif
            </p>
        </div>
    </div>

    @unless ($unlocked)
        <span class="text-xs font-semibold text-gray-400">
            Verrouillée
        </span>
    @endunless
</div>
