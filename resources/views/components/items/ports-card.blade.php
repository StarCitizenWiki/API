@props([
    'ports',
])

@php
    $portsCount = is_array($ports) ? count($ports) : 0;
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow', 'data-testid' => 'item-ports-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex items-center gap-2">
            <h2 class="card-title text-base">Ports</h2>
            @if ($portsCount > 0)
                <span class="badge badge-ghost text-xs" data-testid="item-ports-count">{{ $portsCount }}</span>
            @endif
        </div>

        @if (is_array($ports) && $ports !== [])
            <div class="grid grid-cols-1 gap-3 sm:gap-4 lg:grid-cols-2">
                @foreach ($ports as $port)
                    <x-item-port-display :port="$port" :depth="0" />
                @endforeach
            </div>
        @else
            <div class="text-sm text-subtle">No ports available.</div>
        @endif
    </div>
</section>
