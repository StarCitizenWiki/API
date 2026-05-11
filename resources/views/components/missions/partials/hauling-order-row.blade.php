@props([
    'metric',
    'name',
    'kindLabel',
    'hasLink',
    'webUrl',
    'isCommodity',
    'items',
])

<div class="list-row">
    @if ($metric !== null)
        <div>
            <span class="badge {{ $metric['style'] }} badge-sm font-semibold">{{ $metric['value'] }}</span>
        </div>
    @endif

    <div class="list-col-grow">
        <div class="text-sm font-medium" title="{{ $kindLabel }}">
            @if ($hasLink)
            <a href="{{ $webUrl }}" class="link link-hover link-primary">{{ $name }}</a>
            @else
                {{ $name }}
            @endif
            @if ($isCommodity)
                <span class="text-subtle font-normal">(Commodity)</span>
            @endif
        </div>

        @if ($items !== [])
            <div class="list-col-wrap">
                <div class="flex flex-wrap gap-1.5 mt-1">
                    @foreach ($items as $item)
                        @if (data_get($item, 'web_url'))
                            <a href="{{ data_get($item, 'web_url') }}" class="badge badge-outline badge-sm link link-hover link-primary">{{ data_get($item, 'name', 'Item') }}</a>
                        @else
                            <span class="badge badge-outline badge-sm">{{ data_get($item, 'name', 'Item') }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
