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
            'label' => 'English',
            'locale' => null,
            'text' => $translations,
        ];
    }
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow w-full']) }}>
    @if ($translationEntries !== [])
        <div class="join join-vertical bg-base-100">
            @foreach ($translationEntries as $entry)
                <div class="collapse collapse-arrow join-item border border-base-300">
                    <input
                        type="radio"
                        name="desc_accordion"
                        {{ $loop->first ? 'checked' : '' }}
                    />
                    <div class="collapse-title font-semibold text-sm">
                        {{ $entry['label'] }} Description
                    </div>
                    <div class="collapse-content text-sm">
                        @if ($entry['text'])
                            {!! nl2br(e($entry['text'])) !!}

                            @if (in_array($entry['label'], ['German', 'Chinese'], true) && is_string($entry['locale']))
                                <div class="mt-3 text-xs text-subtle">
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
                            <div class="text-subtle">No content available.</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-sm text-subtle">No translations available.</div>
    @endif
</div>
