<ul class="nav {{ $class or '' }}" {{ $options or '' }}>
    <li class="nav-item">
        @if(isset($isLink) && !empty($isLink))
        <a class="nav-link @unless(isset($show)) collapsed @endunless {{ $titleClass or '' }}"
           data-toggle="collapse"
           data-target=".{{ $id or '' }}"
           aria-expanded="false"
           aria-controls="{{ $id or '' }}">
            {{ $title or '' }}
            @component('components.elements.icon', ['class' => 'ml-2'])
                @if (isset($show) && $show == 1)
                    caret-down
                @else
                    caret-right
                @endif
            @endcomponent
        </a>
        @else
            @unless(empty($title))
                <span class="nav-link {{ $titleClass or '' }}">{{ $title or '' }}</span>
            @endunless
        @endif

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