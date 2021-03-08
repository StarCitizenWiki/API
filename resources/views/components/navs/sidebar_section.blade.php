<ul class="nav {{ $class ?? '' }}" {{ $options ?? '' }} style="flex-basis: 25%;">
    <li class="nav-item">
        @unless(empty($title))
            <span style="font-size: 1.15rem" class="nav-link {{ $titleClass ?? '' }}">{{ $title ?? '' }}</span>
        @endunless

        <ul class="list-unstyled {{ $id ?? '' }} {{ $contentClass ?? '' }}{{--
        --}}@if(isset($isLink) && !empty($isLink))
            @if (isset($show) && $show == 1)
{{--        --}}show
            @else
{{--        --}}collapse
            @endif
        @endif">
            {{ $slot }}
        </ul>
    </li>
</ul>