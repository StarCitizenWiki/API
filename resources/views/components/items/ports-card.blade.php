@props([
    'ports',
])

@php
    $portsCount = is_array($ports) ? count($ports) : 0;
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow', 'data-testid' => 'item-ports-card']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        <span class="flex items-center gap-2">
            <span>Ports</span>
            @if ($portsCount > 0)
                <span class="badge badge-ghost text-xs" data-testid="item-ports-count">{{ $portsCount }}</span>
            @endif
        </span>
    </summary>
    <div class="collapse-content">
        @if (is_array($ports) && $ports !== [])
            <div class="grid gap-3 sm:gap-4 grid-cols-1 lg:grid-cols-2">
                @foreach ($ports as $port)
                    <x-item-port-display :port="$port" :depth="0"/>
                @endforeach
            </div>
        @else
            <div class="text-sm text-base-content/70">No ports available.</div>
        @endif
    </div>
</details>
