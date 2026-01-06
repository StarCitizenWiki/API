@props([
    'title',
])

<li class="menu-title">
    <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">
        @isset($icon)
            <span class="text-base-content/70">
                {{ $icon }}
            </span>
        @endisset
        {{ $title }}
    </span>
</li>

{{ $slot }}
