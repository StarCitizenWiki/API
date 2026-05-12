@props([
    'category',
    'count',
    'items',
])

@use('Illuminate\Support\Str')

@php
    $isPlaceholder = static fn (?string $name): bool => $name === null || Str::startsWith($name, '<=');
@endphp

<div>
    <span class="text-sm font-semibold uppercase tracking-wide text-subtle">
        {{ $category }}
        <span class="font-normal">({{ $count }})</span>
    </span>

    @if (is_array($items) && $items !== [])
        <div class="pl-4 border-l border-base-300 mt-1 space-y-0.5">
            @foreach ($items as $item)
                @php
                    $itemName = data_get($item, 'item_name');
                    $hardpoint = data_get($item, 'hardpoint');
                    $uuid = data_get($item, 'uuid');
                    $isPlaceholderName = $isPlaceholder($itemName);
                    $canLink = ! $isPlaceholderName && $uuid;
                @endphp

                <div class="text-sm flex items-baseline gap-2">
                    @if ($canLink)
                        <a href="{{ route('web.items.show', $uuid) }}" class="link link-hover link-primary font-medium">
                            {{ $itemName }}
                        </a>
                        <span class="text-xs text-ghost truncate">{{ $hardpoint }}</span>
                    @elseif (! $isPlaceholderName)
                        <span class="font-medium">{{ $itemName }}</span>
                        <span class="text-xs text-ghost truncate">{{ $hardpoint }}</span>
                    @else
                        <span class="text-xs text-subtle truncate">{{ $hardpoint }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
