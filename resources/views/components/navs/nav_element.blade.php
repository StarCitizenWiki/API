@component('components.elements.element', ['type' => 'li'])
    @slot('class')
        nav-item {{ $class or '' }}
    @endslot

    @if(empty($route) || '-' === $route)
        @component('components.elements.element', ['type' => 'span'])
            @slot('class')
                nav-link {{ $contentClass or '' }}
            @endslot
            @slot('options')
                {{ $options or '' }}
            @endslot
            {{ $slot }}
        @endcomponent
    @else
        @component('components.elements.element', ['type' => 'a'])
            @slot('class')
                nav-link @if(Request::fullUrl() == $route) active @endif {{ $contentClass or '' }}
            @endslot
            @slot('options')
                href="{{ $route }}" {{ $options or '' }}
            @endslot
            {{ $slot }}
        @endcomponent
    @endif
    {{ $body or '' }}
@endcomponent