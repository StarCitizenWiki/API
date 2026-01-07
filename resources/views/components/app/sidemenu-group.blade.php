@props([
    'title' => null,
])

@if ($title)
    <li class="menu-title pointer-events-none select-none mt-2">
        <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">
            @isset($icon)
                <span class="text-base-content/70">
                    {{ $icon }}
                </span>
            @endisset
            {{ $title }}
        </span>
    </li>
@endif

{{ $slot }}
