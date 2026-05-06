@props([
    'title',
    'open' => false,
    'testId' => null,
    'group' => null,
])

@if($slot->hasActualContent())
<details
    class="col-span-full collapse-arrow collapse{{ $group ? "/" . $group : "" }}"
    @if($testId) data-testid="{{ $testId }}" @endif
    @if($open) open @endif
    {{ $attributes->except(['class', 'open', 'testId', 'group', 'title']) }}
>
    <summary class="collapse-title min-h-0 py-2 text-sm font-semibold text-subtle pl-0">
        {{ $title }}
    </summary>
    <div class="collapse-content pl-0">
        <x-dl-container>
            {{ $slot }}
        </x-dl-container>
    </div>
</details>
@endif
