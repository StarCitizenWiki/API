@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $searchResultUuid = data_get($searchResult, 'uuid');
    $searchFilters = is_array(data_get($search ?? [], 'filters'))
        ? data_get($search, 'filters')
        : [];
    $resultLinkFilters = Arr::except($searchFilters, ['query']);
    $searchResultUrl = data_get($searchResult, 'web_url');

    if (is_string($searchResultUuid) && Str::isUuid($searchResultUuid)) {
        $searchResultUrl = route('web.blueprints.show', array_filter([
            'blueprint' => $searchResultUuid,
            'version' => $resolvedVersionCode,
            'filter' => $resultLinkFilters,
        ]));
    } elseif (is_string($searchResultUrl) && $searchResultUrl !== '' && $resultLinkFilters !== []) {
        $searchResultUrl = url()->query($searchResultUrl, ['filter' => $resultLinkFilters]);
    }

    $isSelectedResult = ! $isEmptyMode && $searchResultUuid === $blueprintUuid;
    $searchResultTitle = data_get($searchResult, 'output_name', 'Unknown blueprint');
    $searchResultClass = data_get($searchResult, 'output_class');
    $searchResultType = data_get($searchResult, 'output.type');
    $searchResultSubtype = data_get($searchResult, 'output.sub_type');
    $searchResultTypeLabel = collect([$searchResultType, $searchResultSubtype])
        ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
        ->implode(' / ');
    $searchResultInputCount = (int) data_get($searchResult, 'ingredient_count', 0);
    $searchResultIngredientNames = collect(data_get($searchResult, 'ingredients', []))
        ->map(static fn (mixed $ingredient): ?string => is_array($ingredient)
            && is_string(data_get($ingredient, 'name'))
            && trim((string) data_get($ingredient, 'name')) !== ''
                ? trim((string) data_get($ingredient, 'name'))
                : null)
        ->filter()
        ->unique()
        ->values();
    $searchResultIngredientPreviewCount = min(3, $searchResultIngredientNames->count());
    $searchResultIngredientPreview = $searchResultIngredientNames->take($searchResultIngredientPreviewCount)->implode(', ');
    $searchResultIngredientOverflowCount = max(0, $searchResultIngredientNames->count() - $searchResultIngredientPreviewCount);
    if ($searchResultIngredientOverflowCount > 0) {
        $searchResultIngredientPreview .= ' +'.$searchResultIngredientOverflowCount.' more';
    }
    $searchResultCraftTime = is_callable($formatCraftTime ?? null)
        ? $formatCraftTime(data_get($searchResult, 'craft_time_seconds'))
        : null;
@endphp

@if (is_string($searchResultUrl) && $searchResultUrl !== '')
    <a
        href="{{ $searchResultUrl }}"
        class="{{ $isSelectedResult
            ? 'group rounded-box border border-primary/40 bg-primary/5 px-4 py-4 shadow-sm transition hover:border-primary/50 hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30'
            : 'group rounded-box border border-base-300 bg-base-100 px-4 py-4 shadow-sm transition hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30' }}"
        @if (is_string($searchResultUuid) && trim($searchResultUuid) !== '') data-testid="blueprints-search-result-link-{{ $searchResultUuid }}" @endif
        data-blueprint-search-result-link
        data-blueprint-uuid="{{ $searchResultUuid }}"
        @if ($isSelectedResult) aria-current="page" @endif
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-base font-semibold text-base-content">{{ $searchResultTitle }}</span>
                    @if ($isSelectedResult)
                        <span class="badge badge-primary badge-sm">Selected</span>
                    @endif
                </div>

                @if (is_string($searchResultClass) && trim($searchResultClass) !== '')
                    <p class="mt-1 truncate text-xs font-mono text-muted">{{ $searchResultClass }}</p>
                @endif

                @if ($searchResultIngredientPreview !== '')
                    <p class="mt-2 text-xs text-subtle">
                        <span class="font-semibold text-emphasis">Inputs:</span>
                        {{ $searchResultIngredientPreview }}
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                @if ($searchResultTypeLabel !== '')
                    <span class="badge badge-soft badge-sm">{{ $searchResultTypeLabel }}</span>
                @endif

                <span class="badge badge-outline badge-sm">
                    {{ $searchResultInputCount }} input{{ $searchResultInputCount === 1 ? '' : 's' }}
                </span>

                @if (is_string($searchResultCraftTime) && $searchResultCraftTime !== '')
                    <span class="badge badge-outline badge-sm">{{ $searchResultCraftTime }}</span>
                @endif
            </div>
        </div>
    </a>
@endif
