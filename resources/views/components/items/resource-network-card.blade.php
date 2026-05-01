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
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Resource Network</h2>
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if($itemType !== 'PowerPlant')
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Power Usage</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range(data_get($powerUsage, 'minimum'), data_get($powerUsage, 'maximum'), 'Segments', 1) }}</dd>
            </div>
            @endif
            @if($itemType !== 'Cooler')
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Coolant Usage</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range(data_get($coolant, 'minimum'), data_get($coolant, 'maximum'), 'Segments', 1) }}</dd>
            </div>
            @endif
        </dl>

        @if (data_get($resourceNetwork, 'repair'))
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Self-Repair
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Repair Count</dt>
                            <dd class="text-sm font-semibold text-base-content">
                                {{ fmt_value_with_unit($repairCount, 'x', 0) }}
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Repair Time</dt>
                            <dd class="text-sm font-semibold text-base-content">
                                {{ fmt_value_with_unit($timeToRepair, 's', 0) }}
                            </dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Health Ratio</dt>
                            <dd class="text-sm font-semibold text-base-content">
                                {{ fmt_value_with_unit($healthRatio * 100, '%', 1) }}
                            </dd>
                        </div>
                    </dl>
            </details>
        @endif

        @unless(empty($states))
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Network States
                </summary>
                    @foreach($states as $state)
                        @php
                            $stateDeltas = data_get($state, 'deltas', []);
                            $statePowerRanges = data_get($state, 'power_ranges', []);
                            $hasDeltas = is_array($stateDeltas) && count($stateDeltas) > 0;
                            $hasPowerRanges = is_array($statePowerRanges) && count($statePowerRanges) > 0;
                        @endphp
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-3">{{ $state['name'] }}</h4>

                        @if($hasDeltas)
                            <details class="group/deltas mb-3" open>
                                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open/deltas:rotate-90" />
                                    Resource Deltas
                                </summary>
                                    @foreach($stateDeltas as $delta)
                                        <div class="mb-4 pb-4 border-b border-base-200 last:border-0 last:mb-0 last:pb-0">
                                            <dl class="space-y-1 mb-3 pt-1 pb-2">
                                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">{{ $delta['type'] }}</dt>
                                                <dd class="text-sm font-semibold text-base-content">
                                                    @if(data_get($delta, 'resource'))
                                                        {{ $delta['resource'] }}
                                                        {{-- Generated Resource --}}
                                                        @if(data_get($delta, 'generated_resource'))
                                                            → {{ $delta['generated_resource'] }}
                                                        @endif
                                                    @endif
                                                </dd>
                                            </dl>

                                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                                {{-- Rate --}}
                                                @if(data_get($delta, 'rate') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Rate</dt>
                                                        <dd class="text-sm font-semibold text-base-content">
                                                            {{ fmt_value_with_unit($delta['rate'], '/s', 1) }}
                                                        </dd>
                                                    </div>
                                                @endif

                                                {{-- Minimum Fraction --}}
                                                @if(data_get($delta, 'minimum_fraction') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Minimum Fraction</dt>
                                                        <dd class="text-sm font-semibold text-base-content">
                                                            {{ fmt_value_with_unit($delta['minimum_fraction'], '', 1) }}
                                                        </dd>
                                                    </div>
                                                @endif


                                                {{-- Generated Rate --}}
                                                @if(data_get($delta, 'generated_rate') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Generated Rate</dt>
                                                        <dd class="text-sm font-semibold text-base-content">
                                                            {{ fmt_value_with_unit($delta['generated_rate'], '/s', 1) }}
                                                        </dd>
                                                    </div>
                                                @endif

                                                {{-- Discharge --}}
                                                @if(data_get($delta, 'discharge') !== null)
                                                    <div class="space-y-1">
                                                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Discharge</dt>
                                                        <dd class="text-sm font-semibold text-base-content">
                                                            {{ fmt_value_with_unit($delta['discharge'], '', 2) }}
                                                        </dd>
                                                    </div>
                                                @endif
                                            </dl>
                                        </div>
                                    @endforeach
                            </details>
                        @endif

                        @if($hasPowerRanges)
                            <details class="group/ranges mb-3">
                                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open/ranges:rotate-90" />
                                    Power Ranges
                                </summary>
                                    @foreach($statePowerRanges as $i => $range)
                                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                            <div class="space-y-1">
                                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">State</dt>
                                                <dd class="text-sm font-semibold text-base-content">
                                                    {{ match ($i) { 0 => 'Low', 1 => 'Standard', 2 => 'High', default => '' } }}
                                                    {{ data_get($range, 'register_range') === 0 ? '(Disabled)' : '' }}
                                                </dd>
                                            </div>
                                            <div class="space-y-1">
                                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Start</dt>
                                                <dd class="text-sm font-semibold text-base-content">
                                                    {{ fmt_value_with_unit(data_get($range, 'start'), '', 0) }}
                                                </dd>
                                            </div>
                                            <div class="space-y-1">
                                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Modifier</dt>
                                                <dd class="text-sm font-semibold text-base-content">
                                                    {{ fmt_value_with_unit(data_get($range, 'modifier'), 'x', 2) }}
                                                </dd>
                                            </div>
                                        </dl>
                                    @endforeach
                            </details>
                        @endif
                    @endforeach

            </details>
        @endunless
    </div>
</div>
