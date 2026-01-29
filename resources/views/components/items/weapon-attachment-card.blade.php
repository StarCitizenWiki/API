@props([
    'weaponAttachment',
 ])

@php
    $ironSight = data_get($weaponAttachment, 'iron_sight', []);
    $ironDefaultRange = data_get($ironSight, 'default_range');
    $ironMaxRange = data_get($ironSight, 'max_range');
    $ironRangeIncrement = data_get($ironSight, 'range_increment');
    $ironAutoZeroingTime = data_get($ironSight, 'auto_zeroing_time');
    $ironZoomScale = data_get($ironSight, 'zoom_scale');
    $ironZoomTimeScale = data_get($ironSight, 'zoom_time_scale');
    $ironZoomTimeChange = data_get($ironSight, 'zoom_time_change');
    $hasIronSight = is_array($ironSight) && collect($ironSight)->filter(static fn($v) => $v !== null)->isNotEmpty();

    $laserPointer = data_get($weaponAttachment, 'laser_pointer', []);
    $laserRange = data_get($laserPointer, 'range');
    $laserColor = data_get($laserPointer, 'color');
    $laserColorCss = data_get($laserPointer, 'color_css');
    $hasLaserPointer = is_array($laserPointer) && collect($laserPointer)->filter(static fn($v) => $v !== null)->isNotEmpty();

    $flashlight = data_get($weaponAttachment, 'flashlight', []);
    $hasFlashlight = is_array($flashlight) && $flashlight !== [];

    $magazine = data_get($weaponAttachment, 'magazine', []);
    $magInitialAmmoCount = data_get($magazine, 'initial_ammo_count');
    $magMaxAmmoCount = data_get($magazine, 'max_ammo_count');
    $magMaxRestockCount = data_get($magazine, 'max_restock_count');
    $hasMagazine = is_array($magazine) && collect($magazine)->filter(static fn($v) => $v !== null)->isNotEmpty();

    $compensator = data_get($weaponAttachment, 'compensator', data_get($weaponAttachment, 'stabilizer', []));
    $compAttachmentPoint = data_get($compensator, 'attachment_point');
    $compType = data_get($compensator, 'type');
    $hasCompensator = is_array($compensator) && collect($compensator)->filter(static fn($v) => $v !== null)->isNotEmpty();

    $flashHider = data_get($weaponAttachment, 'flash_hider', []);
    $flashHiderAttachmentPoint = data_get($flashHider, 'attachment_point');
    $flashHiderType = data_get($flashHider, 'type');
    $hasFlashHider = is_array($flashHider) && collect($flashHider)->filter(static fn($v) => $v !== null)->isNotEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title flex items-center gap-2">
            <x-icon name="attachment" class="size-4 text-primary" />
            <span>Weapon Attachment</span>
        </h2>

        {{-- PRIMARY DATA: Always Visible --}}
        @if ($hasIronSight)
            <h3 class="text-sm font-semibold">Iron Sight</h3>
            <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @if ($ironDefaultRange !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Default Range</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit($ironDefaultRange, 'm', 2) }}</dd>
                    </div>
                @endif
                @if ($ironMaxRange !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Range</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit($ironMaxRange, 'm', 2) }}</dd>
                    </div>
                @endif
                @if ($ironZoomScale !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Scale</dt>
                        <dd class="text-sm font-medium">{{ fmt_or_dash($ironZoomScale, 2) }}</dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($hasLaserPointer)
            <h3 class="text-sm font-semibold">Laser Pointer</h3>
            <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @if ($laserRange !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit($laserRange, 'm', 2) }}</dd>
                    </div>
                @endif
                @if ($laserColorCss !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Color</dt>
                        <dd class="text-sm font-medium">
                            @if ($laserColorCss)
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded border border-base-300" style="background-color: {{ $laserColorCss }};"></div>
                                    <span>{{ $laserColorCss }}</span>
                                </div>
                            @endif
                            <div class="grid grid-cols-3 gap-2 text-xs mt-1">
                                <div>R: {{ fmt_or_dash(data_get($laserColor, 'r'), 2) }}</div>
                                <div>G: {{ fmt_or_dash(data_get($laserColor, 'g'), 2) }}</div>
                                <div>B: {{ fmt_or_dash(data_get($laserColor, 'b'), 2) }}</div>
                            </div>
                        </dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($hasMagazine)
            <h3 class="text-sm font-semibold">Magazine</h3>
            <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @if ($magInitialAmmoCount !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Initial Ammo Count</dt>
                        <dd class="text-sm font-medium">{{ fmt_or_dash($magInitialAmmoCount, 0) }}</dd>
                    </div>
                @endif
                @if ($magMaxAmmoCount !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Ammo Count</dt>
                        <dd class="text-sm font-medium">{{ fmt_or_dash($magMaxAmmoCount, 0) }}</dd>
                    </div>
                @endif
                @if ($magMaxRestockCount !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Restock Count</dt>
                        <dd class="text-sm font-medium">{{ fmt_or_dash($magMaxRestockCount, 0) }}</dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($hasCompensator)
            <h3 class="text-sm font-semibold">Compensator</h3>
            <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @if ($compAttachmentPoint !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Attachment Point</dt>
                        <dd class="text-sm font-medium">{{ $compAttachmentPoint }}</dd>
                    </div>
                @endif
                @if ($compType !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                        <dd class="text-sm font-medium">{{ $compType }}</dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($hasFlashHider)
            <h3 class="text-sm font-semibold">Flash Hider</h3>
            <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @if ($flashHiderAttachmentPoint !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Attachment Point</dt>
                        <dd class="text-sm font-medium">{{ $flashHiderAttachmentPoint }}</dd>
                    </div>
                @endif
                @if ($flashHiderType !== null)
                    <div class="space-y-1">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                        <dd class="text-sm font-medium">{{ $flashHiderType }}</dd>
                    </div>
                @endif
            </dl>
        @endif

        {{-- SECONDARY DATA: Collapsible, default open --}}
        @if ($hasIronSight && ($ironRangeIncrement !== null || $ironAutoZeroingTime !== null || $ironZoomTimeScale !== null || $ironZoomTimeChange !== null))
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Iron Sight Details
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($ironRangeIncrement !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range Increment</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($ironRangeIncrement, 'm', 2) }}</dd>
                            </div>
                        @endif
                        @if ($ironAutoZeroingTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Auto Zeroing Time</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($ironAutoZeroingTime, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($ironZoomTimeScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Time Scale</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($ironZoomTimeScale, 2) }}</dd>
                            </div>
                        @endif
                        @if ($ironZoomTimeChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Time Change</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($ironZoomTimeChange, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasFlashlight)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Flashlight Profiles
                </summary>
                <div class="collapse-content">
                    @foreach ($flashlight as $profileType => $profile)
                        @php
                            $hasProfileData = is_array($profile) && collect($profile)->filter(static fn($v) => $v !== null)->isNotEmpty();
                        @endphp
                        @if ($hasProfileData)
                            <div class="mb-4 last:mb-0">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">
                                    {{ \Illuminate\Support\Str::headline($profileType) }}
                                </h4>
                                <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                                    @if (data_get($profile, 'port_name'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Port Name</dt>
                                            <dd class="text-sm font-medium">{{ data_get($profile, 'port_name') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'name'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Name</dt>
                                            <dd class="text-sm font-medium">{{ data_get($profile, 'name') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'light_type'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Light Type</dt>
                                            <dd class="text-sm font-medium">{{ data_get($profile, 'light_type') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'light_radius'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Light Radius</dt>
                                            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'light_radius'), 'm', 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'intensity'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Intensity</dt>
                                            <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($profile, 'intensity'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'color_css'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Color</dt>
                                            <dd class="text-sm font-medium">
                                                @if (data_get($profile, 'color_css'))
                                                    <div class="flex items-center gap-2">
                                                        <div class="size-8 rounded border border-base-300" style="background-color: {{ data_get($profile, 'color_css') }};"></div>
                                                        <span>{{ data_get($profile, 'color_css') }}</span>
                                                    </div>
                                                @endif
                                                <div class="grid grid-cols-3 gap-2 text-xs mt-1">
                                                    <div>R: {{ fmt_or_dash(data_get($profile, 'color.r', 0), 2) }}</div>
                                                    <div>G: {{ fmt_or_dash(data_get($profile, 'color.g', 0), 2) }}</div>
                                                    <div>B: {{ fmt_or_dash(data_get($profile, 'color.b', 0), 2) }}</div>
                                                </div>
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    @endforeach
                </div>
            </details>
        @endif

        {{-- TERTIARY DATA: Collapsible, default closed --}}
        @if ($hasCompensator)
            @php
                $knownFields = ['attachment_point', 'type'];
                $additionalFields = array_diff_key($compensator, array_flip($knownFields));
                $hasAdditionalFields = is_array($additionalFields) && $additionalFields !== [];
            @endphp
            @if ($hasAdditionalFields && count($additionalFields) >= 2)
                <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                        Compensator Advanced
                    </summary>
                    <div class="collapse-content">
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                            @foreach ($additionalFields as $key => $value)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($key) }}</dt>
                                    <dd class="text-sm">{{ is_numeric($value) ? fmt_or_dash($value, 2) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </details>
            @endif
        @endif

        @if ($hasFlashHider)
            @php
                $knownFields = ['attachment_point', 'type'];
                $additionalFields = array_diff_key($flashHider, array_flip($knownFields));
                $hasAdditionalFields = is_array($additionalFields) && $additionalFields !== [];
            @endphp
            @if ($hasAdditionalFields && count($additionalFields) >= 2)
                <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                        Flash Hider Advanced
                    </summary>
                    <div class="collapse-content">
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                            @foreach ($additionalFields as $key => $value)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($key) }}</dt>
                                    <dd class="text-sm">{{ is_numeric($value) ? fmt_or_dash($value, 2) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </details>
            @endif
        @endif
    </div>
</div>
