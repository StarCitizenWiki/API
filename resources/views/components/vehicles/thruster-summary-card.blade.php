@props(['vehicle'])

@php
    $propulsion = data_get($vehicle, 'propulsion', []);
    $thrusters = data_get($propulsion, 'thrusters', []);

    $thrusterRows = array_values(array_filter(
        $thrusters,
        static fn (array $t): bool => ($t['type'] ?? null) !== null
    ));
@endphp

@if ($thrusterRows !== [])
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Thruster Groups</h2>

            <x-dl-section :dlClass="'grid grid-cols-4 gap-x-3 gap-y-2'">
                <div class="text-xs font-light uppercase tracking-wide text-subtle">Type</div>
                <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Count</div>
                <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Capacity</div>
                <div class="text-right text-xs font-light uppercase tracking-wide text-subtle">Accel</div>

                @foreach ($thrusterRows as $thruster)
                    <dt class="text-xs font-light uppercase tracking-wide text-subtle">{{ $thruster['type'] }}</dt>
                    <dd class="text-right text-sm font-semibold text-base-content">{{ (int) ($thruster['count'] ?? 0) }}</dd>
                    <dd class="text-right text-sm font-semibold text-base-content">
                        @if (($thruster['capacity'] ?? null) !== null)
                            {{ number_format((float) $thruster['capacity'], 2) }} MN
                        @else
                            -
                        @endif
                    </dd>
                    <dd class="text-right text-sm font-semibold text-base-content">
                        @if (($thruster['g'] ?? null) !== null)
                            {{ number_format((float) $thruster['g'], 2) }} G
                        @else
                            -
                        @endif
                    </dd>
                @endforeach
            </x-dl-section>
        </div>
    </section>
@endif
