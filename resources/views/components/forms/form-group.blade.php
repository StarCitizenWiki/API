<div class="form-group {{ $class ?? '' }}">
    @if(isset($label))
    <label @if(isset($labelClass)) class="{{ $labelClass ?? '' }}" @endif
           for="{{ $id }}"
           aria-label="{{ $id }}"
           {{ $labelOptions ?? '' }}>
        {{ $label }}:
    </label>
    @endif

    @if(isset($inputType) && $inputType === 'textarea')
        <textarea type="{{ $inputType ?? 'text' }}"
                  name="{{ $name ?? $id }}"
                  aria-label="{{ $id }}"
                  @if(isset($tabIndex)) tabindex="{{ $tabIndex ?? 0 }}" @endif
                  @if(isset($cols)) cols="{{ $cols ?? 0 }}" @endif
                  rows="{{ $rows ?? 3 }}"
                  id="{{ $id }}"
                  class="{{ $inputClass ?? 'form-control' }} {{ $errors->has($id) ? 'is-invalid' : '' }}"
                  @if(isset($required) && $required == '1') required @endif
                  @if(isset($autofocus) && $autofocus == '1') autofocus @endif
                {{ $inputOptions ?? '' }}>
                {{ $value ?? old($id) }}
        </textarea>
    @elseif(isset($inputType) && $inputType === 'select')
        <select type="{{ $inputType ?? 'text' }}"
                name="{{ $name ?? $id }}"
                aria-label="{{ $id }}"
                @if(isset($tabIndex)) tabindex="{{ $tabIndex ?? 0 }}" @endif
                id="{{ $id }}"
                class="{{ $inputClass ?? 'form-control' }} {{ $errors->has($id) ? 'is-invalid' : '' }}"
                @if(isset($required) && $required == '1') required @endif
                @if(isset($autofocus) && $autofocus == '1') autofocus @endif
            {{ $inputOptions ?? '' }}>
            {{ $selectOptions ?? '' }}
        </select>
    @else
        <input type="{{ $inputType ?? 'text' }}"
               name="{{ $name ?? $id }}"
               aria-label="{{ $id }}"
               @if(isset($tabIndex)) tabindex="{{ $tabIndex ?? 0 }}" @endif
               @if(isset($value)) value="{{ $value ?? '' }}" @endif
               id="{{ $id }}"
               class="{{ $inputClass ?? 'form-control' }} {{ $errors->has($id) ? 'is-invalid' : '' }}"
               @if(isset($required) && $required == '1') required @endif
               @if(isset($autofocus) && $autofocus == '1') autofocus @endif
            {{ $inputOptions ?? '' }}>
        </input>
    @endif
    {{ $slot }}
</div>