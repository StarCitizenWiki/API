@component('components.elements.element', ['type' => 'i'])
    @slot('id')
        {{ $id or '' }}
    @endslot
    @slot('class')
        fa fa-{{ $slot }} {{ $class or '' }}
    @endslot
    @slot('options')
        {{ $options or '' }}
    @endslot
    {{ $content or '' }}
@endcomponent