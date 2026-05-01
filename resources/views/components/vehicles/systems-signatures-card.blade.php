@props(['vehicle'])

@php
    $signature = data_get($vehicle, 'signature', []);
    $cooling = data_get($vehicle, 'cooling', []);
    $power = data_get($vehicle, 'power', []);

    $formatWholeNumber = static fn (mixed $value): string => $value === null ? '-' : number_format((float) $value, 0);

    $signatureSummaryRows = array_values(array_filter([
        [
            'label' => 'IR',
            'shields' => data_get($signature, 'ir_shields'),
            'quantum' => data_get($signature, 'ir_quantum'),
        ],
        [
            'label' => 'EM',
            'shields' => data_get($signature, 'em_shields'),
            'quantum' => data_get($signature, 'em_quantum'),
        ],
    ], static fn (array $row): bool => $row['shields'] !== null || $row['quantum'] !== null));

    $coolingGeneration = data_get($cooling, 'generation_segments');
    $coolingSummaryRows = array_values(array_filter([
        [
            'label' => 'Used',
            'shields' => data_get($cooling, 'used_segments_shields') !== null
                ? fmt_value_with_unit(data_get($cooling, 'used_segments_shields'), 'Segments', 0)
                : null,
            'quantum' => data_get($cooling, 'used_segments_quantum') !== null
                ? fmt_value_with_unit(data_get($cooling, 'used_segments_quantum'), 'Segments', 0)
                : null,
        ],
        [
            'label' => 'Usage',
            'shields' => data_get($cooling, 'usage_shields_pct') !== null
                ? fmt_value_with_unit(data_get($cooling, 'usage_shields_pct') * 100, '%', 1)
                : null,
            'quantum' => data_get($cooling, 'usage_quantum_pct') !== null
                ? fmt_value_with_unit(data_get($cooling, 'usage_quantum_pct') * 100, '%', 1)
                : null,
        ],
    ], static fn (array $row): bool => $row['shields'] !== null || $row['quantum'] !== null));

    $powerGeneration = data_get($power, 'generation_segments');
    $emPerSegment = data_get($signature, 'em_per_segment');
    $powerSummaryRows = array_values(array_filter([
        [
            'label' => 'Used',
            'shields' => data_get($power, 'used_segments_shields') !== null
                ? fmt_value_with_unit(data_get($power, 'used_segments_shields'), 'Segments', 0)
                : null,
            'quantum' => data_get($power, 'used_segments_quantum') !== null
                ? fmt_value_with_unit(data_get($power, 'used_segments_quantum'), 'Segments', 0)
                : null,
        ],
    ], static fn (array $row): bool => $row['shields'] !== null || $row['quantum'] !== null));

    $hasSummaryPanels = $signatureSummaryRows !== []
        || $coolingGeneration !== null
        || $coolingSummaryRows !== []
        || $powerGeneration !== null
        || $emPerSegment !== null
        || $powerSummaryRows !== [];
@endphp

@if ($hasSummaryPanels)
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Resource Network</h2>

            <div class="grid gap-12 lg:grid-cols-3">
                @if ($signatureSummaryRows !== [])
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Signature</h3>
                        </div>

                        <dl class="grid grid-cols-3 gap-x-3 gap-y-2">
                            <div></div>
                            <div class="text-right text-xs font-medium uppercase tracking-wide text-muted">Shields</div>
                            <div class="text-right text-xs font-medium uppercase tracking-wide text-muted">Quantum</div>

                            @foreach ($signatureSummaryRows as $row)
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">
                                    {{ $row['label'] }}
                                </dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $row['shields'] !== null ? $formatWholeNumber($row['shields']) : '-' }}
                                </dd>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $row['quantum'] !== null ? $formatWholeNumber($row['quantum']) : '-' }}
                                </dd>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($coolingGeneration !== null || $coolingSummaryRows !== [])
                    <section class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <h3 class="text-sm font-semibold text-base-content">Cooling</h3>
                            </div>

                            @if ($coolingGeneration !== null)
                                <div class="text-sm font-semibold text-base-content">
                                    {{ fmt_value_with_unit($coolingGeneration, 'Segments', 0) }}
                                </div>
                            @endif
                        </div>

                        @if ($coolingSummaryRows !== [])
                            <dl class="grid grid-cols-3 gap-x-3 gap-y-2">
                                <div></div>
                                <div class="text-right text-xs font-medium uppercase tracking-wide text-muted">Shields</div>
                                <div class="text-right text-xs font-medium uppercase tracking-wide text-muted">Quantum</div>

                                @foreach ($coolingSummaryRows as $row)
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">
                                        {{ $row['label'] }}
                                    </dt>
                                    <dd class="text-right text-sm font-semibold text-base-content">
                                        {{ $row['shields'] ?? '-' }}
                                    </dd>
                                    <dd class="text-right text-sm font-semibold text-base-content">
                                        {{ $row['quantum'] ?? '-' }}
                                    </dd>
                                @endforeach
                            </dl>
                        @endif
                    </section>
                @endif

                @if ($powerGeneration !== null || $emPerSegment !== null || $powerSummaryRows !== [])
                    <section class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <h3 class="text-sm font-semibold text-base-content">Power</h3>
                            </div>

                            @if ($powerGeneration !== null)
                                <div class="text-sm font-semibold text-base-content">
                                    {{ fmt_value_with_unit($powerGeneration, 'Segments', 0) }}

                                    @if ($emPerSegment !== null)
                                        <span class="text-xs text-secondary">/ {{ fmt_value_with_unit($emPerSegment, 'EM per Segment', 0) }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($powerSummaryRows !== [])
                            <dl class="grid grid-cols-3 gap-x-3 gap-y-2">
                                <div></div>
                                <div class="text-right text-xs font-medium uppercase tracking-wide text-muted">Shields</div>
                                <div class="text-right text-xs font-medium uppercase tracking-wide text-muted">Quantum</div>

                                @foreach ($powerSummaryRows as $row)
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">
                                        {{ $row['label'] }}
                                    </dt>
                                    <dd class="text-right text-sm font-semibold text-base-content">
                                        {{ $row['shields'] ?? '-' }}
                                    </dd>
                                    <dd class="text-right text-sm font-semibold text-base-content">
                                        {{ $row['quantum'] ?? '-' }}
                                    </dd>
                                @endforeach
                            </dl>
                        @endif
                    </section>
                @endif
            </div>
        </div>
    </section>
@endif
