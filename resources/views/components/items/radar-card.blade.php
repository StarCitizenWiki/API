@props([
    'radar',
])

@php
    $cooldown = data_get($radar, 'cooldown');

    $sensitivity = data_get($radar, 'sensitivity', []);
    $sensIr = data_get($sensitivity, 'infrared');
    $sensCs = data_get($sensitivity, 'cross_section');
    $sensEm = data_get($sensitivity, 'electromagnetic');
    $sensResource = data_get($sensitivity, 'resource');
    $sensDb = data_get($sensitivity, 'db');

    $groundVehicleSensitivity = data_get($radar, 'ground_vehicle_sensitivity', []);
    $gvsIr = data_get($groundVehicleSensitivity, 'infrared');
    $gvsCs = data_get($groundVehicleSensitivity, 'cross_section');
    $gvsEm = data_get($groundVehicleSensitivity, 'electromagnetic');
    $gvsResource = data_get($groundVehicleSensitivity, 'resource');
    $gvsDb = data_get($groundVehicleSensitivity, 'db');

    $piercing = data_get($radar, 'piercing', []);
    $pierceIr = data_get($piercing, 'infrared');
    $pierceCs = data_get($piercing, 'cross_section');
    $pierceEm = data_get($piercing, 'electromagnetic');
    $pierceResource = data_get($piercing, 'resource');
    $pierceDb = data_get($piercing, 'db');

    $hasSensitivity = collect($sensitivity)->filter()->isNotEmpty();
    $hasGroundVehicleSensitivity = collect($groundVehicleSensitivity)->filter()->isNotEmpty();
    $hasPiercing = collect($piercing)->filter()->isNotEmpty();
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="wifi" class="size-4 text-primary" />
            <span>Radar Specifications</span>
        </h2>

        @if ($cooldown !== null)
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$cooldown, 2) }}s</dd>
                </div>
            </dl>
        @endif

        @if ($hasSensitivity)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Sensitivity</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($sensIr !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Infrared</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sensIr, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensCs !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sensCs, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensEm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sensEm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensResource !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Resource</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sensResource, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensDb !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">dB</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sensDb, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasGroundVehicleSensitivity)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Ground Vehicle Sensitivity</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($gvsIr !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Infrared</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gvsIr, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsCs !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gvsCs, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsEm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gvsEm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsResource !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Resource</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gvsResource, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsDb !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">dB</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gvsDb, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasPiercing)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Piercing</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($pierceIr !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Infrared</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pierceIr, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceCs !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pierceCs, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceEm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pierceEm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceResource !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Resource</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pierceResource, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceDb !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">dB</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pierceDb, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
