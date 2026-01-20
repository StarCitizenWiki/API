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
    $hasIronSight = is_array($ironSight) && array_filter($ironSight, static fn($v) => $v !== null);

    $laserPointer = data_get($weaponAttachment, 'laser_pointer', []);
    $laserRange = data_get($laserPointer, 'range');
    $laserColor = data_get($laserPointer, 'color');
    $laserColorCss = data_get($laserPointer, 'color_css');
    $hasLaserPointer = is_array($laserPointer) && array_filter($laserPointer, static fn($v) => $v !== null);

    $flashlight = data_get($weaponAttachment, 'flashlight', []);
    $hasFlashlight = is_array($flashlight) && $flashlight !== [];

    $magazine = data_get($weaponAttachment, 'magazine', []);
    $magInitialAmmoCount = data_get($magazine, 'initial_ammo_count');
    $magMaxAmmoCount = data_get($magazine, 'max_ammo_count');
    $magMaxRestockCount = data_get($magazine, 'max_restock_count');
    $hasMagazine = is_array($magazine) && array_filter($magazine, static fn($v) => $v !== null);

    $compensator = data_get($weaponAttachment, 'compensator', data_get($weaponAttachment, 'stabilizer', []));
    $compAttachmentPoint = data_get($compensator, 'attachment_point');
    $compType = data_get($compensator, 'type');
    $hasCompensator = is_array($compensator) && array_filter($compensator, static fn($v) => $v !== null);

    $flashHider = data_get($weaponAttachment, 'flash_hider', []);
    $flashHiderAttachmentPoint = data_get($flashHider, 'attachment_point');
    $flashHiderType = data_get($flashHider, 'type');
    $hasFlashHider = is_array($flashHider) && array_filter($flashHider, static fn($v) => $v !== null);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="puzzle" class="size-4 text-primary" />
            <span>Weapon Attachment Specifications</span>
        </h2>

        @if ($hasIronSight)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Iron Sight</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($ironDefaultRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Default Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$ironDefaultRange, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($ironMaxRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$ironMaxRange, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($ironRangeIncrement !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range Increment</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$ironRangeIncrement, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($ironAutoZeroingTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Auto Zeroing Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$ironAutoZeroingTime, 2) }} s</dd>
                            </div>
                        @endif
                        @if ($ironZoomScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Scale</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$ironZoomScale, 2) }}</dd>
                            </div>
                        @endif
                        @if ($ironZoomTimeScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Time Scale</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$ironZoomTimeScale, 2) }}</dd>
                            </div>
                        @endif
                        @if ($ironZoomTimeChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Time Change</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$ironZoomTimeChange, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasLaserPointer)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Laser Pointer</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($laserRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$laserRange, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($laserColorCss !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Color
                                </dt>
                                <dd class="text-sm font-medium">
                                    @if ($laserColorCss)
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded border border-base-300"
                                                 style="background-color: {{ $laserColorCss }};"></div>
                                            <span>{{ $laserColorCss }}</span>
                                        </div>
                                    @endif
                                    <div class="grid grid-cols-3 gap-2 text-xs mt-1">
                                        <div>R: {{ number_format((float)data_get($laserColor, 'r'), 2) }}</div>
                                        <div>G: {{ number_format((float)data_get($laserColor, 'g'), 2) }}</div>
                                        <div>B: {{ number_format((float)data_get($laserColor, 'b'), 2) }}</div>
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasFlashlight)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Flashlight</div>
                <div class="collapse-content">
                    @foreach ($flashlight as $profileType => $profile)
                        @php
                            $hasProfileData = is_array($profile) && array_filter($profile, static fn($v) => $v !== null);
                        @endphp
                        @if ($hasProfileData)
                            <div class="mb-6 last:mb-0">
                                <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">
                                    {{ \Illuminate\Support\Str::headline($profileType) }}
                                </div>
                                <dl class="grid gap-4 sm:grid-cols-2">
                                    @if (data_get($profile, 'port_name'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Port Name
                                            </dt>
                                            <dd class="text-sm font-medium">{{ data_get($profile, 'port_name') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'name'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Name
                                            </dt>
                                            <dd class="text-sm font-medium">{{ data_get($profile, 'name') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'light_type'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Light Type
                                            </dt>
                                            <dd class="text-sm font-medium">{{ data_get($profile, 'light_type') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'light_radius'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Light Radius
                                            </dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($profile, 'light_radius'), 2) }}
                                                m
                                            </dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'intensity'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Intensity
                                            </dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($profile, 'intensity'), 2) }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($profile, 'color_css'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Color
                                            </dt>
                                            <dd class="text-sm font-medium">
                                                @if (data_get($profile, 'color_css'))
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded border border-base-300"
                                                             style="background-color: {{ data_get($profile, 'color_css') }};"></div>
                                                        <span>{{ data_get($profile, 'color_css') }}</span>
                                                    </div>
                                                @endif
                                                <div class="grid grid-cols-3 gap-2 text-xs mt-1">
                                                    <div>
                                                        R: {{ number_format((float)data_get($profile, 'color.r',0), 2) }}</div>
                                                    <div>
                                                        G: {{ number_format((float)data_get($profile, 'color.g',0), 2) }}</div>
                                                    <div>
                                                        B: {{ number_format((float)data_get($profile, 'color.b',0), 2) }}</div>
                                                </div>
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if ($hasMagazine)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Magazine</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($magInitialAmmoCount !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Initial
                                    Ammo Count
                                </dt>
                                <dd class="text-sm font-medium">{{ (int)$magInitialAmmoCount }}</dd>
                            </div>
                        @endif
                        @if ($magMaxAmmoCount !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Ammo
                                    Count
                                </dt>
                                <dd class="text-sm font-medium">{{ (int)$magMaxAmmoCount }}</dd>
                            </div>
                        @endif
                        @if ($magMaxRestockCount !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max
                                    Restock Count
                                </dt>
                                <dd class="text-sm font-medium">{{ (int)$magMaxRestockCount }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasCompensator)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Compensator</div>
                <div class="collapse-content">
                    @php
                        $knownFields = ['attachment_point', 'type'];
                        $additionalFields = array_diff_key($compensator, array_flip($knownFields));
                        $hasAdditionalFields = is_array($additionalFields) && $additionalFields !== [];
                    @endphp
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($compAttachmentPoint !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                    Attachment Point
                                </dt>
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

                    @if ($hasAdditionalFields)
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100 mt-4">
                            <input type="checkbox"/>
                            <div class="collapse-title text-sm font-semibold">Additional Fields</div>
                            <div class="collapse-content">
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($additionalFields as $key => $value)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($key) }}</dt>
                                            <dd class="text-sm">{{ is_numeric($value) ? number_format((float)$value, 2) : $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasFlashHider)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Flash Hider</div>
                <div class="collapse-content">
                    @php
                        $knownFields = ['attachment_point', 'type'];
                        $additionalFields = array_diff_key($flashHider, array_flip($knownFields));
                        $hasAdditionalFields = is_array($additionalFields) && $additionalFields !== [];
                    @endphp
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($flashHiderAttachmentPoint !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                    Attachment Point
                                </dt>
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

                    @if ($hasAdditionalFields)
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100 mt-4">
                            <input type="checkbox"/>
                            <div class="collapse-title text-sm font-semibold">Additional Fields</div>
                            <div class="collapse-content">
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($additionalFields as $key => $value)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($key) }}</dt>
                                            <dd class="text-sm">{{ is_numeric($value) ? number_format((float)$value, 2) : $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
