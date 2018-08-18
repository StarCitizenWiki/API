<ul class="nav {{ $class or '' }}" {{ $options or '' }}>
    <li class="nav-item">
        @unless(empty($title))
            <span class="nav-link {{ $titleClass or '' }}">{{ $title or '' }}</span>
        @endunless

        <ul class="list-unstyled {{ $id or '' }} {{ $contentClass or '' }}{{--
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