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

    $versionQuery = request()->query('version');
    $url = route('web.items.index', ['filter' => $filters]);

    if (is_string($versionQuery) && $versionQuery !== '') {
        $url = url()->query($url, ['version' => $versionQuery]);
    }
@endphp

<a href="{{ $url }}" class="badge badge-sm badge-soft no-underline hover:opacity-80" title="Browse matching items">
    @if ($label)
        <span>{{ $label }}</span>
    @endif
    <x-icon name="external-link" class="size-3 opacity-60"/>
</a>
