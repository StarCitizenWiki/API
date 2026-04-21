@props(['vehicle'])

@php
    $uuid = data_get($vehicle, 'uuid');
    $classification = data_get($vehicle, 'classification');
    $className = data_get($vehicle, 'class_name');
    $version = data_get($vehicle, 'version');
    $apiLink = data_get($vehicle, 'link');
    $webUrl = data_get($vehicle, 'web_url');
    $rawVehicleJson = json_encode($vehicle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <!-- Technical Details -->
    @if ($uuid || $classification || $className || $version || $apiLink || $webUrl)
        <details id="technical-details" class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
            <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="technical-details-content">
                Technical
            </summary>
            <div id="technical-details-content" class="collapse-content">
                <dl class="grid gap-4 grid-cols-1 md:grid-cols-2">
                    @if ($uuid)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UUID</dt>
                            <dd class="text-right text-sm font-medium">{{ $uuid }}</dd>
                        </div>
                    @endif
                    @if ($classification)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Classification</dt>
                            <dd class="text-right text-sm font-medium">{{ $classification }}</dd>
                        </div>
                    @endif
                    @if ($className)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class Name</dt>
                            <dd class="text-right text-sm font-medium">{{ $className }}</dd>
                        </div>
                    @endif
                    @if ($version)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Game Version</dt>
                            <dd class="text-right text-sm font-medium">{{ $version }}</dd>
                        </div>
                    @endif
                    @if ($apiLink)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API Link</dt>
                            <dd class="text-right text-sm font-medium">
                                <a href="{{ $apiLink }}" class="link link-primary">{{ $apiLink }}</a>
                            </dd>
                        </div>
                    @endif
                    @if ($webUrl)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Web URL</dt>
                            <dd class="text-right text-sm font-medium">
                                <a href="{{ $webUrl }}" class="link link-primary">{{ $webUrl }}</a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </details>
    @endif

    <!-- Raw Payload -->
    <details id="raw-payload-details" class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
        <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="raw-payload-details-content">
            Raw Payload
        </summary>
        <div id="raw-payload-details-content" class="collapse-content">
            <pre class="text-xs whitespace-pre-wrap">{{ $rawVehicleJson }}</pre>
        </div>
    </details>
</div>
