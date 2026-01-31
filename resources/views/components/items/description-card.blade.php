@props([
    'translations',
    'descriptionData',
])

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        Description
    </summary>
    <div class="collapse-content">
        <div class="space-y-6">
            <section class="text-sm text-base-content/80">
                @if (! empty($translations))
                    <div class="grid grid-cols-1 gap-3 sm:gap-4">
                        @foreach ($translations as $locale => $translation)
                            @php
                                $label = is_string($locale)
                                    ? (\App\Models\System\Language::LABEL_MAP[$locale] ?? 'Translation '.$loop->iteration)
                                    : 'Translation '.$loop->iteration;
                                $translationText = is_string($translation)
                                    ? $translation
                                    : json_encode($translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            @endphp
                            <div class="card border border-base-300 bg-base-100 shadow p-3 sm:p-4">
                                <div class="space-y-3">
                                    <span class="badge badge-outline text-xs">{{ $label }}</span>
                                    @if ($translationText)
                                        <div class="text-sm leading-relaxed text-base-content/80 max-h-40 overflow-y-auto wrap-break-word sm:max-h-none sm:overflow-visible">
                                            {!! nl2br(e($translationText)) !!}
                                        </div>
                                        @if (in_array($label, ['German', 'Chinese'], true))
                                            <div class="text-xs text-base-content/70 mt-4">
                                                {{ $label ?? $locale }} translation from
                                                <a class="link"
                                                    href="{{ config("translations.sources_git.".substr($locale, 2)) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer nofollow"
                                                    referrerpolicy="no-referrer">{{ config("translations.sources_git.".substr($locale, 0, 2)) }}</a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-sm text-base-content/70">No content available.</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-base-content/70">No translations available.</div>
                @endif
            </section>

            <section class="text-sm text-base-content/80">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Description Data</h3>
                @if (is_array($descriptionData) && $descriptionData !== [])
                    <div class="grid gap-2 sm:hidden">
                        @foreach ($descriptionData as $datum)
                            <div class="card border border-base-300 bg-base-100 shadow-sm">
                                <div class="card-body gap-2 p-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                        {{ $datum['name'] ?? '-' }}
                                    </div>
                                    <div class="text-sm font-medium wrap-break-word">
                                        {{ $datum['value'] ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="overflow-x-auto hidden sm:block">
                        <table class="table table-sm">
                            <caption class="sr-only">Structured description data</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Key</th>
                                    <th scope="col">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($descriptionData as $datum)
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $datum['name'] ?? '-' }}</td>
                                        <td>{{ $datum['value'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mt-1">-</div>
                @endif
            </section>
        </div>
    </div>
</details>
