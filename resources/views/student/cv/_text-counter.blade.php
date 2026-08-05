@php
    $counterName = $name ?? 'description';
    $counterValue = (string) ($value ?? '');
    $counterRows = (int) ($rows ?? 3);
    $counterPlaceholder = $placeholder ?? '';
    $counterLabel = $label ?? null;
    $counterHelp = $help ?? null;
    $counterContext = $context ?? 'text';
    $counterWrapperClass = $wrapperClass ?? '';
    $counterMinWords = (int) ($minWords ?? 10);
    $counterMaxWords = (int) ($maxWords ?? 80);
@endphp

<div
    class="{{ $counterWrapperClass }}"
    data-cv-writing-counter
    data-counter-context="{{ $counterContext }}"
    x-data="{
        text: @js($counterValue),
        minWords: {{ $counterMinWords }},
        maxWords: {{ $counterMaxWords }},
        wordCount() {
            const normalized = this.text.trim();

            if (! normalized) {
                return 0;
            }

            return normalized
                .split(/\s+/)
                .filter(Boolean)
                .length;
        },
        status() {
            const count = this.wordCount();

            if (count === 0) {
                return 'empty';
            }

            if (count < this.minWords) {
                return 'short';
            }

            if (count > this.maxWords) {
                return 'long';
            }

            return 'good';
        }
    }"
>
    @if ($counterLabel)
        <label
            for="{{ $counterContext }}-{{ $counterName }}"
            class="text-sm font-medium text-gray-800"
        >
            {{ $counterLabel }}
        </label>
    @endif

    <textarea
        id="{{ $counterContext }}-{{ $counterName }}"
        name="{{ $counterName }}"
        rows="{{ $counterRows }}"
        placeholder="{{ $counterPlaceholder }}"
        class="mt-1 block w-full rounded-lg border-gray-300"
        x-model="text"
    >{{ $counterValue }}</textarea>

    <div class="mt-2 flex flex-wrap items-start justify-between gap-2">
        <div class="text-xs leading-5 text-gray-500">
            @if ($counterHelp)
                <p>{{ $counterHelp }}</p>
            @endif

            <p>
                Écris des phrases claires, sans puce, sans tiret et sans
                point au début du paragraphe.
            </p>
        </div>

        <div class="shrink-0 text-right">
            <p
                class="text-xs font-bold"
                :class="{
                    'text-gray-500': status() === 'empty',
                    'text-amber-700': status() === 'short',
                    'text-red-700': status() === 'long',
                    'text-green-700': status() === 'good'
                }"
            >
                <span x-text="wordCount()"></span>
                mot(s)
            </p>

            <p class="text-[11px] text-gray-400">
                Recommandé :
                {{ $counterMinWords }} à {{ $counterMaxWords }}
            </p>
        </div>
    </div>

    <p
        class="mt-1 text-xs font-medium text-amber-700"
        x-show="status() === 'short'"
        x-cloak
    >
        Ajoute quelques informations concrètes.
    </p>

    <p
        class="mt-1 text-xs font-medium text-red-700"
        x-show="status() === 'long'"
        x-cloak
    >
        Raccourcis le texte pour garder un CV facile à lire.
    </p>

    <p
        class="mt-1 text-xs font-medium text-green-700"
        x-show="status() === 'good'"
        x-cloak
    >
        Longueur adaptée au CV.
    </p>
</div>
