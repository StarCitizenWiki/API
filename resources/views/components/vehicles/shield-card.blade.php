@use('App\Support\Format')
@props(['vehicle'])

@php
    $shield = data_get($vehicle, 'shield', []);
    $hp = data_get($shield, 'hp');
    $regeneration = data_get($shield, 'regeneration');
    $faceType = data_get($shield, 'face_type');

    $resistance = data_get($shield, 'resistance', []);

    $resistanceRows = array_values(array_filter([
        ['label' => 'Physical', 'maximum' => data_get($resistance, 'physical.maximum')],
        ['label' => 'Energy', 'maximum' => data_get($resistance, 'energy.maximum')],
        ['label' => 'Distortion', 'maximum' => data_get($resistance, 'distortion.maximum')],
    ], static fn (array $row): bool => $row['maximum'] !== null));

    $hasShieldData = ! empty($hp) || ! empty($regeneration) || $faceType !== null || $resistanceRows !== [];
@endphp

@if ($hasShieldData)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Shield</h2>

            <div class="grid gap-12 lg:grid-cols-2">
                <x-dl-section title="Info">
                    @if ($faceType !== null)
                        <x-dt-dd label="Face Type">{{ $faceType }}</x-dt-dd>
                    @endif

                    @if ($hp !== null)
                        <x-dt-dd label="Hit Points">
                            {{ Format::numberOrDash($hp) }} <span class="text-xs text-muted">HP</span>
                        </x-dt-dd>
                    @endif

                    @if ($regeneration !== null)
                        <x-dt-dd label="Regeneration">
                            {{ Format::numberOrDash($regeneration) }} <span class="text-xs text-muted">HP/s</span>
                        </x-dt-dd>
                    @endif
                </x-dl-section>

                @if ($resistanceRows !== [])
                    <x-dl-section title="Resistance">
                        @foreach ($resistanceRows as $row)
                            <x-dt-dd :label="$row['label']">
                                <span class="{{ Format::colorClass($row['maximum'], true) }}">{{ Format::percentOrDash($row['maximum']) }}</span>
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif
            </div>
        </div>
    </section>
@endif
