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
            {{ $slot or '' }}
        @endcomponent
    @else
        @component('components.elements.element', ['type' => 'a'])
            @slot('class')
                nav-link @if(Route::currentRouteName() == $route) active @endif {{ $contentClass or '' }}
            @endslot
            @slot('options')
                href="@if($route === '#' || str_contains($route, '//')) {{ $route }} @else {{ route($route) }} @endif" {{ $options or '' }}
            @endslot
            {{ $slot or '' }}
        @endcomponent
    @endif
    {{ $body or '' }}
@endcomponent