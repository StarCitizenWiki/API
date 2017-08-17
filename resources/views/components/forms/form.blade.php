@component('components.elements.element', ['type' => 'form'])
    @slot('id')
        {{ $id or '' }}
    @endslot
    @slot('class')
        {{ $class or '' }}
    @endslot
    @slot('options')
        method="{{ $method or '' }}" action="{{ $action or '' }}"
    @endslot

    {{ csrf_field() }}
    {{ $slot }}
@endcomponent