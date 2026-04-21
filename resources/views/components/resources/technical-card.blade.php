@props(['resource'])

@php
    $uuid = data_get($resource, 'uuid');
    $apiLink = data_get($resource, 'link');
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow', 'data-testid' => 'resource-technical-card']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        Technical
    </summary>
    <div class="collapse-content">
        <dl class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 tabular-nums">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UUID</dt>
                <dd class="text-right text-sm font-medium break-all">{{ $uuid ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API Link</dt>
                <dd class="text-right text-sm font-medium break-all">
                    @if ($apiLink)
                        <a href="{{ $apiLink }}" class="link link-primary">{{ $apiLink }}</a>
                    @else
                        -
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</details>
