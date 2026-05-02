@props([
    'image',
    'showFooter' => true,
])

@php
    if (!function_exists('formatFileSize')) {
         function formatFileSize(float $bytes): string
        {
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            }
            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            }
            if ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            }
            return $bytes . ' B';
        }
    }

    $imageId = data_get($image, 'id');
    $name = data_get($image, 'name');
    $alt = data_get($image, 'alt');
    $rsiUrl = data_get($image, 'rsi_url');
    $mimeType = data_get($image, 'mime_type');
    $size = data_get($image, 'size');
    $lastModified = data_get($image, 'last_modified');
    $tags = data_get($image, 'tags', []);
    $commLinks = data_get($image, 'comm_links', []);
    $duplicates = data_get($image, 'duplicates', []);
    $baseImage = data_get($image, 'base_image');
    $similarUrl = data_get($image, 'similar_url');
    $showFooter = filter_var($showFooter, FILTER_VALIDATE_BOOLEAN);

    $isVideo = is_string($mimeType) && str_contains($mimeType, 'video');
    $isImage = is_string($mimeType) && str_contains($mimeType, 'image');
    $isAudio = is_string($mimeType) && str_contains($mimeType, 'audio');


    $sizeFormatted = $size !== null ? formatFileSize((float) $size) : null;
    $lastModifiedFormatted = $lastModified ? \Carbon\Carbon::parse($lastModified)->diffForHumans() : null;
    $lastModifiedAbsolute = $lastModified ? \Carbon\Carbon::parse($lastModified)->format('Y-m-d') : null;

    $previewTag = is_array($tags) ? (collect($tags)->first() ?? null) : null;
    $commLinks = is_array($commLinks) ? $commLinks : [];
    $commLinksPreview = collect($commLinks)->sortByDesc('id')->take(3);
    $duplicates = is_array($duplicates) ? $duplicates : [];
    $duplicatesCount = count($duplicates);
    $hasBaseImage = is_array($baseImage) && $baseImage !== null;
@endphp


<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow', 'data-testid' => $imageId ? 'comm-link-image-card-'.$imageId : 'comm-link-image-card']) }}>
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
        @if ($duplicatesCount > 0)
            <span class="badge badge-secondary absolute left-3 bottom-3 z-10 tooltip" data-tip="{{ $duplicatesCount }} duplicate{{ $duplicatesCount > 1 ? 's' : '' }}">
                ×{{ $duplicatesCount }}
            </span>
        @endif
        @if ($hasBaseImage)
            <span class="badge badge-secondary absolute right-3 bottom-3 z-10 tooltip" data-tip="Duplicate of {{ $baseImage['name'] }}">
                Duplicate
            </span>
        @endif

        @if ($isVideo)
            <video class="h-48 w-full rounded-t-box object-cover" controls>
                <source src="{{ $rsiUrl }}" type="{{ $mimeType }}">
            </video>
        @elseif ($isImage)
            <a href="{{ $rsiUrl }}" target="_blank" rel="noreferrer" class="block">
                <img
                    src="{{ $rsiUrl }}"
                    alt="{{ $alt ?? 'Comm-Link image' }}"
                    class="h-48 w-full rounded-t-box object-cover"
                    loading="lazy"
                >
            </a>
        @elseif ($isAudio)
            <audio class="w-full px-4 py-4" controls>
                <source src="{{ $rsiUrl }}" type="{{ $mimeType }}">
            </audio>
        @else
            <div class="flex h-48 items-center justify-center text-sm text-subtle">
                {{ $name ?? 'File' }}
            </div>
        @endif
    </div>

    <div class="card-body gap-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-sm font-semibold">{{ $name ?? 'Image' }}</div>
                @if ($alt)
                    <div class="text-xs text-subtle">{{ $alt }}</div>
                @endif
            </div>
            @if ($imageId)
                <a class="link link-primary text-xs" data-testid="comm-link-image-details-link-{{ $imageId }}" href="{{ route('web.comm-links.images.show', $imageId) }}">Details</a>
            @endif
        </div>

        <x-dl-section dlClass="grid gap-2 text-xs text-subtle">
            <x-dt-dd label="Last Modified">
                <span class="tooltip" data-tip="{{ $lastModifiedAbsolute }}">{{ $lastModifiedFormatted ?? '-' }}</span>
            </x-dt-dd>
            <x-dt-dd label="Size">
                {{ $sizeFormatted ?? '-' }}
            </x-dt-dd>
        </x-dl-section>

        <div class="flex flex-wrap gap-2">
            @if ($rsiUrl)
                <a class="btn btn-outline btn-xs" data-testid="comm-link-image-source-link-{{ $imageId }}" href="{{ $rsiUrl }}" target="_blank" rel="noreferrer">Source</a>
            @endif
            @if ($imageId)
                <a class="btn btn-outline btn-xs" data-testid="comm-link-image-info-link-{{ $imageId }}" href="{{ route('web.comm-links.images.show', $imageId) }}">Info</a>
            @endif
            @auth
                @if ($isImage)
                    <a class="btn btn-outline btn-xs" href="{{ route('web.comm-links.images.similar', $imageId) }}" target="_blank">Find Similar</a>
                @endif
            @endauth
        </div>

        @if (count($commLinks) > 3)
            <div class="flex items-center gap-2">
                <div class="text-xs text-subtle">Used in Comm-Links</div>
                <span class="badge badge-neutral badge-xs">{{ count($commLinks) }}</span>
            </div>
            <div class="collapse collapse-arrow border border-base-300 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-xs font-semibold">
                    Show all {{ count($commLinks) }} Comm-Links
                </div>
                <div class="collapse-content">
                    <div class="flex flex-col gap-1 text-xs">
                        @foreach ($commLinks as $commLink)
                            <a class="link link-primary" href="{{ $commLink['web_url'] ?? route('web.comm-links.show', $commLink['id'] ?? 0) }}">
                                {{ $commLink['id'] ?? '-' }} - {{ $commLink['title'] ?? 'Comm-Link' }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @elseif ($commLinksPreview->isNotEmpty())
            <div class="text-xs text-subtle">Used in Comm-Links</div>
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
