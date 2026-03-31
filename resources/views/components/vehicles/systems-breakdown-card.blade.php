@props(['vehicle'])

@php
    use Illuminate\Support\Str;

    $signature = data_get($vehicle, 'signature', []);
    $cooling = data_get($vehicle, 'cooling', []);
    $power = data_get($vehicle, 'power', []);

    $formatWholeNumber = static fn (mixed $value): string => $value === null ? '-' : number_format((float) $value, 0);

    $buildStateComparisonRows = static function (array $shieldsGroups, array $quantumGroups) use ($formatWholeNumber): array {
        $systems = array_values(array_unique(array_merge(array_keys($shieldsGroups), array_keys($quantumGroups))));
        $rows = [];

        foreach ($systems as $system) {
            $shieldsValue = $shieldsGroups[$system] ?? null;
            $quantumValue = $quantumGroups[$system] ?? null;

            if ($shieldsValue === null && $quantumValue === null) {
                continue;
            }

            $rows[] = [
                'label' => Str::headline((string) $system),
                'shields' => $shieldsValue !== null ? $formatWholeNumber($shieldsValue) : '-',
                'quantum' => $quantumValue !== null ? $formatWholeNumber($quantumValue) : '-',
            ];
        }

        return $rows;
    };

    $signatureBreakdownRows = $buildStateComparisonRows(
        (array) (data_get($signature, 'em_groups_shields') ?? []),
        (array) (data_get($signature, 'em_groups_quantum') ?? [])
    );

    $coolingBreakdownRows = $buildStateComparisonRows(
        (array) (data_get($cooling, 'used_segments_shields_grouped') ?? []),
        (array) (data_get($cooling, 'used_segments_quantum_grouped') ?? [])
    );

    $powerBreakdownRows = [];
    foreach ((array) (data_get($power, 'used_segments_grouped') ?? []) as $system => $group) {
        if ($group === null) {
            continue;
        }

        $powerBreakdownRows[] = [
            'label' => Str::headline((string) $system),
            'value' => fmt_or_dash($group),
        ];
    }

    $hasBreakdowns = $signatureBreakdownRows !== []
        || $coolingBreakdownRows !== []
        || $powerBreakdownRows !== [];
@endphp

@if ($hasBreakdowns)
    <details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
        <summary class="collapse-title min-h-11 py-3 font-semibold">
            System Breakdown
        </summary>

        <div class="collapse-content space-y-5">
            <div class="grid gap-12 xl:grid-cols-3">
                @if ($signatureBreakdownRows !== [])
                    <section class="space-y-3">
                        <div class="text-sm font-semibold text-base-content/65">EM Groups</div>

                        <dl class="grid grid-cols-3 gap-x-3 gap-y-2">
                            <div></div>
                            <div class="text-right text-xs font-medium uppercase tracking-wide text-base-content/45">Shields</div>
                            <div class="text-right text-xs font-medium uppercase tracking-wide text-base-content/45">Quantum</div>

                            @foreach ($signatureBreakdownRows as $row)
                                <dt class="text-sm text-base-content/80">{{ $row['label'] }}</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">{{ $row['shields'] }}</dd>
                                <dd class="text-right text-sm font-semibold text-base-content">{{ $row['quantum'] }}</dd>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($coolingBreakdownRows !== [])
                    <section class="space-y-3">
                        <div class="text-sm font-semibold text-base-content/65">Cooling Groups</div>

                        <dl class="grid grid-cols-3 gap-x-3 gap-y-2">
                            <div></div>
                            <div class="text-right text-xs font-medium uppercase tracking-wide text-base-content/45">Shields</div>
                            <div class="text-right text-xs font-medium uppercase tracking-wide text-base-content/45">Quantum</div>

                            @foreach ($coolingBreakdownRows as $row)
                                <dt class="text-sm text-base-content/80">{{ $row['label'] }}</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">{{ $row['shields'] }}</dd>
                                <dd class="text-right text-sm font-semibold text-base-content">{{ $row['quantum'] }}</dd>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($powerBreakdownRows !== [])
                    <section class="space-y-3">
                        <div class="text-sm font-semibold text-base-content/65">Power Groups</div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">
                            <div class="text-xs font-medium uppercase tracking-wide text-base-content/45">System</div>
                            <div class="text-right text-xs font-medium uppercase tracking-wide text-base-content/45">Segments</div>

                            @foreach ($powerBreakdownRows as $row)
                                <dt class="text-sm text-base-content/80">{{ $row['label'] }}</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">{{ $row['value'] }}</dd>
                            @endforeach
                        </dl>
                    </section>
                @endif
            </div>
        </div>
    </details>
@endif
