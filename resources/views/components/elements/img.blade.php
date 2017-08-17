@component('components.elements.element', ['type' => 'img'])
    @slot('id')
        {{ $id or '' }}
    @endslot
    @slot('class')
        {{ $class or '' }}
    @endslot
    @slot('options')
        src="{{ $slot }}" {{ $options or '' }}
    @endslot
@endcomponent