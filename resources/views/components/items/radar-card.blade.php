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

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Radar</h2>

        <!-- Primary Data: Always Visible -->
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Cooldown</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($cooldown, 's', 2) }}</dd>
            </div>
        </dl>

         <!-- Secondary Data: Expanded by Default -->
         @if ($hasSensitivity)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Sensitivity
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($sensIr !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Infrared</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($sensIr, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensCs !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($sensCs, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensEm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($sensEm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensResource !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Resource</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($sensResource, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sensDb !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">dB</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($sensDb, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasGroundVehicleSensitivity)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Ground Vehicle Sensitivity
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($gvsIr !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Infrared</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($gvsIr, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsCs !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($gvsCs, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsEm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($gvsEm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsResource !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Resource</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($gvsResource, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gvsDb !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">dB</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($gvsDb, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

         <!-- Tertiary Data: Collapsed by Default -->
         @if ($hasPiercing)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Piercing
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($pierceIr !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Infrared</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($pierceIr, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceCs !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($pierceCs, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceEm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($pierceEm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceResource !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Resource</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($pierceResource, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pierceDb !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">dB</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($pierceDb, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
