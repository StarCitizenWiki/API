@props([
    'head' => null,
    'headCols' => null
])

@if($head?->hasActualContent())
    <dl @class([
        "grid gap-x-8 grid-cols-1",
        "grid-cols-{$headCols}" => $headCols,
        "sm:grid-cols-2" => !$headCols
    ])>
        {{ $head }}
    </dl>
@endif

@if($slot->hasActualContent())
<div {{ $attributes->merge(['class' => "grid gap-x-8 gap-y-2 xl:grid-cols-2"]) }}>
    {{ $slot }}
</div>
@endif
