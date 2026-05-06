@use('App\Support\Format')
@props([
    'weaponAttachment',
])

@php
    use Illuminate\Support\Str;

    // Iron Sight
    $ironSight = data_get($weaponAttachment, 'iron_sight', []);
    $ironSightMetrics = array_values(array_filter([
        ['label' => 'Default Range', 'value' => data_get($ironSight, 'default_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Max Range', 'value' => data_get($ironSight, 'max_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Range Increment', 'value' => data_get($ironSight, 'range_increment'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Auto Zeroing Time', 'value' => data_get($ironSight, 'auto_zeroing_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Zoom Scale', 'value' => data_get($ironSight, 'zoom_scale'), 'unit' => '', 'precision' => 2],
        ['label' => 'Zoom Time', 'value' => data_get($ironSight, 'zoom_time_change'), 'unit' => '%', 'precision' => 1, 'colored' => true, 'factor' => 100],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    // Laser Pointer
    $laserPointer = data_get($weaponAttachment, 'laser_pointer', []);
    $laserPointerMetrics = [
        ['label' => 'Range', 'value' => data_get($laserPointer, 'range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Color', 'value' => data_get($laserPointer, 'color_css'), 'type' => 'color'],
    ];

    // Flashlight
    $flashlightRaw = data_get($weaponAttachment, 'flashlight', []);
    $flashlightSections = [];
    foreach ($flashlightRaw as $profileType => $profile) {
        if (! is_array($profile) || collect($profile)->filter(static fn ($v) => $v !== null)->isEmpty()) {
            continue;
        }
        $sectionTitle = 'Flashlight: ' . Str::headline($profileType);
        $flashlightSections[$sectionTitle] = [
            ['label' => 'Light Radius', 'value' => data_get($profile, 'light_radius'), 'unit' => 'm', 'precision' => 2],
            ['label' => 'Color', 'value' => data_get($profile, 'color_css'), 'type' => 'color'],
            ['label' => 'Light Type', 'value' => data_get($profile, 'light_type')],
            ['label' => 'Intensity', 'value' => data_get($profile, 'intensity'), 'unit' => '', 'precision' => 2],
        ];
    }

    // Magazine
    $magazine = data_get($weaponAttachment, 'magazine', []);
    $magazineMetrics = array_values(array_filter([
        ['label' => 'Initial Ammo Count', 'value' => data_get($magazine, 'initial_ammo_count'), 'unit' => '', 'precision' => 0],
        ['label' => 'Max Ammo Count', 'value' => data_get($magazine, 'max_ammo_count'), 'unit' => '', 'precision' => 0],
        ['label' => 'Max Restock Count', 'value' => data_get($magazine, 'max_restock_count'), 'unit' => '', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    // Compensator (or Stabilizer)
    $compensator = data_get($weaponAttachment, 'compensator', data_get($weaponAttachment, 'stabilizer', []));
    $compensatorMetrics = array_values(array_filter([
        data_get($compensator, 'attachment_point') !== null ? ['label' => 'Attachment Point', 'value' => data_get($compensator, 'attachment_point')] : null,
        data_get($compensator, 'type') !== null ? ['label' => 'Type', 'value' => data_get($compensator, 'type')] : null,
        ...collect($compensator)
            ->filter(fn ($value, $key) => str_ends_with($key, '_change') && $value != 0 && $value !== null)
            ->map(fn ($value, $key) => [
                'label' => Str::headline(str_replace('_change', '', $key)),
                'value' => $value * 100,
            ])
            ->values()
            ->all(),
    ], static fn (?array $m): bool => $m !== null));

    // Flash Hider
    $flashHider = data_get($weaponAttachment, 'flash_hider', []);
    $flashHiderMetrics = array_values(array_filter([
        data_get($flashHider, 'attachment_point') !== null ? ['label' => 'Attachment Point', 'value' => data_get($flashHider, 'attachment_point')] : null,
        data_get($flashHider, 'type') !== null ? ['label' => 'Type', 'value' => data_get($flashHider, 'type')] : null,
        ...collect($flashHider)
            ->filter(fn ($value, $key) => str_ends_with($key, '_change') && $value != 0 && $value !== null)
            ->map(fn ($value, $key) => [
                'label' => Str::headline(str_replace('_change', '', $key)),
                'value' => $value * 100,
            ])
            ->values()
            ->all(),
    ], static fn (?array $m): bool => $m !== null));

@endphp

<x-item-card title="Weapon Attachment">
    <x-dl-container>
        <x-dl-section title="Iron Sight">
            @foreach ($ironSightMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if (($metric['colored'] ?? false))
                        <span class="{{ Format::colorClass($metric['value'] * ($metric['factor'] ?? 1)) }}">{{ Format::valueWithUnit($metric['value'] * ($metric['factor'] ?? 1), $metric['unit'], $metric['precision']) }}</span>
                    @else
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Laser Pointer">
            @foreach ($laserPointerMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if (($metric['type'] ?? null) === 'color')
                        <div class="flex items-center gap-2">
                            <div class="size-8 rounded border border-base-300"
                                 style="background-color: {{ $metric['value'] }};"></div>
                            <span>{{ $metric['value'] }}</span>
                        </div>
                    @else
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        @foreach ($flashlightSections as $sectionTitle => $metrics)
            <x-dl-section :title="$sectionTitle">
                @foreach ($metrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if (($metric['type'] ?? null) === 'color')
                            <div class="flex items-center gap-2">
                                <div class="size-4 rounded border border-base-300"
                                     style="background-color: {{ $metric['value'] }};"></div>
                                <span>{{ $metric['value'] }}</span>
                            </div>
                        @elseif (isset($metric['unit']))
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @endforeach

        <x-dl-section title="Magazine">
            @foreach ($magazineMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Compensator">
            @foreach ($compensatorMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if (is_string($metric['value']))
                        {{ $metric['value'] }}
                    @else
                        <span class="{{ Format::colorClass($metric['value']) }}">{{ Format::valueWithUnit($metric['value'], '%', 1) }}</span>
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Flash Hider">
            @foreach ($flashHiderMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if (is_string($metric['value']))
                        {{ $metric['value'] }}
                    @else
                        <span class="{{ Format::colorClass($metric['value']) }}">{{ Format::valueWithUnit($metric['value'], '%', 1) }}</span>
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
