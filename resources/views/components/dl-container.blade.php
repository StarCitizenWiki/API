@props([
    'head' => null,
])

@if($head)
    <dl class="grid gap-x-8 grid-cols-1 sm:grid-cols-2">
        {{ $head }}
    </dl>
@endif

<div {{ $attributes->merge(['class' => "grid gap-x-8 gap-y-2 xl:grid-cols-2"]) }}>
    {{ $slot }}
</div>
