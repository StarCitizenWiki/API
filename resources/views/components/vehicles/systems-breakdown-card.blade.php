@use('App\Support\Format')
@props(['vehicle'])

@php
    use Illuminate\Support\Str;

    $signature = data_get($vehicle, 'signature', []);
    $cooling = data_get($vehicle, 'cooling', []);
    $power = data_get($vehicle, 'power', []);

    $buildStateComparisonRows = static function (array $shieldsGroups, array $quantumGroups): array {
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
                'shields' => $shieldsValue !== null ? Format::numberOrDash($shieldsValue) : '-',
                'quantum' => $quantumValue !== null ? Format::numberOrDash($quantumValue) : '-',
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
            'value' => Format::numberOrDash($group),
        ];
    }

    $hasBreakdowns = $signatureBreakdownRows !== []
        || $coolingBreakdownRows !== []
        || $powerBreakdownRows !== [];
@endphp

@if ($hasBreakdowns)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">System Breakdown</h2>

            <div class="grid gap-6 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
                @if ($signatureBreakdownRows !== [])
                    <x-dl-section title="EM Groups" :dlClass="'grid grid-cols-3 gap-x-3 gap-y-2'">
                        <div class="text-xs font-light uppercase tracking-wide text-subtle">System / EM</div>
                        <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Shields</div>
                        <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Quantum</div>

                        @foreach ($signatureBreakdownRows as $row)
                            <dt class="text-xs font-light uppercase tracking-wide text-subtle">{{ $row['label'] }}</dt>
                            <dd class="text-right text-sm font-semibold text-base-content">{{ $row['shields'] }}</dd>
                            <dd class="text-right text-sm font-semibold text-base-content">{{ $row['quantum'] }}</dd>
                        @endforeach
                    </x-dl-section>
                @endif

                @if ($coolingBreakdownRows !== [])
                    <x-dl-section title="Cooling Groups" :dlClass="'grid grid-cols-3 gap-x-3 gap-y-2'">
                        <div class="text-xs font-light uppercase tracking-wide text-subtle">System / Segment</div>
                        <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Shields</div>
                        <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Quantum</div>

                        @foreach ($coolingBreakdownRows as $row)
                            <dt class="text-xs font-light uppercase tracking-wide text-subtle">{{ $row['label'] }}</dt>
                            <dd class="text-right text-sm font-semibold text-base-content">{{ $row['shields'] }}</dd>
                            <dd class="text-right text-sm font-semibold text-base-content">{{ $row['quantum'] }}</dd>
                        @endforeach
                    </x-dl-section>
                @endif

                @if ($powerBreakdownRows !== [])
                    <x-dl-section title="Power Groups" :dlClass="'grid grid-cols-2 gap-x-3 gap-y-2'">
                        <div class="text-xs font-light uppercase tracking-wide text-subtle">System</div>
                        <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Segments</div>

                        @foreach ($powerBreakdownRows as $row)
                            <dt class="text-xs font-light uppercase tracking-wide text-subtle">{{ $row['label'] }}</dt>
                            <dd class="text-right text-sm font-semibold text-base-content">{{ $row['value'] }}</dd>
                        @endforeach
                    </x-dl-section>
                @endif
            </div>
        </div>
    </section>
@endif
