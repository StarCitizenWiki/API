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
    $hasDamageDrop = is_array($damageDropMinDistance) && $damageDropMinDistance !== [] ||
                      is_array($damageDropPerMeter) && $damageDropPerMeter !== [] ||
                      is_array($damageDropMinDamage) && $damageDropMinDamage !== [];
    $hasBulletImpulseFalloff = is_array($bulletImpulseFalloff) && collect($bulletImpulseFalloff)->filter()->isNotEmpty();
    $hasBulletElectron = is_array($bulletElectron) && $bulletElectron !== [];
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="puzzle" class="size-4 text-primary" />
            <span>Ammunition</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($speed !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Speed</dt>
                    <dd class="text-sm font-medium">{{ (int)$speed }} m/s</dd>
                </div>
            @endif
            @if ($lifetime !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lifetime</dt>
                    <dd class="text-sm font-medium">{{ (int)$lifetime }}s</dd>
                </div>
            @endif
            @if ($range !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                    <dd class="text-sm font-medium">{{ (int)$range }} m</dd>
                </div>
            @endif
            @if ($size !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size</dt>
                    <dd class="text-sm font-medium">{{ (int)$size }}</dd>
                </div>
            @endif
            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                    <dd class="text-sm font-medium">{{ (int)$capacity }}</dd>
                </div>
            @endif
            @if ($initialCapacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Initial Capacity</dt>
                    <dd class="text-sm font-medium">{{ (int)$initialCapacity }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasPenetration)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Penetration</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($penetration, 'base_distance'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Base Distance</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($penetration, 'base_distance') }} m</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'near_radius'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Near Radius</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($penetration, 'near_radius') }} m</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'far_radius'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Far Radius</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($penetration, 'far_radius') }} m</dd>
                            </div>
                        @endif
                        @if (data_get($penetration, 'angle'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angle</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($penetration, 'angle') }} deg</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasImpactDamage)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Impact Damage</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                            @if (data_get($nonZeroImpact, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroImpact, 'physical') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroImpact, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroImpact, 'energy') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroImpact, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroImpact, 'distortion') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroImpact, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroImpact, 'thermal') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroImpact, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroImpact, 'biochemical') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroImpact, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroImpact, 'stun') }}</dd>
                                </div>
                            @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasDetonationDamage)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Detonation Damage</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                            @if (data_get($nonZeroDetonation, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDetonation, 'physical') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDetonation, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDetonation, 'energy') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDetonation, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDetonation, 'distortion') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDetonation, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDetonation, 'thermal') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDetonation, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDetonation, 'biochemical') }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDetonation, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDetonation, 'stun') }}</dd>
                                </div>
                            @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasExplosionRadius)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Explosion Radius</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($explosionRadius, 'min'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($explosionRadius, 'min') }} m</dd>
                            </div>
                        @endif
                        @if (data_get($explosionRadius, 'max'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($explosionRadius, 'max') }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasDamageDrop)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Damage Drop</div>
                <div class="collapse-content space-y-4">
                    @if (is_array($damageDropMinDistance) && $damageDropMinDistance !== [])
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                            <input type="checkbox" />
                            <div class="collapse-title text-xs font-semibold">Min Distance</div>
                            <div class="collapse-content">
                                <dl class="grid gap-3 sm:grid-cols-2">
                            @if (data_get($damageDropMinDistance, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDistance, 'physical') }} m</dd>
                                </div>
                            @endif
                            @if (data_get($damageDropMinDistance, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDistance, 'energy') }} m</dd>
                                </div>
                            @endif
                            @if (data_get($damageDropMinDistance, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDistance, 'distortion') }} m</dd>
                                </div>
                            @endif
                            @if (data_get($damageDropMinDistance, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDistance, 'thermal') }} m</dd>
                                </div>
                            @endif
                            @if (data_get($damageDropMinDistance, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDistance, 'biochemical') }} m</dd>
                                </div>
                            @endif
                            @if (data_get($damageDropMinDistance, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDistance, 'stun') }} m</dd>
                                </div>
                            @endif
                            @if (data_get($damageDropMinDistance, 'total'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Total</dt>
                                    <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDistance, 'total') }} m</dd>
                                </div>
                            @endif
                                </dl>
                            </div>
                        </div>
                    @endif

                    @if (is_array($damageDropPerMeter) && $damageDropPerMeter !== [])
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                            <input type="checkbox" />
                            <div class="collapse-title text-xs font-semibold">Per Meter</div>
                            <div class="collapse-content">
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @if (data_get($damageDropPerMeter, 'physical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($damageDropPerMeter, 'physical'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropPerMeter, 'energy'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($damageDropPerMeter, 'energy'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropPerMeter, 'distortion'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($damageDropPerMeter, 'distortion'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropPerMeter, 'thermal'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($damageDropPerMeter, 'thermal'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropPerMeter, 'biochemical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($damageDropPerMeter, 'biochemical'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropPerMeter, 'stun'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($damageDropPerMeter, 'stun'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropPerMeter, 'total'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Total</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($damageDropPerMeter, 'total'), 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endif

                    @if (is_array($damageDropMinDamage) && $damageDropMinDamage !== [])
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                            <input type="checkbox" />
                            <div class="collapse-title text-xs font-semibold">Min Damage</div>
                            <div class="collapse-content">
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @if (data_get($damageDropMinDamage, 'physical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDamage, 'physical') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropMinDamage, 'energy'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDamage, 'energy') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropMinDamage, 'distortion'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDamage, 'distortion') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropMinDamage, 'thermal'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDamage, 'thermal') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropMinDamage, 'biochemical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDamage, 'biochemical') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropMinDamage, 'stun'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDamage, 'stun') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($damageDropMinDamage, 'total'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Total</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($damageDropMinDamage, 'total') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasBulletImpulseFalloff)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Bullet Impulse Falloff</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($bulletImpulseFalloff, 'min_distance'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Distance</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($bulletImpulseFalloff, 'min_distance') }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletImpulseFalloff, 'drop_falloff'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drop Falloff</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($bulletImpulseFalloff, 'drop_falloff') }}</dd>
                            </div>
                        @endif
                        @if (data_get($bulletImpulseFalloff, 'max_falloff'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Falloff</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($bulletImpulseFalloff, 'max_falloff') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasBulletElectron)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Bullet Electron</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($bulletElectron, 'jump_range'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Jump Range</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($bulletElectron, 'jump_range') }} m</dd>
                            </div>
                        @endif
                        @if (data_get($bulletElectron, 'maximum_jumps'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Jumps</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($bulletElectron, 'maximum_jumps') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
