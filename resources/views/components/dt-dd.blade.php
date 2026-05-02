@props([
    'label',
    'stacked' => false,
    'ddClass' => null,
])

<div @class([
    'flex items-baseline justify-between gap-3' => ! $stacked,
    'space-y-1' => $stacked,
])>
    <dt class="text-xs font-light uppercase tracking-wide text-subtle">{{ $label }}</dt>
    <dd @class([
        'text-sm font-semibold text-base-content',
        'text-right' => ! $stacked,
        $ddClass => $ddClass,
    ])>
        {{ $slot }}
    </dd>
</div>
