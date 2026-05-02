@props([
    'title',
    'open' => false,
    'testId' => null,
    'group' => null,
])

<details
    class="col-span-full group{{ $group ? "/" . $group : "" }}"
    @if($testId) data-testid="{{ $testId }}" @endif
    @if($open) open @endif
    {{ $attributes->except(['class', 'open', 'testId', 'group', 'title']) }}
>
    <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
        <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open{{ $group ? '/' . $group : '' }}:rotate-90" />
        {{ $title }}
    </summary>
    <x-dl-container>
        {{ $slot }}
    </x-dl-container>
</details>
