@component('components.elements.element', ['type' => 'nav'])
    @slot('class')
        navbar navbar-expand-lg navbar-dark {{ $class or '' }}
    @endslot

    <?php if (isset($title) && !empty($title)) { ?>
        @component('components.elements.element', ['type' => 'a', 'class' => 'navbar-brand'])
            @slot('options')
                href="{{ $titleLink or '#' }}"
            @endslot
            {{ $title }}
        @endcomponent
    <?php } ?>

    @component('components.elements.element', ['type' => 'button', 'class' => 'navbar-toggler'])
        @slot('options')
            type="button" data-toggle="collapse" data-target="#nav-top-menu" aria-controls="nav-top-menu" aria-expanded="false" aria-label="Toggle navigation"
        @endslot

        @component('components.elements.element', ['type' => 'span', 'class' => 'navbar-toggler-icon'])
        @endcomponent
    @endcomponent

    @component('components.elements.element', ['type' => 'div', 'id' => 'nav-top-menu'])
        @slot('class')
            collapse navbar-collapse justify-content-end {{ $contentClass or '' }}
        @endslot

        @component('components.elements.element', ['type' => 'ul', 'class' => 'navbar-nav'])
            {{ $slot or '' }}
        @endcomponent
    @endcomponent
@endcomponent