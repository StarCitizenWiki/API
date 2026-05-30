@props([
    'metric',
    'name',
    'kindLabel',
    'hasLink',
    'webUrl',
    'isCommodity',
    'containerSize',
    'items',
])

@if ($hasLink)
    <a href="{{ $webUrl }}" class="group block rounded-box border border-base-300 bg-base-100 p-3 transition hover:border-base-content/20 hover:bg-base-200/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-base-content/20">
@else
    <div class="group block rounded-box border border-base-300 bg-base-100 p-3">
@endif
    <div class="min-w-0 space-y-1.5">
        <div class="flex items-center gap-1.5 truncate text-sm font-semibold text-base-content @if($hasLink) transition group-hover:text-base-content/80 @endif">
            <span class="truncate">{{ $name }}</span>
            @if ($isCommodity)
                <span class="text-subtle font-normal">(Commodity)</span>
            @elseif ($kindLabel !== 'Commodity')
                <span class="text-subtle font-normal">({{ $kindLabel }})</span>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-xs text-subtle">
            @if ($metric !== null)
                <span class="badge {{ $metric['style'] }} badge-sm font-semibold">{{ $metric['value'] }}</span>
            @endif

            @if ($containerSize > 0)
                <span class="badge badge-ghost badge-sm">{{ $containerSize }} SCU container</span>
            @endif
        </div>

        @if ($items !== [])
            <div class="flex flex-wrap gap-1.5 pt-1">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'web_url');
                        $itemName = data_get($item, 'name', 'Item');
                    @endphp
                    @if ($itemUrl)
                        @if ($hasLink)<span class="inline-block" onclick="event.stopPropagation()" role="presentation">@endif
                        <a href="{{ $itemUrl }}" class="badge badge-outline badge-sm link link-hover link-primary"@if($hasLink) tabindex="-1"@endif>{{ $itemName }}</a>
                        @if ($hasLink)</span>@endif
                    @else
                        <span class="badge badge-outline badge-sm">{{ $itemName }}</span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@if ($hasLink)
    </a>
@else
    </div>
@endif
