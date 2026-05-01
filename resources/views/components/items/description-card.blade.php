@props([
    'translations',
])

@php
    $translationEntries = [];

    if (is_array($translations)) {
        foreach ($translations as $locale => $translation) {
            $translationEntries[] = [
                'label' => is_string($locale)
                    ? (\App\Models\System\Language::LABEL_MAP[$locale] ?? 'Translation '.(count($translationEntries) + 1))
                    : 'Translation '.(count($translationEntries) + 1),
                'locale' => is_string($locale) ? $locale : null,
                'text' => is_string($translation)
                    ? $translation
                    : json_encode($translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }
    } elseif (is_string($translations) && trim($translations) !== '') {
        $translationEntries[] = [
            'label' => 'Description',
            'locale' => null,
            'text' => $translations,
        ];
    }
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        Description
    </summary>
    <div class="collapse-content">
        @if ($translationEntries !== [])
            <div class="grid grid-cols-1 gap-3 sm:gap-4">
                @foreach ($translationEntries as $entry)
                    <div class="card border border-base-300 bg-base-100 shadow-sm">
                        <div class="card-body gap-4 p-4">
                            <span class="badge badge-outline text-xs">{{ $entry['label'] }}</span>

                            @if ($entry['text'])
                                <div class="text-sm leading-relaxed text-emphasis wrap-break-word whitespace-pre-line">
                                    {!! nl2br(e($entry['text'])) !!}
                                </div>

                                @if (in_array($entry['label'], ['German', 'Chinese'], true) && is_string($entry['locale']))
                                    <div class="text-xs text-subtle">
                                        {{ $entry['label'] }} translation from
                                        <a
                                            class="link"
                                            href="{{ config('translations.sources_git.'.substr($entry['locale'], 2)) }}"
                                            target="_blank"
                                            rel="noopener noreferrer nofollow"
                                            referrerpolicy="no-referrer"
                                        >{{ config('translations.sources_git.'.substr($entry['locale'], 0, 2)) }}</a>
                                    </div>
                                @endif
                            @else
                                <div class="text-sm text-subtle">No content available.</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-sm text-subtle">No translations available.</div>
        @endif
    </div>
</details>
