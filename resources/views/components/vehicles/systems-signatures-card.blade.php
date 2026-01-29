@props(['vehicle'])

@php
    use Illuminate\Support\Str;

    $signature = data_get($vehicle, 'signature', []);
    $cooling = data_get($vehicle, 'cooling', []);
    $power = data_get($vehicle, 'power', []);
@endphp
<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 font-semibold">
        Emission & Resource Network
    </summary>
    <div class="collapse-content gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">IR / EM Signature</h3>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">IR (Quantum Drive active)</dt>
                        <dd class="text-sm font-medium">{{ data_get($signature, 'ir_quantum') ? number_format(data_get($signature, 'ir_quantum'), 0) : '-' }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">EM Quantum</dt>
                        <dd class="text-sm font-medium">{{ data_get($signature, 'em_quantum') ? number_format(data_get($signature, 'em_quantum'), 0) : '-' }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">IR (Shields active)</dt>
                        <dd class="text-sm font-medium">{{ data_get($signature, 'ir_shields') ? number_format(data_get($signature, 'ir_shields'), 0) : '-' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">EM Shields</dt>
                        <dd class="text-sm font-medium">{{ data_get($signature, 'em_shields') ? number_format(data_get($signature, 'em_shields'), 0) : '-' }}</dd>
                    </div>

                </dl>
            </div>

            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooling</h3>
                <dl class="grid gap-4 sm:grid-cols-3 mb-3">
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Generation</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($cooling, 'generation_segments'), 'Segments', 0) }}</dd>
                    </div>
                    <div class="space-y-1" title="{{ fmt_value_with_unit(data_get($cooling, 'used_segments_shields'), 'Segments', 0, 0) }}">
                        <dt class="text-xs text-base-content/60">Usage (Shields)</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($cooling, 'usage_shields_pct') * 100, '%', 1) }}</dd>
                    </div>
                    <div class="space-y-1" title="{{ fmt_value_with_unit(data_get($cooling, 'used_segments_quantum'), 'Segments', 0, 0) }}">
                        <dt class="text-xs text-base-content/60">Usage (Quantum)</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($cooling, 'usage_quantum_pct') * 100, '%', 1) }}</dd>
                    </div>
                </dl>

                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60 mt-3">Power</h3>
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Generation</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($power, 'generation_segments'), 'Segments', 0) }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Used Segments</dt>
                        <dd class="text-sm font-medium">
                            @if (data_get($power, 'used_segments_shields') !== null)
                                {{ number_format(data_get($power, 'used_segments_shields'), 0) }} (S)
                            @endif
                            @if (data_get($power, 'used_segments_quantum') !== null)
                                {{ number_format(data_get($power, 'used_segments_quantum'), 0) }} (Q)
                            @endif
                        </dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">EM Per Segment</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($signature, 'em_per_segment'), 'EM', 0) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex flex-col gap-3">
                <details id="signature-details" class="collapse collapse-arrow border border-base-300 bg-base-100" aria-expanded="false" aria-controls="signature-details-content">
                    <summary class="collapse-title min-h-11 py-3 text-xs font-semibold">EM Groups</summary>
                    <div id="signature-details-content" class="collapse-content">
                        <div class="overflow-x-auto">
                            <table class="table table-compact table-zebra table-xs w-full">
                                <thead>
                                <tr>
                                    <th>System (Shields active)</th>
                                    <th>EM Emission</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach (data_get($signature, 'em_groups_shields') ?? [] as $system => $group)
                                    <tr>
                                        <td>{{ Str::headline($system) }}</td>
                                        <td>{{ fmt_value_with_unit($group, 'EM', 0) }}</td>
                                    </tr>
                                @endforeach
                                <thead>
                                <tr>
                                    <th>System (QD active)</th>
                                    <th>EM Emission</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach (data_get($signature, 'em_groups_quantum') ?? [] as $system => $group)
                                    <tr>
                                        <td>{{ Str::headline($system) }}</td>
                                        <td>{{ fmt_value_with_unit($group, 'EM', 0) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>

            </div>

            <div class="flex flex-col gap-3">
                <!-- Cooling Details Collapsible -->
                @if (!empty(data_get($cooling, 'used_segments_shields_grouped')) || !empty(data_get($cooling, 'used_segments_quantum_grouped')))
                    <details id="cooling-details" class="collapse collapse-arrow border border-base-300 bg-base-100" aria-expanded="false" aria-controls="cooling-details-content">
                        <summary class="collapse-title min-h-11 py-3 text-xs font-semibold">Cooling usage Groups</summary>
                        <div id="cooling-details-content" class="collapse-content">
                            <div class="overflow-x-auto">
                                <table class="table table-compact table-xs table-zebra w-full mb-4">
                                    <thead>
                                    <tr>
                                        <th>System (Shields active)</th>
                                        <th>Segments</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach (data_get($cooling, 'used_segments_shields_grouped') ?? [] as $system => $group)
                                        <tr>
                                            <td>{{ Str::headline($system) }}</td>
                                            <td>{{ fmt_or_dash($group) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>

                                    <thead>
                                    <tr>
                                        <th>System (QD active)</th>
                                        <th>Segments</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach (data_get($cooling, 'used_segments_quantum_grouped') ?? [] as $system => $group)
                                        <tr>
                                            <td>{{ Str::headline($system) }}</td>
                                            <td>{{ fmt_or_dash($group) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                @endif


                <!-- Power Details Collapsible -->
                @if (!empty(data_get($power, 'used_segments_grouped')))
                    <details id="power-details" class="collapse collapse-arrow border border-base-300 bg-base-100" aria-expanded="false" aria-controls="power-details-content">
                        <summary class="collapse-title min-h-11 py-3 text-xs font-semibold">Power usage Groups</summary>
                        <div id="power-details-content" class="collapse-content">
                            <div class="overflow-x-auto">
                                <table class="table table-compact table-xs table-zebra w-full">
                                    <thead>
                                    <tr>
                                        <th>System</th>
                                        <th>Segments</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach (data_get($power, 'used_segments_grouped') ?? [] as $system => $group)
                                        <tr>
                                            <td>{{ Str::headline($system) ?? $system }}</td>
                                            <td>{{ fmt_or_dash($group) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                @endif
            </div>
        </div>
    </div>
</details>
