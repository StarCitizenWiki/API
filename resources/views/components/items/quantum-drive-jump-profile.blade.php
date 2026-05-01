@props(['profile' => []])

<dl class="grid gap-4 grid-cols-2 sm:grid-cols-2">
    @if (data_get($profile, 'drive_speed'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Drive Speed</dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($profile, 'drive_speed'), 'm/s', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'cooldown_time'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cooldown Time</dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($profile, 'cooldown_time'), 's', 1) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'stage_one_accel_rate'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stage One Acceleration Rate
            </dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($profile, 'stage_one_accel_rate'), 'm/s²', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'stage_two_accel_rate'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stage Two Acceleration Rate
            </dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($profile, 'stage_two_accel_rate'), 'm/s²', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'engage_speed'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Engage Speed</dt>
            <dd class="text-sm font-semibold text-base-content">{{ (int)data_get($profile, 'engage_speed') }} m/s</dd>
        </div>
    @endif

    @if (data_get($profile, 'interdiction_effect_time'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Interdiction Effect Time</dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($profile, 'interdiction_effect_time'), 's', 2) }}
            </dd>
        </div>
    @endif

    @if (data_get($profile, 'calibration_rate'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Calibration Rate</dt>
            <dd class="text-sm font-semibold text-base-content">{{ (int)data_get($profile, 'calibration_rate') }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'min_calibration_requirement'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Calibration Requirement</dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_range(data_get($profile, 'min_calibration_requirement'), data_get($profile, 'max_calibration_requirement'), '', 0) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'calibration_process_angle_limit'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Calibration Angle</dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_range(data_get($profile, 'calibration_process_angle_limit'), data_get($profile, 'calibration_warning_angle_limit'), 'deg', 1) }}</dd>
        </div>
    @endif

    @if (data_get($profile, 'calibration_delay_in_seconds'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Calibration Delay</dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($profile, 'calibration_delay_in_seconds'), 's', 1) }}
            </dd>
        </div>
    @endif

    @if (data_get($profile, 'spool_up_time'))
        <div class="space-y-1">
            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Spool Up Time</dt>
            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($profile, 'spool_up_time'), 's', 1) }}</dd>
        </div>
    @endif
</dl>
