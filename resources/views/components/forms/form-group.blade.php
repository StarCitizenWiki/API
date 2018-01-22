<div class="form-group {{ $class or '' }}">
    @if(isset($label))
    <label @if(isset($labelClass)) class="{{ $labelClass or '' }}" @endif
           for="{{ $id }}"
           aria-label="{{ $id }}"
           {{ $labelOptions or '' }}>
        {{ $label }}:
    </label>
    @endif

    @if(isset($inputType) && $inputType === 'textarea')
        <textarea type="{{ $inputType or 'text' }}"
               name="{{ $name or $id }}"
               aria-label="{{ $id }}"
               @if(isset($tabIndex)) tabindex="{{ $tabIndex or 0 }}" @endif
               @if(isset($cols)) cols="{{ $cols or 0 }}" @endif
               rows="{{ $rows or 3 }}"
               id="{{ $id }}"
               class="{{ $inputClass or 'form-control' }} {{ $errors->has($id) ? 'is-invalid' : '' }}"
               @if(isset($required) && $required == '1') required @endif
               @if(isset($autofocus) && $autofocus == '1') autofocus @endif
                {{ $inputOptions or '' }}>{{ $value or old($id) }}</textarea>
    @elseif(isset($inputType) && $inputType === 'select')
        <select type="{{ $inputType or 'text' }}"
                  name="{{ $name or $id }}"
                  aria-label="{{ $id }}"
                  @if(isset($tabIndex)) tabindex="{{ $tabIndex or 0 }}" @endif
                  id="{{ $id }}"
                  class="{{ $inputClass or 'form-control' }} {{ $errors->has($id) ? 'is-invalid' : '' }}"
                  @if(isset($required) && $required == '1') required @endif
                  @if(isset($autofocus) && $autofocus == '1') autofocus @endif
                {{ $inputOptions or '' }}>
            {{ $selectOptions or '' }}
        </select>
    @else
        <input type="{{ $inputType or 'text' }}"
               name="{{ $name or $id }}"
               aria-label="{{ $id }}"
               @if(isset($tabIndex)) tabindex="{{ $tabIndex or 0 }}" @endif
               @if(isset($value)) value="{{ $value or '' }}" @endif
               id="{{ $id }}"
               class="{{ $inputClass or 'form-control' }} {{ $errors->has($id) ? 'is-invalid' : '' }}"
               @if(isset($required) && $required == '1') required @endif
               @if(isset($autofocus) && $autofocus == '1') autofocus @endif
                {{ $inputOptions or '' }}>
    @endif
    {{ $slot }}
</div>