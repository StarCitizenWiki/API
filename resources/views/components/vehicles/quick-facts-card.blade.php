@props(['vehicle'])

@php
    $cargoCapacity = data_get($vehicle, 'cargo_capacity');
    $stowage = data_get($vehicle, 'vehicle_inventory');
    $health = data_get($vehicle, 'health');
    $shieldHp = data_get($vehicle, 'shield.hp');
    $scmSpeed = data_get($vehicle, 'speed.scm');
    $maxSpeed = data_get($vehicle, 'speed.max');
    $irShields = data_get($vehicle, 'signature.ir_shields');
    $emShields = data_get($vehicle, 'signature.em_shields');
    $crew = data_get($vehicle, 'crew', []);
    $crewMinimum = data_get($crew, 'min');
    $crewMaximum = data_get($crew, 'max');
    $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));
    $dimension = data_get($vehicle, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');
    $uuid = data_get($vehicle, 'uuid');
    $version = data_get($vehicle, 'version');

    if ($crewMinimum !== null && $crewMaximum !== null && $crewMaximum !== $crewMinimum) {
        $crewValue = sprintf('%s-%s', $crewMinimum, $crewMaximum);
    } else {
        $crewValue = $crewMinimum ?? $crewMaximum ?? '-';
    }

    if ($length || $width || $height) {
        $dimensionsValue = sprintf('%s × %s × %sm', $length ?? '-', $width ?? '-', $height ?? '-');
    } else {
        $dimensionsValue = '-';
    }

    $storageRows = [
        ['label' => 'Cargo', 'value' => $cargoCapacity !== null ? fmt_value_with_unit($cargoCapacity, 'SCU', 0) : null],
        ['label' => 'Stowage', 'value' => $stowage !== null ? fmt_value_with_unit($stowage, 'µSCU', 0) : null],
    ];

    $speedRows = [
        ['label' => 'SCM', 'value' => $scmSpeed !== null ? fmt_value_with_unit($scmSpeed, 'm/s', 0) : null],
        ['label' => 'Max', 'value' => $maxSpeed !== null ? fmt_value_with_unit($maxSpeed, 'm/s', 0) : null],
    ];

    $defenseRows = [
        ['label' => 'HP', 'value' => $health !== null ? fmt_value_with_unit($health, 'HP', 0) : null],
        ['label' => 'Shield', 'value' => $shieldHp !== null ? fmt_value_with_unit($shieldHp, 'HP', 0) : null],
    ];

    $signatureRows = [
        ['label' => 'IR', 'value' => $irShields !== null ? fmt_or_dash($irShields) : null],
        ['label' => 'EM', 'value' => $emShields !== null ? fmt_or_dash($emShields) : null],
    ];

    $columns = [
        [
            ['title' => 'Storage', 'rows' => $storageRows],
            ['title' => 'Signature', 'rows' => $signatureRows],
            [
                'title' => 'Stats',
                'rows' => [
                    ['label' => 'Crew', 'value' => $crewValue],
                    ['label' => 'Dimensions', 'value' => $dimensionsValue],
                    ['label' => 'Cross Section', 'value' => fmt_or_dash(data_get($vehicle, 'cross_section_max'))],
                    ['label' => 'Mass', 'value' => fmt_value_with_unit($massTotal, 'kg', 0)],
                ],
            ],
        ],
        [
            ['title' => 'Speed', 'rows' => $speedRows],
            ['title' => 'Defense', 'rows' => $defenseRows],
        ],
    ];

    $className = data_get($vehicle, 'class_name');
    $uuidApiUrl = $uuid !== null ? route('vehicles.show', $uuid) : null;

    $footer = [
        ['label' => 'Class Name', 'value' => $className],
        $uuid !== null ? ['label' => 'UUID', 'value' => $uuid, 'url' => $uuidApiUrl] : ['label' => 'UUID', 'value' => '-'],
        ['label' => 'Version', 'value' => $version ?? '-'],
    ];
@endphp
<x-quick-facts-card :columns="$columns" :footer="$footer" :test-id="'vehicle-quick-facts-card'" {{ $attributes }} />
