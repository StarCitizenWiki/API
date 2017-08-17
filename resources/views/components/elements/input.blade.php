@component('components.elements.element', ['type' => 'input'])
    @slot('id')
        {{ $id or '' }}
    @endslot
    @slot('class')
        {{ $labelClass or 'form-control' }}
    @endslot
    @slot('options')
        type="{{ $type or 'text' }}"
        name="{{ $name or $id }}"
        for="{{ $for or $id }}"
        aria-label="{{ $id or '' }}"
        tabindex="{{ $tabIndex or 0 }}"
        value="{{ $value or '' }}"
        {{ $inputOptions or '' }}
        @if(isset($required) && $required == '1')
{{--    --}}required
        @endif
        @if(isset($autofocus) && $autofocus == '1')
{{--    --}}autofocus
        @endif
    @endslot
@endcomponent