@props([
    'translations',
    'attributionLinks' => false,
    'removeNonEnglish' => false,
    'emptyMessage' => 'No translations available.',
])

@php
    use App\Models\System\Language;

    $translationEntries = [];

    if ($translations !== null) {
        if (is_array($translations)) {
            foreach ($translations as $locale => $translation) {
                $translationEntries[] = [
                    'label' => is_string($locale)
                        ? (Language::LABEL_MAP[$locale] ?? 'Translation '.(count($translationEntries) + 1))
                        : 'Translation '.(count($translationEntries) + 1),
                    'locale' => is_string($locale) ? $locale : null,
                    'text' => is_string($translation)
                        ? trim(html_entity_decode($translation))
                        : null,
                ];
            }

            $translationEntries = array_values(array_filter(
                $translationEntries,
                static fn (array $entry): bool => is_string($entry['text']) && trim($entry['text']) !== '',
            ));
        } elseif (is_string($translations) && trim($translations) !== '') {
            $translationEntries[] = [
                'label' => 'English',
                'locale' => null,
                'text' => trim(html_entity_decode($translations)),
            ];
        }
    }
@endphp

@if ($translationEntries !== [])
    @if (count($translationEntries) === 1)
        <div class="max-h-48 overflow-y-auto text-sm leading-relaxed whitespace-pre-line text-subtle">
            {!! e($translationEntries[0]['text']) !!}
        </div>
    @else
        <div role="tablist" class="tabs tabs-border">
            @foreach ($translationEntries as $entry)
                @php
                    $isNonEnglish = $entry['label'] !== 'English';
                    $wrapperAttrs = ($removeNonEnglish && $isNonEnglish) ? 'data-remove' : '';
                @endphp

                @if ($wrapperAttrs)
                    <div {{ $wrapperAttrs }}>
                @endif
                <input
                    type="radio"
                    name="translation_tabs_{{ $attributes->offsetExists('id') ? $attributes['id'] : crc32(implode('', array_column($translationEntries, 'label'))) }}"
                    role="tab"
                    class="tab p-0 !pr-3"
                    aria-label="{{ $entry['label'] }}"
                    {{ $loop->first ? 'checked' : '' }}
                />
                <div role="tabpanel" class="tab-content text-sm leading-relaxed whitespace-pre-line text-subtle">
                    @if ($entry['text'])
                        {!! e($entry['text']) !!}

                        @if ($attributionLinks && is_string($entry['locale']) && config('translations.sources_git.'.substr($entry['locale'], 0, 2)))
                            <div class="mt-3 text-xs text-subtle">
                                {{ $entry['label'] }} translation from
                                <a
                                    class="link"
                                    href="{{ config('translations.sources_git.'.substr($entry['locale'], 0, 2)) }}"
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
                @if ($wrapperAttrs)
                    </div>
                @endif
            @endforeach
        </div>
    @endif
@else
    <div class="text-sm text-subtle">{{ $emptyMessage }}</div>
@endif
