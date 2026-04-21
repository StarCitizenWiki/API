@props([
    'ammunition',
 ])

@php
    $uuid = data_get($ammunition, 'uuid');
    $speed = data_get($ammunition, 'speed');
    $lifetime = data_get($ammunition, 'lifetime');
    $range = data_get($ammunition, 'range');
    $size = data_get($ammunition, 'size');
    $capacity = data_get($ammunition, 'capacity');
    $initialCapacity = data_get($ammunition, 'initial_capacity');
    $bulletType = data_get($ammunition, 'bullet_type');
    $penetration = data_get($ammunition, 'penetration');
    $impactDamageMap = data_get($ammunition, 'impact_damage_map', []);
    $detonationDamageMap = data_get($ammunition, 'detonation_damage_map', []);
    $explosionRadius = data_get($ammunition, 'explosion_radius');
    $damageDropMinDistance = data_get($ammunition, 'damage_drop_min_distance');
    $damageDropPerMeter = data_get($ammunition, 'damage_drop_per_meter');
    $damageDropMinDamage = data_get($ammunition, 'damage_drop_min_damage');
    $bulletImpulseFalloff = data_get($ammunition, 'bullet_impulse_falloff');
    $bulletElectron = data_get($ammunition, 'bullet_electron');

    $nonZeroImpact = array_filter($impactDamageMap, fn($v) => $v !== null && $v > 0);
    $nonZeroDetonation = array_filter($detonationDamageMap, fn($v) => $v !== null && $v > 0);

    $hasPenetration = is_array($penetration) && $penetration !== [];
    $hasImpactDamage = $nonZeroImpact !== [];
    $hasDetonationDamage = $nonZeroDetonation !== [];
    $hasExplosionRadius = is_array($explosionRadius) && $explosionRadius !== [];
    $hasDamageDrop = (is_array($damageDropMinDistance) &&  collect($damageDropMinDistance)->every(fn ($key) => !empty($key))) ||
                    (is_array($damageDropPerMeter) &&  collect($damageDropPerMeter)->every(fn ($key) => !empty($key))) ||
                    (is_array($damageDropMinDamage) &&  collect($damageDropMinDamage)->every(fn ($key) => !empty($key)));
    $hasBulletImpulseFalloff = is_array($bulletImpulseFalloff) && collect($bulletImpulseFalloff)->filter()->isNotEmpty();
    $hasBulletElectron = is_array($bulletElectron) && $bulletElectron !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Ammunition</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($size !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Size</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($size, 0) }}</dd>
                </div>
            @endif
            @if ($range !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Range</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($range, 'm', 0) }}</dd>
                </div>
            @endif
            @if ($speed !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Speed</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($speed, 'm/s', 0) }}</dd>
                </div>
            @endif
            @if ($lifetime !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Lifetime</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($lifetime, 's', 2) }}</dd>
                </div>
            @endif


            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Capacity</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($capacity, 0) }}</dd>
                </div>
            @endif
            @if ($initialCapacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Initial Capacity</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($initialCapacity, 0) }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasPenetration)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Penetration
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($penetration, 'base_distance'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Base Distance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($penetration, 'base_distance'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'near_radius'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Near Radius</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($penetration, 'near_radius'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'far_radius'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Far Radius</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($penetration, 'far_radius'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'angle'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Angle</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($penetration, 'angle'), 'deg', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasImpactDamage)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Impact Damage
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($nonZeroImpact, 'physical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroImpact, 'physical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'energy'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroImpact, 'energy'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'distortion'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroImpact, 'distortion'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'thermal'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thermal</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroImpact, 'thermal'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'biochemical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Biochemical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroImpact, 'biochemical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'stun'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Stun</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroImpact, 'stun'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasDetonationDamage)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Detonation Damage
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($nonZeroDetonation, 'physical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDetonation, 'physical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'energy'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDetonation, 'energy'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'distortion'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDetonation, 'distortion'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'thermal'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thermal</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDetonation, 'thermal'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'biochemical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Biochemical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDetonation, 'biochemical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'stun'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Stun</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDetonation, 'stun'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasExplosionRadius)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Explosion Radius
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($explosionRadius, 'min') || data_get($explosionRadius, 'max'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Radius</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_range(data_get($explosionRadius, 'min'), data_get($explosionRadius, 'max'), 'm', 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasDamageDrop)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Damage Drop
                </summary>
                <div class="collapse-content space-y-6">
                    @if (is_array($damageDropMinDistance) && $damageDropMinDistance !== [])
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wide text-base-content/45 mb-3">Min Distance</h4>
                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                @if (data_get($damageDropMinDistance, 'physical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'physical'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'energy'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'energy'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'distortion'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'distortion'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'thermal'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thermal</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'thermal'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'biochemical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Biochemical</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'biochemical'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'stun'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Stun</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'stun'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'total'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Total</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'total'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if (is_array($damageDropPerMeter) && $damageDropPerMeter !== [])
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wide text-base-content/45 mb-3">Per Meter</h4>
                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                @if (data_get($damageDropPerMeter, 'physical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropPerMeter, 'physical'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'energy'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropPerMeter, 'energy'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'distortion'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropPerMeter, 'distortion'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'thermal'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thermal</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropPerMeter, 'thermal'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'biochemical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Biochemical</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropPerMeter, 'biochemical'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'stun'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Stun</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropPerMeter, 'stun'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'total'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Total</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropPerMeter, 'total'), 2) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if (is_array($damageDropMinDamage) && $damageDropMinDamage !== [])
                        <div>
                            <h4 class="text-xs font-medium uppercase tracking-wide text-base-content/45 mb-3">Min Damage</h4>
                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                @if (data_get($damageDropMinDamage, 'physical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropMinDamage, 'physical'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'energy'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropMinDamage, 'energy'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'distortion'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropMinDamage, 'distortion'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'thermal'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thermal</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropMinDamage, 'thermal'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'biochemical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Biochemical</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropMinDamage, 'biochemical'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'stun'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Stun</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropMinDamage, 'stun'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'total'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Total</dt>
                                        <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($damageDropMinDamage, 'total'), 0) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
            </details>
        @endif

        @if ($hasBulletImpulseFalloff)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Bullet Impulse Falloff
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($bulletImpulseFalloff, 'min_distance'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Min Distance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($bulletImpulseFalloff, 'min_distance'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletImpulseFalloff, 'drop_falloff'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Drop Falloff</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($bulletImpulseFalloff, 'drop_falloff'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletImpulseFalloff, 'max_falloff'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Falloff</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($bulletImpulseFalloff, 'max_falloff'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasBulletElectron)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Bullet Electron
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($bulletElectron, 'jump_range'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Jump Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($bulletElectron, 'jump_range'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletElectron, 'maximum_jumps'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Maximum Jumps</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($bulletElectron, 'maximum_jumps'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
