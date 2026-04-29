@props(['vehicle'])

@php
    $shield = data_get($vehicle, 'shield', []);
    $hp = data_get($shield, 'hp');
    $regeneration = data_get($shield, 'regeneration');
    $faceType = data_get($shield, 'face_type');

    $resistance = data_get($shield, 'resistance', []);

    $formatWholeNumber = static fn (mixed $value): string => $value === null ? '-' : number_format((float) $value, 0);

    $formatPercent = static function (mixed $value): string {
        if ($value === null) {
            return '-';
        }

        $pct = (float) $value * 100;

        return number_format($pct, 1) . '%';
    };

    $resistanceRows = array_values(array_filter([
        ['label' => 'Physical', 'maximum' => data_get($resistance, 'physical.maximum')],
        ['label' => 'Energy', 'maximum' => data_get($resistance, 'energy.maximum')],
        ['label' => 'Distortion', 'maximum' => data_get($resistance, 'distortion.maximum')],
    ], static fn (array $row): bool => $row['maximum'] !== null));

    $hasShieldData = $hp !== null || $regeneration !== null || $faceType !== null || $resistanceRows !== [];
@endphp

@if ($hasShieldData)
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Shield</h2>

            <div class="grid gap-12 lg:grid-cols-2">
                <section class="space-y-4">
                    <div class="space-y-1">
                        <h3 class="text-sm font-semibold text-base-content">Info</h3>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-3 gap-y-2">
                        @if ($faceType !== null)
                            <dt class="text-sm text-base-content/80">Face Type</dt>
                            <dd class="text-right text-sm font-semibold text-base-content">{{ $faceType }}</dd>
                        @endif

                        @if ($hp !== null)
                            <dt class="text-sm text-base-content/80">Hit Points</dt>
                            <dd class="text-right text-sm font-semibold text-base-content">
                                {{ $formatWholeNumber($hp) }} <span class="text-xs text-base-content/45">HP</span>
                            </dd>
                        @endif

                        @if ($regeneration !== null)
                            <dt class="text-sm text-base-content/80">Regeneration</dt>
                            <dd class="text-right text-sm font-semibold text-base-content">
                                {{ $formatWholeNumber($regeneration) }} <span class="text-xs text-base-content/45">HP/s</span>
                            </dd>
                        @endif
                    </dl>
                </section>

                @if ($resistanceRows !== [])
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Resistance</h3>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">
                            @foreach ($resistanceRows as $row)
                                <dt class="text-sm text-base-content/80">{{ $row['label'] }}</dt>
                                <dd class="text-right text-sm font-semibold text-base-content {{ color_class($row['maximum'], true) }}">
                                    {{ $formatPercent($row['maximum']) }}
                                </dd>
                            @endforeach
                        </dl>
                    </section>
                @endif
            </div>
        </div>
    </section>
@endif
