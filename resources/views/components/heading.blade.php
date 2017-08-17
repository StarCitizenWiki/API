@component('components.elements.div')
    @slot('class')
        {{ $class or '' }}
    @endslot

    @unless(isset($hideImage))
        @component('components.elements.element', ['type' => 'a'])
            @slot('class')
                {{ $linkClass or '' }}
            @endslot
            @slot('options')
                href="@if(empty($route) || $route === '#' || str_contains($route, '/')) {{ $route or '' }} @else {{ route($route) }} @endif"
            @endslot

            @component('components.elements.img')
                @slot('class')
                    center-block {{ $imageClass or '' }}
                @endslot

                {{ asset('media/images/Star_Citizen_Wiki_Logo.png') }}
            @endcomponent
        @endcomponent
    @endunless

    @unless(strlen($slot) === 0)
        @component('components.elements.element', ['type' => 'h1'])
            {{ $slot }}
        @endcomponent
    @endunless

    @unless(empty($subTitle))
        @component('components.elements.element', ['type' => 'p', 'class' => 'lead'])
            {{ $subTitle or '' }}
        @endcomponent
    @endunless
@endcomponent