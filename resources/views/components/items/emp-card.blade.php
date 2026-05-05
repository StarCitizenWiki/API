@use('App\Support\Format')
@props([
    'emp',
])

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">EMP Generator</h2>

        <x-dl-container>
            <x-slot:head>
                <x-dt-dd label="EMP Radius">{{ Format::range(data_get($emp, 'min_emp_radius'), data_get($emp, 'emp_radius'), 'm', 2) }}</x-dt-dd>
                <x-dt-dd label="Charge Duration">{{ Format::valueWithUnit(data_get($emp, 'charge_duration'), 's', 2) }}</x-dt-dd>
                <x-dt-dd label="Unleash Duration">{{ Format::valueWithUnit(data_get($emp, 'unleash_duration'), 's', 2) }}</x-dt-dd>
                <x-dt-dd label="Cooldown Duration">{{ Format::valueWithUnit(data_get($emp, 'cooldown_duration'), 's', 2) }}</x-dt-dd>
                <x-dt-dd label="Distortion Damage">{{ Format::valueWithUnit(data_get($emp, 'distortion_damage'), 'N', 2) }}</x-dt-dd>
            </x-slot:head>
        </x-dl-container>
    </div>
</div>
