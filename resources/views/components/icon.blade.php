@props([
    'name',
    'size' => 'md',
])

@php
$sizes = [
    'xs' => 'size-3',
    'sm' => 'size-3.5',
    'md' => 'size-4',
    'lg' => 'size-5',
    'xl' => 'size-6',
];
@endphp

<i data-lucide="{{ $name }}" {{ $attributes->class([$sizes[$size] ?? 'size-4', 'shrink-0']) }}></i>
