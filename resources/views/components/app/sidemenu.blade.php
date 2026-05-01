@props([
    'label' => null,
])

<ul {{ $attributes->class(['menu', 'w-full', 'gap-1']) }}>
    @if ($label)
        <li class="menu-title">
            <span class="text-xs font-semibold uppercase tracking-wide text-subtle">{{ $label }}</span>
        </li>
    @endif

    {{ $slot }}
</ul>
