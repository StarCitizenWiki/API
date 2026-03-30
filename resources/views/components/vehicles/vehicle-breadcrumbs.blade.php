<?php declare(strict_types=1); ?>

@props([
    'vehicle' => 'array', // API response array with vehicle data
    'manufacturerCode' => 'string|null',
])

<div class="breadcrumbs text-sm text-base-content/70" data-testid="vehicle-breadcrumbs">
    <ul>
        <li>
            <a data-testid="vehicle-breadcrumbs-all-link" href="{{ route('web.vehicles.index') }}">All Vehicles</a>
        </li>
        <li>
            <a
                data-testid="vehicle-breadcrumbs-manufacturer-link"
                href="{{ route('web.vehicles.index', ['filter' => ['manufacturer' => $manufacturerCode]]) }}"
            >
                {{ data_get($vehicle, 'manufacturer.name') }}
            </a>
        </li>
        <li>{{ data_get($vehicle, 'name') }}</li>
    </ul>
</div>
