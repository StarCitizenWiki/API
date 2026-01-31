@props([
    'uuid',
    'classification',
    'className',
    'version',
    'apiLink',
    'entityTagMap',
])

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        Technical
    </summary>
    <div class="collapse-content">
        <dl class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 tabular-nums">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UUID</dt>
                <dd class="text-sm font-medium">{{ $uuid ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Classification</dt>
                <dd class="text-sm font-medium">{{ $classification ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class Name</dt>
                <dd class="text-sm font-medium">{{ $className ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Game Version</dt>
                <dd class="text-sm font-medium">{{ $version ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API Link</dt>
                <dd class="text-sm font-medium break-all">
                    @if ($apiLink)
                        <a href="{{ $apiLink }}" class="link link-primary">{{ $apiLink }}</a>
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="space-y-1 sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Entity Tag Map</dt>
                <dd class="text-sm font-medium">
                    @if (is_array($entityTagMap) && $entityTagMap !== [])
                        <div class="flex flex-wrap gap-2">
                            @foreach ($entityTagMap as $tag)
                                <span class="badge badge-neutral" title="{{ $tag['uuid'] ?? '' }}">
                                    {{ $tag['name'] ?? 'Unknown' }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        -
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</details>
