@use('App\Support\Format')
@props([
    'cargoGrid',
 ])

@php
    $scuConverted = data_get($cargoGrid, 'scu');
    $isOpen = data_get($cargoGrid, 'open');
    $isExternal = data_get($cargoGrid, 'external');
    $isClosed = data_get($cargoGrid, 'closed');
    $width = data_get($cargoGrid, 'width');
    $height = data_get($cargoGrid, 'height');
    $length = data_get($cargoGrid, 'length');

    $hasDimensions = $width !== null && $height !== null && $length !== null;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Cargo Grid</h2>


        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($scuConverted !== null)
                <x-dt-dd label="Capacity">{{ Format::valueWithUnit($scuConverted, 'SCU', 1) }}</x-dt-dd>
            @endif
            @if ($hasDimensions)
                <x-dt-dd label="Dimensions">{{ Format::valueWithUnit($width, 'm', 1) }} × {{ Format::valueWithUnit($height, 'm', 1) }} × {{ Format::valueWithUnit($length, 'm', 1) }}</x-dt-dd>
            @endif
            <x-dt-dd label="Type">
                {{ $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-')) }}
            </x-dt-dd>
        </x-dl-section>
    </div>
</div>
