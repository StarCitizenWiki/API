@props(['profile' => []])

<dl class="grid gap-4 sm:grid-cols-2">
    @if (data_get($profile, 'drive_speed'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drive Speed</dt>
            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'drive_speed'), 'm/s', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'cooldown_time'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Time</dt>
            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'cooldown_time'), 's', 1) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'stage_one_accel_rate'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stage One Acceleration Rate
            </dt>
            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'stage_one_accel_rate'), 'm/s²', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'stage_two_accel_rate'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stage Two Acceleration Rate
            </dt>
            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'stage_two_accel_rate'), 'm/s²', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'engage_speed'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Engage Speed</dt>
            <dd class="text-sm font-medium">{{ (int)data_get($profile, 'engage_speed') }} m/s</dd>
        </div>
    @endif

    @if (data_get($profile, 'interdiction_effect_time'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Interdiction Effect Time</dt>
            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'interdiction_effect_time'), 's', 2) }}
            </dd>
        </div>
    @endif

    @if (data_get($profile, 'calibration_rate'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Calibration Rate</dt>
            <dd class="text-sm font-medium">{{ (int)data_get($profile, 'calibration_rate') }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'min_calibration_requirement'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Calibration Requirement</dt>
            <dd class="text-sm font-medium">{{ fmt_range(data_get($profile, 'min_calibration_requirement'), data_get($profile, 'max_calibration_requirement'), '', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'calibration_process_angle_limit'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Calibration Angle</dt>
            <dd class="text-sm font-medium">{{ fmt_range(data_get($profile, 'calibration_process_angle_limit'), data_get($profile, 'calibration_warning_angle_limit'), 'deg', 1) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'calibration_delay_in_seconds'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Calibration Delay</dt>
            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'calibration_delay_in_seconds'), 's', 1) }}
            </dd>
        </div>
    @endif

    @if (data_get($profile, 'spool_up_time'))
        <div class="space-y-1">
            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Spool Up Time</dt>
            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($profile, 'spool_up_time'), 's', 1) }}</dd>
        </div>
    @endif
</dl>
