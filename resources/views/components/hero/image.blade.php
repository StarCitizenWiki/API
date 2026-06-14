@props([
    'images' => [],
    'alt' => '',
    'portrait' => false,
    'loading' => 'eager',
    'fetchpriority' => 'high',
    'minWidth' => 'min-w-48',
])

@php
    $image = is_array($images) && isset($images[0]) && is_array($images[0]) ? $images[0] : [];

    $thumbnailUrl = data_get($image, 'thumbnail_url');
    $originalUrl = data_get($image, 'original_url');
    $src = $thumbnailUrl ?? $originalUrl;
    $source = data_get($image, 'source');

    $width = data_get($image, 'thumbnail_width') ?? data_get($image, 'original_width');
    $height = data_get($image, 'thumbnail_height') ?? data_get($image, 'original_height');

    $srcset = null;
    if (is_string($thumbnailUrl) && is_string($originalUrl) && $thumbnailUrl !== $originalUrl) {
        $thumbWidth = (int) (data_get($image, 'thumbnail_width') ?? 0);
        $originalWidth = (int) (data_get($image, 'original_width') ?? 0);

        if ($thumbWidth > 0 && $originalWidth > $thumbWidth) {
            $srcset = "{$thumbnailUrl} {$thumbWidth}w, {$originalUrl} {$originalWidth}w";
        }
    }

    $objectClass = $portrait ? 'object-contain' : 'object-cover';
@endphp

@if ($src)
    <figure class="relative isolate {{ $minWidth }}">
        <a href="{{ $originalUrl ?? $src }}" target="_blank" rel="noopener noreferrer" class="block size-full">
            <img
                src="{{ $src }}"
                @if (is_numeric($width)) width="{{ (int) $width }}" @endif
                @if (is_numeric($height)) height="{{ (int) $height }}" @endif
                @if (is_string($srcset)) srcset="{{ $srcset }}" sizes="(min-width: 640px) 24rem, 100vw" @endif
                alt="{{ $alt }}"
                class="size-full {{ $objectClass }} max-h-96"
                loading="{{ $loading }}"
                fetchpriority="{{ $fetchpriority }}"
            />
        </a>
        @if (is_string($source) && $source !== '')
            <span class="pointer-events-none absolute bottom-2 right-2 z-10 w-auto h-auto rounded bg-black/30 px-2 py-0.5 text-xs text-white/70">
                Image from {{ $source }}
            </span>
        @endif
    </figure>
@endif
