@component('components.elements.element', ['type' => 'a'])
    @slot('id')
        {{ $id or '' }}
    @endslot
    @slot('class')
        {{ $class or '' }}
    @endslot
    @slot('options')
        href="{{ $href or '' }}" {{ $options or '' }}
    @endslot
    {{ $slot or '' }}
@endcomponent