@use('App\Support\Format')
@props(['vehicle'])

@php
    $weaponStorage = data_get($vehicle, 'weapon_storage');
    $suitStorage = data_get($vehicle, 'suit_storage');
@endphp

@if ($weaponStorage !== null || $suitStorage !== null)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">Storage</h2>

            <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
                @if ($suitStorage !== null)
                    <x-dl-section title="Suit Storage">
                        <x-dt-dd label="Lockers">
                            {{ data_get($suitStorage, 'lockers') }}
                        </x-dt-dd>
                        <x-dt-dd label="Total Slots">
                            {{ Format::numberOrDash(data_get($suitStorage, 'slots_total')) }}
                        </x-dt-dd>
                    </x-dl-section>
                @endif

                @if ($weaponStorage !== null)
                    <x-dl-section title="Weapon Storage">
                        <x-dt-dd label="Racks">
                            {{ data_get($weaponStorage, 'lockers') }}
                        </x-dt-dd>
                        <x-dt-dd label="Rifle Slots">
                            {{ Format::numberOrDash(data_get($weaponStorage, 'slots_rifle')) }}
                        </x-dt-dd>
                        <x-dt-dd label="Pistol Slots">
                            {{ Format::numberOrDash(data_get($weaponStorage, 'slots_pistol')) }}
                        </x-dt-dd>
                    </x-dl-section>
                @endif
            </div>
        </div>
    </section>
@endif
