@props([
    'image',
    'showFooter' => true,
])

@php
    $imageId = data_get($image, 'id');
    $name = data_get($image, 'name');
    $alt = data_get($image, 'alt');
    $rsiUrl = data_get($image, 'rsi_url');
    $mimeType = data_get($image, 'mime_type');
    $size = data_get($image, 'size');
    $lastModified = data_get($image, 'last_modified');
    $tags = data_get($image, 'tags', []);
    $commLinks = data_get($image, 'comm_links', []);
    $showFooter = filter_var($showFooter, FILTER_VALIDATE_BOOLEAN);

    $isVideo = is_string($mimeType) && str_contains($mimeType, 'video');
    $isImage = is_string($mimeType) && str_contains($mimeType, 'image');
    $isAudio = is_string($mimeType) && str_contains($mimeType, 'audio');
    $sizeKb = $size !== null ? number_format(((float) $size) / 1024, 1) : null;
    $previewTag = is_array($tags) ? (collect($tags)->first() ?? null) : null;
    $commLinks = is_array($commLinks) ? $commLinks : [];
    $commLinksPreview = collect($commLinks)->sortByDesc('id')->take(3);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="relative">
        @if ($previewTag)
            <span class="badge badge-neutral absolute left-3 top-3 z-10">
                {{ $previewTag['translated_name'] ?? $previewTag['name'] ?? 'Tag' }}
            </span>
        @endif
        @if ($mimeType)
            <span class="badge badge-info absolute right-3 top-3 z-10">
                {{ $mimeType }}
            </span>
        @endif

        @if ($isVideo)
            <video class="h-48 w-full rounded-t-box object-cover" controls>
                <source src="{{ $rsiUrl }}" type="{{ $mimeType }}">
            </video>
        @elseif ($isImage)
            <img
                src="{{ $rsiUrl }}"
                alt="{{ $alt ?? 'Comm-Link image' }}"
                class="h-48 w-full rounded-t-box object-cover"
                loading="lazy"
            >
        @elseif ($isAudio)
            <audio class="w-full px-4 py-4" controls>
                <source src="{{ $rsiUrl }}" type="{{ $mimeType }}">
            </audio>
        @else
            <div class="flex h-48 items-center justify-center text-sm text-base-content/70">
                {{ $name ?? 'File' }}
            </div>
        @endif
    </div>

    <div class="card-body gap-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-sm font-semibold">{{ $name ?? 'Image' }}</div>
                @if ($alt)
                    <div class="text-xs text-base-content/60">{{ $alt }}</div>
                @endif
            </div>
            @if ($imageId)
                <a class="link link-primary text-xs" href="{{ route('web.comm-links.images.show', $imageId) }}">Details</a>
            @endif
        </div>

        <dl class="grid gap-2 text-xs text-base-content/70">
            <div class="flex items-center justify-between">
                <dt>Last Modified</dt>
                <dd>{{ $lastModified ?? '-' }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt>Size</dt>
                <dd>{{ $sizeKb !== null ? "{$sizeKb} KB" : '-' }}</dd>
            </div>
        </dl>

        <div class="flex flex-wrap gap-2">
            @if ($rsiUrl)
                <a class="btn btn-outline btn-xs" href="{{ $rsiUrl }}" target="_blank" rel="noreferrer">Source</a>
            @endif
            @if ($imageId)
                <a class="btn btn-outline btn-xs" href="{{ route('web.comm-links.images.show', $imageId) }}">Info</a>
            @endif
        </div>

        @if ($commLinksPreview->isNotEmpty())
            <div class="text-xs text-base-content/70">Used in Comm-Links</div>
            <div class="flex flex-col gap-1 text-xs">
                @foreach ($commLinksPreview as $commLink)
                    <a class="link link-primary" href="{{ $commLink['web_url'] ?? route('web.comm-links.show', $commLink['id'] ?? 0) }}">
                        {{ $commLink['id'] ?? '-' }} - {{ $commLink['title'] ?? 'Comm-Link' }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($showFooter)
            @if (is_array($tags) && $tags !== [])
                <div class="flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <span class="badge badge-neutral">
                            {{ $tag['translated_name'] ?? $tag['name'] ?? 'Tag' }}
                        </span>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
