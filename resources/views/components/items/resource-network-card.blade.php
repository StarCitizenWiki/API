@props([
    'resourceNetwork',
    'itemType',
])

@php
    $usage = data_get($resourceNetwork, 'usage');
    $powerUsage = data_get($usage, 'power');
    $coolant = data_get($usage, 'coolant');

    $repair = data_get($resourceNetwork, 'repair');
    $repairCount = data_get($repair, 'max_repair_count');
    $timeToRepair = data_get($repair, 'time_to_repair');
    $healthRatio = data_get($repair, 'health_ratio');

    $states = data_get($resourceNetwork, 'states');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h4 class="card-title text-sm flex items-center gap-2">
            <x-icon name="network" class="size-4 text-primary" />
            <span>Resource Network</span>
        </h4>
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if($itemType !== 'PowerPlant')
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Power Usage</dt>
                <dd class="text-sm font-medium">{{ fmt_range(data_get($powerUsage, 'minimum'), data_get($powerUsage, 'maximum'), 'Segments', 1) }}</dd>
            </div>
            @endif
            @if($itemType !== 'Cooler')
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Coolant Usage</dt>
                <dd class="text-sm font-medium">{{ fmt_range(data_get($coolant, 'minimum'), data_get($coolant, 'maximum'), 'Segments', 1) }}</dd>
            </div>
            @endif
        </dl>

        @if (data_get($resourceNetwork, 'repair'))
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Self-Repair
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-3">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Repair Count</dt>
                            <dd class="text-sm font-medium">
                                {{ fmt_value_with_unit($repairCount, 'x', 0) }}
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Repair Time</dt>
                            <dd class="text-sm font-medium">
                                {{ fmt_value_with_unit($timeToRepair, 's', 0) }}
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Health Ratio</dt>
                            <dd class="text-sm font-medium">
                                {{ fmt_value_with_unit($healthRatio * 100, '%', 1) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </details>
        @endif

        @unless(empty($states))
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Network States
                </summary>
                <div class="collapse-content">
                    @foreach($states as $state)
                        @php
                            $stateDeltas = data_get($state, 'deltas', []);
                            $statePowerRanges = data_get($state, 'power_ranges', []);
                            $hasDeltas = is_array($stateDeltas) && count($stateDeltas) > 0;
                            $hasPowerRanges = is_array($statePowerRanges) && count($statePowerRanges) > 0;
                        @endphp
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-3">{{ $state['name'] }}</h4>

                        @if($hasDeltas)
                            <details class="collapse collapse-arrow border border-base-300 bg-base-100 mb-3" open>
                                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                                    Resource Deltas
                                </summary>
                                <div class="collapse-content">
                                    @foreach($stateDeltas as $delta)
                                        <div class="mb-4 pb-4 border-b border-base-200 last:border-0 last:mb-0 last:pb-0">
                                            <dl class="space-y-1 mb-3">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $delta['type'] }}</dt>
                                                <dd class="text-sm font-medium">
                                                    @if(data_get($delta, 'resource'))
                                                        {{ $delta['resource'] }}
                                                        {{-- Generated Resource --}}
                                                        @if(data_get($delta, 'generated_resource'))
                                                            → {{ $delta['generated_resource'] }}
                                                        @endif
                                                    @endif
                                                </dd>
                                            </dl>

                                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                                                {{-- Rate --}}
                                                @if(data_get($delta, 'rate') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Rate</dt>
                                                        <dd class="text-sm font-medium">
                                                            {{ fmt_value_with_unit($delta['rate'], '/s', 1) }}
                                                        </dd>
                                                    </div>
                                                @endif

                                                {{-- Minimum Fraction --}}
                                                @if(data_get($delta, 'minimum_fraction') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Fraction</dt>
                                                        <dd class="text-sm font-medium">
                                                            {{ fmt_value_with_unit($delta['minimum_fraction'], '', 1) }}
                                                        </dd>
                                                    </div>
                                                @endif


                                                {{-- Generated Rate --}}
                                                @if(data_get($delta, 'generated_rate') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Generated Rate</dt>
                                                        <dd class="text-sm font-medium">
                                                            {{ fmt_value_with_unit($delta['generated_rate'], '/s', 1) }}
                                                        </dd>
                                                    </div>
                                                @endif

                                                {{-- Discharge --}}
                                                @if(data_get($delta, 'discharge') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Discharge</dt>
                                                        <dd class="text-sm font-medium">
                                                            {{ fmt_value_with_unit($delta['discharge'], '', 2) }}
                                                        </dd>
                                                    </div>
                                                @endif
                                            </dl>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif

                        @if($hasPowerRanges)
                            <details class="collapse collapse-arrow border border-base-300 bg-base-100 mb-3">
                                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                                    Power Ranges
                                </summary>
                                <div class="collapse-content">
                                    @foreach($statePowerRanges as $i => $range)
                                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-3 mb-3">
                                            <div class="space-y-1">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">State</dt>
                                                <dd class="text-sm font-medium">
                                                    {{ match ($i) { 0 => 'Low', 1 => 'Standard', 2 => 'High', default => '' } }}
                                                    {{ data_get($range, 'register_range') === 0 ? '(Disabled)' : '' }}
                                                </dd>
                                            </div>
                                            <div class="space-y-1">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Start</dt>
                                                <dd class="text-sm font-medium">
                                                    {{ fmt_value_with_unit(data_get($range, 'start'), '', 0) }}
                                                </dd>
                                            </div>
                                            <div class="space-y-1">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Modifier</dt>
                                                <dd class="text-sm font-medium">
                                                    {{ fmt_value_with_unit(data_get($range, 'modifier'), 'x', 2) }}
                                                </dd>
                                            </div>
                                        </dl>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endforeach

                </div>
            </details>
        @endunless
    </div>
</div>
