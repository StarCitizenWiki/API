@component('components.elements.element', ['type' => 'section'])
    @slot('class')
        nav flex-column {{ $class or '' }}
    @endslot
    @slot('options')
        {{ $options or '' }}
    @endslot

    @component('components.elements.element', ['type' => 'li', 'class' => 'nav-item'])
        @if(isset($isLink) && !empty($isLink))
            @component('components.elements.element', ['type' => 'a'])
                @slot('class')
                    nav-link {{ $titleClass or '' }}
                @endslot
                @slot('options')
                    data-toggle="collapse" data-target=".{{ $id or '' }}" aria-expanded="false" aria-controls="{{ $id or '' }}"
                @endslot

                {{ $title or '' }}
                @component('components.elements.icon', ['class' => 'ml-2'])
                    @if (isset($show) && $show == 1)
                        caret-down
                    @else
                        caret-right
                    @endif
                @endcomponent
            @endcomponent
        @else
            @unless(empty($title))
                @component('components.elements.element', ['type' => 'span'])
                    @slot('class')
                        nav-link {{ $titleClass or '' }}
                    @endslot

                    {{ $title or '' }}
                @endcomponent
            @endunless
        @endif

        @component('components.elements.element', ['type' => 'ul'])
            @slot('class')
                flex-column list-unstyled {{ $id or '' }} {{ $contentClass or '' }}{{--
            --}}@if(isset($isLink) && !empty($isLink))
                    @if (isset($show) && $show == 1)
{{--                --}}show
                    @else
{{--                --}}collapse
                    @endif
                @endif
            @endslot
            {{ $slot }}
        @endcomponent
    @endcomponent
@endcomponent