@props([
    'title' => null,
])

@if ($title)
    <li class="menu-title pointer-events-none select-none mt-2">
        <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-subtle">
            @isset($icon)
                <span class="text-subtle">
                    {{ $icon }}
                </span>
            @endisset
            {{ $title }}
        </span>
    </li>
@endif

{{ $slot }}
