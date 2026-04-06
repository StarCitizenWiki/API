@php
    $selectionGroup = is_array($aspect['selection_group'] ?? null) ? $aspect['selection_group'] : null;
    $isSelectable = $selectionGroup !== null
        && is_numeric($selectionGroup['required_count'] ?? null)
        && is_numeric($selectionGroup['option_count'] ?? null)
        && (int) $selectionGroup['required_count'] < (int) $selectionGroup['option_count'];
    $isSelected = (bool) ($aspect['is_selected'] ?? true);
    $input = is_array($aspect['input'] ?? null) ? $aspect['input'] : [];
    $inputKind = is_string($input['kind'] ?? null) ? trim($input['kind']) : '';
    $inputKindLabel = match ($inputKind) {
        'item' => 'Item',
        'resource' => 'Resource',
        default => 'Input',
    };
    $inputName = is_string($input['name'] ?? null) && trim($input['name']) !== '' ? trim($input['name']) : 'Unknown input';
    $inputMinQuality = (int) ($input['min_quality'] ?? 0);
    $inputQuantity = $input['quantity'] ?? null;
    $inputQuantityScu = $input['quantity_scu'] ?? null;
    $inputWebUrl = is_string($input['web_url'] ?? null) && trim($input['web_url']) !== '' ? trim($input['web_url']) : null;
    $cardClasses = $isSelected
        ? 'card border border-base-300 bg-base-200/60 shadow-sm'
        : 'card border border-dashed border-base-300 bg-base-100/70 opacity-70 shadow-sm';
    $selectionButtonClasses = $isSelected
        ? 'btn btn-primary btn-xs'
        : 'btn btn-outline btn-xs border-base-300 bg-base-100 text-base-content/70';
@endphp

<div class="{{ $cardClasses }}" data-aspect-card="{{ $aspectIndex }}">
    <div class="card-body gap-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 space-y-2">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-base-content/55">{{ $aspect['name'] }}</div>
                @if ($inputWebUrl)
                    <h3 class="text-lg font-semibold"><a class="link link-hover" href="{{ $inputWebUrl }}">{{ $inputName }}</a></h3>
                @else
                    <h3 class="text-lg font-semibold">{{ $inputName }}</h3>
                @endif
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-base-content/70">
                    <span>{{ $inputKindLabel }}</span>

                    @if ($inputQuantityScu !== null)
                        <span>{{ number_format((float) $inputQuantityScu, 2) }} SCU</span>
                    @elseif ($inputQuantity !== null)
                        <span>
                            {{ $inputQuantity }}
                            @if ($inputKind === 'item')
                                {{ (float) $inputQuantity === 1.0 ? 'item' : 'items' }}
                            @endif
                        </span>
                    @endif

                    @if ($inputMinQuality > 0)
                        <span>Min quality {{ $inputMinQuality }}</span>
                    @endif

                    @if (is_numeric($aspect['required_count']) && $aspect['required_count'] > 1)
                        <span>{{ $aspect['required_count'] }} required</span>
                    @endif
                </div>
            </div>

            @if ($isSelectable)
                <button
                    type="button"
                    class="{{ $selectionButtonClasses }}"
                    data-aspect-toggle="{{ $aspectIndex }}"
                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                >
                    {{ $isSelected ? 'Included' : 'Excluded' }}
                </button>
            @endif
        </div>

        <div class="rounded-box border border-base-300 bg-base-100 px-3 py-3">
            @if ($aspect['has_dynamic_modifiers'])
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-base-content/90">Quality</div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="hidden btn btn-ghost btn-xs text-base-content/40 transition-colors"
                            data-aspect-reset="{{ $aspectIndex }}"
                            aria-label="Reset {{ $aspect['name'] }} quality"
                            @if (! $isSelected) disabled @endif
                        >
                            Reset to {{ $aspect['initial_quality'] }}
                        </button>
                        <div class="badge badge-neutral badge-sm tabular-nums" data-aspect-quality-value="{{ $aspectIndex }}">
                            {{ $isSelected ? $aspect['initial_quality'] : 'Off' }}
                        </div>
                    </div>
                </div>

                <input
                    type="range"
                    min="{{ $aspect['slider_min'] }}"
                    max="{{ $aspect['slider_max'] }}"
                    value="{{ $aspect['initial_quality'] }}"
                    class="range range-primary range-sm mt-3 w-full @if (! $isSelected) opacity-50 @endif"
                    data-aspect-slider="{{ $aspectIndex }}"
                    @if (! $isSelected) disabled @endif
                />

                <div class="mt-2 flex w-full justify-between text-xs text-base-content/70">
                    <span>{{ $aspect['slider_min'] }}</span>
                    <span>Base {{ $aspect['initial_quality'] }}</span>
                    <span>{{ $aspect['slider_max'] }}</span>
                </div>
            @elseif ($aspect['has_modifiers'])
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-base-content/90">Quality</div>
                        <p class="mt-1 text-sm text-base-content/70">Fixed modifier band.</p>
                    </div>
                    <span class="badge badge-ghost badge-sm">{{ $isSelected ? 'Fixed' : 'Off' }}</span>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-base-content/90">Quality</div>
                        <p class="mt-1 text-sm text-base-content/70">No modifier data.</p>
                    </div>
                    <span class="badge badge-ghost badge-sm">{{ $isSelected ? 'None' : 'Off' }}</span>
                </div>
            @endif
        </div>

        @if ($aspect['modifiers'] !== [])
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($aspect['modifiers'] as $modifierIndex => $modifier)
                    <div
                        class="rounded-box border border-base-300 bg-base-100 px-3 py-3 transition-colors"
                        data-modifier-card="{{ $aspectIndex }}:{{ $modifierIndex }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-base-content">
                                    {{ data_get($modifier, 'label', data_get($modifier, 'property_key', 'Modifier')) }}
                                </div>
                                <div class="mt-1 text-xs text-base-content/70">
                                    {{ match (data_get($modifier, 'better_when', 'neutral')) {
                                        'higher' => 'Higher is better',
                                        'lower' => 'Lower is better',
                                        default => 'Neutral',
                                    } }}
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-sm font-semibold tabular-nums text-base-content/60" data-modifier-change="{{ $aspectIndex }}:{{ $modifierIndex }}">
                                    {{ $isSelected ? 'No change' : 'Excluded' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
