<?php declare(strict_types=1); ?>

@props([
    'vehicle' => 'array', // API response array with vehicle data
    'manufacturerCode' => 'string|null',
    'breadcrumbs' => [],
])

@php
    $versionQuery = request()->query('version');
    $makeVehiclesUrl = static function (array $params = []) use ($versionQuery): string {
        $url = route('web.vehicles.index', $params);

        if (is_string($versionQuery) && $versionQuery !== '') {
            $url = url()->query($url, ['version' => $versionQuery]);
        }

        return $url;
    };

    if (! is_array($breadcrumbs) || $breadcrumbs === []) {
        $breadcrumbs = [
            [
                'label' => 'All Vehicles',
                'url' => $makeVehiclesUrl(),
            ],
            [
                'label' => data_get($vehicle, 'manufacturer.name'),
                'url' => $makeVehiclesUrl(['filter' => ['manufacturer' => $manufacturerCode]]),
            ],
            [
                'label' => data_get($vehicle, 'name'),
                'url' => null,
            ],
        ];
    }
@endphp

<div class="breadcrumbs text-sm text-subtle overflow-x-auto" data-testid="vehicle-breadcrumbs">
    <ul class="w">
        @foreach ($breadcrumbs as $breadcrumb)
            <li>
                @if (! empty($breadcrumb['url']))
                    <a
                        data-testid="{{ $loop->first ? 'vehicle-breadcrumbs-all-link' : ($loop->iteration === 2 ? 'vehicle-breadcrumbs-manufacturer-link' : 'vehicle-breadcrumb-link-'.$loop->index) }}"
                        href="{{ $breadcrumb['url'] }}"
                    >{{ $breadcrumb['label'] }}</a>
                @else
                    <span>{{ $breadcrumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ul>
</div>
