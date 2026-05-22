@use('App\Support\Format')
@props([
    'weaponAttachment',
])

@php
    use Illuminate\Support\Str;

    $sections = [];

    // Iron Sight
    $ironSight = data_get($weaponAttachment, 'iron_sight', []);
    $ironSightRows = array_values(array_filter([
        ['label' => 'Default Range', 'value' => data_get($ironSight, 'default_range') !== null ? Format::valueWithUnit(data_get($ironSight, 'default_range'), 'm', 2) : null],
        ['label' => 'Max Range', 'value' => data_get($ironSight, 'max_range') !== null ? Format::valueWithUnit(data_get($ironSight, 'max_range'), 'm', 2) : null],
        ['label' => 'Range Increment', 'value' => data_get($ironSight, 'range_increment') !== null ? Format::valueWithUnit(data_get($ironSight, 'range_increment'), 'm', 2) : null],
        ['label' => 'Auto Zeroing Time', 'value' => data_get($ironSight, 'auto_zeroing_time') !== null ? Format::valueWithUnit(data_get($ironSight, 'auto_zeroing_time'), 's', 2) : null],
        ['label' => 'Zoom Scale', 'value' => data_get($ironSight, 'zoom_scale') !== null ? Format::valueWithUnit(data_get($ironSight, 'zoom_scale'), '', 2) : null],
        ['label' => 'Zoom Time', 'value' => data_get($ironSight, 'zoom_time_change') !== null ? Format::valueWithUnit(data_get($ironSight, 'zoom_time_change') * 100, '%', 1) : null, 'class' => data_get($ironSight, 'zoom_time_change') !== null ? Format::colorClass(data_get($ironSight, 'zoom_time_change') * 100) : ''],
    ], static fn (array $row): bool => $row['value'] !== null && $row['value'] != '0'));

    if ($ironSightRows !== []) {
        $sections[] = ['title' => 'Iron Sight', 'rows' => $ironSightRows];
    }

    // Laser Pointer
    $laserPointer = data_get($weaponAttachment, 'laser_pointer', []);
    $laserRange = data_get($laserPointer, 'range');
    $laserColor = data_get($laserPointer, 'color_css');
    $laserRows = array_values(array_filter([
        ['label' => 'Range', 'value' => $laserRange !== null ? Format::valueWithUnit($laserRange, 'm', 2) : null],
        ['label' => 'Color', 'value' => $laserColor],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($laserRows !== []) {
        $sections[] = ['title' => 'Laser Pointer', 'rows' => $laserRows];
    }

    // Flashlight
    $flashlightRaw = data_get($weaponAttachment, 'flashlight', []);
    foreach ($flashlightRaw as $profileType => $profile) {
        if (! is_array($profile) || collect($profile)->filter(static fn ($v) => $v !== null)->isEmpty()) {
            continue;
        }
        $flashRows = array_values(array_filter([
            ['label' => 'Light Radius', 'value' => data_get($profile, 'light_radius') !== null ? Format::valueWithUnit(data_get($profile, 'light_radius'), 'm', 2) : null],
            ['label' => 'Color', 'value' => data_get($profile, 'color_css')],
            ['label' => 'Light Type', 'value' => data_get($profile, 'light_type')],
            ['label' => 'Intensity', 'value' => data_get($profile, 'intensity') !== null ? Format::valueWithUnit(data_get($profile, 'intensity'), '', 2) : null],
        ], static fn (array $row): bool => $row['value'] !== null));

        if ($flashRows !== []) {
            $sections[] = ['title' => 'Flashlight: ' . Str::headline($profileType), 'rows' => $flashRows];
        }
    }

    // Magazine
    $magazine = data_get($weaponAttachment, 'magazine', []);
    $magazineRows = array_values(array_filter([
        ['label' => 'Initial Ammo Count', 'value' => data_get($magazine, 'initial_ammo_count') !== null ? Format::numberOrDash(data_get($magazine, 'initial_ammo_count'), 0) : null],
        ['label' => 'Max Ammo Count', 'value' => data_get($magazine, 'max_ammo_count') !== null ? Format::numberOrDash(data_get($magazine, 'max_ammo_count'), 0) : null],
        ['label' => 'Max Restock Count', 'value' => data_get($magazine, 'max_restock_count') !== null ? Format::numberOrDash(data_get($magazine, 'max_restock_count'), 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null && $row['value'] != '0'));

    if ($magazineRows !== []) {
        $sections[] = ['title' => 'Magazine', 'rows' => $magazineRows];
    }

    // Compensator (or Stabilizer)
    $compensator = data_get($weaponAttachment, 'compensator', data_get($weaponAttachment, 'stabilizer', []));
    $compensatorRows = [];
    if (data_get($compensator, 'attachment_point') !== null) {
        $compensatorRows[] = ['label' => 'Attachment Point', 'value' => data_get($compensator, 'attachment_point')];
    }
    if (data_get($compensator, 'type') !== null) {
        $compensatorRows[] = ['label' => 'Type', 'value' => data_get($compensator, 'type')];
    }
    $compChanges = collect($compensator)
        ->filter(fn ($value, $key) => str_ends_with($key, '_change') && $value != 0 && $value !== null)
        ->map(fn ($value, $key) => [
            'label' => Str::headline(str_replace('_change', '', $key)),
            'value' => Format::valueWithUnit($value * 100, '%', 1),
            'class' => Format::colorClass($value * 100),
        ])
        ->values()
        ->all();
    $compensatorRows = array_merge($compensatorRows, $compChanges);

    if ($compensatorRows !== []) {
        $sections[] = ['title' => 'Compensator', 'rows' => $compensatorRows];
    }

    // Flash Hider
    $flashHider = data_get($weaponAttachment, 'flash_hider', []);
    $flashHiderRows = [];
    if (data_get($flashHider, 'attachment_point') !== null) {
        $flashHiderRows[] = ['label' => 'Attachment Point', 'value' => data_get($flashHider, 'attachment_point')];
    }
    if (data_get($flashHider, 'type') !== null) {
        $flashHiderRows[] = ['label' => 'Type', 'value' => data_get($flashHider, 'type')];
    }
    $fhChanges = collect($flashHider)
        ->filter(fn ($value, $key) => str_ends_with($key, '_change') && $value != 0 && $value !== null)
        ->map(fn ($value, $key) => [
            'label' => Str::headline(str_replace('_change', '', $key)),
            'value' => Format::valueWithUnit($value * 100, '%', 1),
            'class' => Format::colorClass($value * 100),
        ])
        ->values()
        ->all();
    $flashHiderRows = array_merge($flashHiderRows, $fhChanges);

    if ($flashHiderRows !== []) {
        $sections[] = ['title' => 'Flash Hider', 'rows' => $flashHiderRows];
    }
@endphp

<x-data-card title="Weapon Attachment" :sections="$sections" />
