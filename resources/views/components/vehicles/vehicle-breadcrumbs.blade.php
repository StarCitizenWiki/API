<?php declare(strict_types=1); ?>

@props([
    'vehicle' => 'array', // API response array with vehicle data
    'manufacturerCode' => 'string|null',
])

<div class="breadcrumbs text-sm text-base-content/70">
    <ul>
        <li>
            <a href="{{ route('web.vehicles.index') }}">All Vehicles</a>
        </li>
        <li>
            <a href="{{ route('web.vehicles.index', ['filter' => ['manufacturer' => $manufacturerCode]]) }}">
                {{ data_get($vehicle, 'manufacturer.name') }}
            </a>
        </li>
        <li>{{ data_get($vehicle, 'name') }}</li>
    </ul>
</div>
