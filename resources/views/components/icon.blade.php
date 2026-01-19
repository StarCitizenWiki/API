@props([
    'name',
])

<i data-lucide="{{ $name }}" {{ $attributes->class(['size-4', 'shrink-0']) }}></i>
