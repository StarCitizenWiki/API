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
    $hasDamageDrop = (is_array($damageDropMinDistance) && $damageDropMinDistance !== []) ||
                    (is_array($damageDropPerMeter) && $damageDropPerMeter !== []) ||
                    (is_array($damageDropMinDamage) && $damageDropMinDamage !== []);
    $hasBulletImpulseFalloff = is_array($bulletImpulseFalloff) && collect($bulletImpulseFalloff)->filter()->isNotEmpty();
    $hasBulletElectron = is_array($bulletElectron) && $bulletElectron !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="cylinder" class="size-4 text-primary" />
            <span>Ammunition</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-">
            @if ($size !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size</dt>
                    <dd class="text-sm font-medium">{{ fmt_or_dash($size, 0) }}</dd>
                </div>
            @endif
            @if ($range !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($range, 'm', 0) }}</dd>
                </div>
            @endif
            @if ($speed !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Speed</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($speed, 'm/s', 0) }}</dd>
                </div>
            @endif
            @if ($lifetime !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lifetime</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($lifetime, 's', 2) }}</dd>
                </div>
            @endif


            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                    <dd class="text-sm font-medium">{{ fmt_or_dash($capacity, 0) }}</dd>
                </div>
            @endif
            @if ($initialCapacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Initial Capacity</dt>
                    <dd class="text-sm font-medium">{{ fmt_or_dash($initialCapacity, 0) }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasPenetration)
            <details id="penetration" class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="true" aria-controls="penetration-content">
                    Penetration
                </summary>
                <div id="penetration-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if (data_get($penetration, 'base_distance'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Base Distance</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($penetration, 'base_distance'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'near_radius'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Near Radius</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($penetration, 'near_radius'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'far_radius'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Far Radius</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($penetration, 'far_radius'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'angle'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angle</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($penetration, 'angle'), 'deg', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasImpactDamage)
            <details id="impact-damage" class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="true" aria-controls="impact-damage-content">
                    Impact Damage
                </summary>
                <div id="impact-damage-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if (data_get($nonZeroImpact, 'physical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroImpact, 'physical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'energy'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroImpact, 'energy'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'distortion'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroImpact, 'distortion'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'thermal'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroImpact, 'thermal'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'biochemical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroImpact, 'biochemical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroImpact, 'stun'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroImpact, 'stun'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasDetonationDamage)
            <details id="detonation-damage" class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="true" aria-controls="detonation-damage-content">
                    Detonation Damage
                </summary>
                <div id="detonation-damage-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if (data_get($nonZeroDetonation, 'physical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDetonation, 'physical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'energy'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDetonation, 'energy'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'distortion'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDetonation, 'distortion'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'thermal'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDetonation, 'thermal'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'biochemical'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDetonation, 'biochemical'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($nonZeroDetonation, 'stun'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDetonation, 'stun'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasExplosionRadius)
            <details id="explosion-radius" class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="true" aria-controls="explosion-radius-content">
                    Explosion Radius
                </summary>
                <div id="explosion-radius-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if (data_get($explosionRadius, 'min') || data_get($explosionRadius, 'max'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radius</dt>
                                <dd class="text-sm font-medium">{{ fmt_range(data_get($explosionRadius, 'min'), data_get($explosionRadius, 'max'), 'm', 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasDamageDrop)
            <details id="damage-drop" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="damage-drop-content">
                    Damage Drop
                </summary>
                <div id="damage-drop-content" class="collapse-content space-y-6">
                    @if (is_array($damageDropMinDistance) && $damageDropMinDistance !== [])
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-3">Min Distance</h4>
                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                                @if (data_get($damageDropMinDistance, 'physical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'physical'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'energy'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'energy'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'distortion'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'distortion'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'thermal'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'thermal'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'biochemical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'biochemical'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'stun'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'stun'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDistance, 'total'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Total</dt>
                                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($damageDropMinDistance, 'total'), 'm', 0) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if (is_array($damageDropPerMeter) && $damageDropPerMeter !== [])
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-3">Per Meter</h4>
                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                                @if (data_get($damageDropPerMeter, 'physical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropPerMeter, 'physical'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'energy'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropPerMeter, 'energy'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'distortion'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropPerMeter, 'distortion'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'thermal'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropPerMeter, 'thermal'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'biochemical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropPerMeter, 'biochemical'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'stun'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropPerMeter, 'stun'), 2) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropPerMeter, 'total'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Total</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropPerMeter, 'total'), 2) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if (is_array($damageDropMinDamage) && $damageDropMinDamage !== [])
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-3">Min Damage</h4>
                            <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                                @if (data_get($damageDropMinDamage, 'physical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropMinDamage, 'physical'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'energy'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropMinDamage, 'energy'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'distortion'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropMinDamage, 'distortion'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'thermal'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropMinDamage, 'thermal'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'biochemical'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropMinDamage, 'biochemical'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'stun'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropMinDamage, 'stun'), 0) }}</dd>
                                    </div>
                                @endif
                                @if (data_get($damageDropMinDamage, 'total'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Total</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($damageDropMinDamage, 'total'), 0) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </div>
            </details>
        @endif

        @if ($hasBulletImpulseFalloff)
            <details id="bullet-impulse-falloff" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="bullet-impulse-falloff-content">
                    Bullet Impulse Falloff
                </summary>
                <div id="bullet-impulse-falloff-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        @if (data_get($bulletImpulseFalloff, 'min_distance'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Distance</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($bulletImpulseFalloff, 'min_distance'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletImpulseFalloff, 'drop_falloff'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drop Falloff</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($bulletImpulseFalloff, 'drop_falloff'), 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletImpulseFalloff, 'max_falloff'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Falloff</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($bulletImpulseFalloff, 'max_falloff'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasBulletElectron)
            <details id="bullet-electron" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="bullet-electron-content">
                    Bullet Electron
                </summary>
                <div id="bullet-electron-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        @if (data_get($bulletElectron, 'jump_range'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Jump Range</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($bulletElectron, 'jump_range'), 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletElectron, 'maximum_jumps'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Jumps</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($bulletElectron, 'maximum_jumps'), 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
