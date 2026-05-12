@php
    use App\Support\Format;
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
@endphp

<div :class="getAspectCardClass({{ $aspectIndex }})">
    <div class="card-body gap-3 p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="flex flex-1 flex-col gap-3 lg:flex-row lg:items-start lg:gap-4">
                <div class="min-w-0 space-y-1 lg:w-1/2">
                    <div class="flex flex-wrap items-center gap-2">
                        <div
                            class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $aspect['name'] }}</div>
                        @if ($isSelectable)
                            <button
                                type="button"
                                :class="getToggleClass({{ $aspectIndex }})"
                                x-on:click="toggleSelected({{ $aspectIndex }})"
                                :aria-pressed="selectedByAspect[{{ $aspectIndex }}] ? 'true' : 'false'"
                                x-text="getToggleText({{ $aspectIndex }})"
                            ></button>
                        @endif
                    </div>
                    @if ($inputWebUrl)
                        <h3 class="text-base font-semibold leading-snug"><a class="link link-primary link-hover"
                                                                            href="{{ $inputWebUrl }}">{{ $inputName }}</a>
                        </h3>
                    @else
                        <h3 class="text-base font-semibold leading-snug"><span
                                class="link link-primary">{{ $inputName }}</span></h3>
                    @endif
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-subtle">
                        <span>{{ $inputKindLabel }}</span>

                        @if ($inputQuantityScu !== null)
                            <span>{{ Format::number((float) $inputQuantityScu, 2) }} SCU</span>
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

                @if ($aspect['has_dynamic_modifiers'])
                    <div class="min-w-0 space-y-1 lg:w-1/2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs font-semibold text-emphasis">Quality</div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    :class="getResetClass({{ $aspectIndex }})"
                                    x-on:click="resetQuality({{ $aspectIndex }})"
                                    :disabled="isResetDisabled({{ $aspectIndex }})"
                                    aria-label="Reset {{ $aspect['name'] }} quality"
                                >
                                    Reset to {{ $aspect['initial_quality'] }}
                                </button>
                                <div class="badge badge-neutral badge-sm min-w-10 tabular-nums"
                                     x-text="getQualityDisplay({{ $aspectIndex }})">
                                    {{ $isSelected ? $aspect['initial_quality'] : 'Off' }}
                                </div>
                            </div>
                        </div>
                        <input
                            type="range"
                            min="{{ $aspect['slider_min'] }}"
                            max="{{ $aspect['slider_max'] }}"
                            :value="qualityByAspect[{{ $aspectIndex }}]"
                            :class="getSliderClass({{ $aspectIndex }})"
                            x-on:input="setQuality({{ $aspectIndex }}, Number($event.target.value))"
                            :disabled="!selectedByAspect[{{ $aspectIndex }}]"
                        />
                        <div class="flex w-full justify-between text-xs text-subtle">
                            <span>{{ $aspect['slider_min'] }}</span>
                            <span>Base {{ $aspect['initial_quality'] }}</span>
                            <span>{{ $aspect['slider_max'] }}</span>
                        </div>
                    </div>
                @elseif ($aspect['has_modifiers'])
                    <div class="flex items-center gap-2 lg:w-1/2 lg:justify-end lg:pt-1">
                        <span class="text-xs text-subtle">Fixed modifier band.</span>
                        <span class="badge badge-soft badge-sm"
                              x-text="selectedByAspect[{{ $aspectIndex }}] ? 'Fixed' : 'Off'">{{ $isSelected ? 'Fixed' : 'Off' }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 lg:w-1/2 lg:justify-end lg:pt-1">
                        <span class="text-xs text-subtle">No modifier data.</span>
                        <span class="badge badge-soft badge-sm"
                              x-text="selectedByAspect[{{ $aspectIndex }}] ? 'None' : 'Off'">{{ $isSelected ? 'None' : 'Off' }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if ($aspect['modifiers'] !== [])
            <div class="grid gap-1.5 md:grid-cols-2">
                @foreach ($aspect['modifiers'] as $modifierIndex => $modifier)
                    <div
                        :class="getModifierCardClass({{ $aspectIndex }}, {{ $modifierIndex }})"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="truncate text-xs font-medium text-base-content">
                                    {{ data_get($modifier, 'label', data_get($modifier, 'property_key', 'Modifier')) }}
                                </div>
                                <div class="mt-0.5 text-xs text-subtle">
                                    {{ match (data_get($modifier, 'better_when', 'neutral')) {
                                        'higher' => 'Higher is better',
                                        'lower' => 'Lower is better',
                                        default => 'Neutral',
                                    } }}
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div :class="getModifierChangeClass({{ $aspectIndex }}, {{ $modifierIndex }})"
                                     x-text="getModifierChangeText({{ $aspectIndex }}, {{ $modifierIndex }})">
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
