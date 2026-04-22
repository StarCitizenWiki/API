@props([
    'type' => null,
    'category' => null,
    'subType' => null,
    'name' => null,
    'sizeMin' => null,
    'sizeMax' => null,
    'label' => null,
])

@php
    $filters = array_filter([
        'type' => $type,
        'category' => $category,
        'sub_type' => $subType,
        'name' => $name,
    ]);

    if ($sizeMin !== null && $sizeMax !== null) {
        $filters['size'] = implode(',', range($sizeMin, $sizeMax));
    }

    if (empty($filters)) {
        return;
    }
@endphp

<a href="{{ route('web.items.index', ['filter' => $filters]) }}" class="badge badge-sm badge-ghost no-underline hover:badge-primary" title="Browse matching items">
    @if ($label)
        <span>{{ $label }}</span>
    @endif
    <x-icon name="external-link" class="size-3 opacity-60"/>
</a>
