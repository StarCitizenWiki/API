@props(['tier'])

@php
    $tierClasses = match ($tier) {
        'common' => 'badge-ghost',
        'uncommon' => 'badge-success',
        'rare' => 'badge-info',
        'epic' => 'badge-secondary',
        'legendary' => 'badge-error',
        default => 'badge-outline',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge badge-sm {$tierClasses}"]) }}>{{ ucfirst($tier) }}</span>
