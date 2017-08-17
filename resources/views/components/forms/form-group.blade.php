@component('components.elements.div')
    @slot('class')
        form-group {{ $class or '' }}
    @endslot

    @component('components.elements.element', ['type' => 'label'])
        @slot('class')
            {{ $labelClass or '' }}
        @endslot
        @slot('options')
            for="{{ $id }}" aria-label="{{ $id }}" {{ $labelOptions or '' }}
        @endslot

        {{ $slot or '' }}
    @endcomponent

    @component('components.elements.input')
        @slot('id')
            {{ $id or '' }}
        @endslot
        @slot('class')
            {{ $inputClass or 'form-control' }}
        @endslot
        @slot('type')
            {{ $inputType or 'text' }}
        @endslot
        @slot('name')
            {{ $name or $id }}
        @endslot
        @slot('for')
            {{ $for or $id }}
        @endslot
        @slot('tabIndex')
            {{ $tabIndex or 0 }}
        @endslot
        @slot('value')
            {{ $value or '' }}
        @endslot
        @slot('options')
            {{ $inputOptions or '' }}
        @endslot
        @slot('required')
            {{ $required or '' }}
        @endslot
        @slot('autofocus')
            {{ $autofocus or '' }}
        @endslot
    @endcomponent
@endcomponent