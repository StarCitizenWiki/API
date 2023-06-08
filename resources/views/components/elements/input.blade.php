<input type="{{ $type ?? 'text' }}"
       name="{{ $name ?? $id }}"
       aria-label="{{ $id ?? '' }}"

       @if(isset($tabIndex)) tabindex="{{ $tabIndex ?? 0 }}" @endif
       @if(isset($value)) value="{{ $value ?? '' }}" @endif
       @if(isset($id)) id="{{ $id ?? '' }}" @endif
       @if(isset($required) && $required == '1') required @endif
       @if(isset($autofocus) && $autofocus == '1') autofocus @endif
       class="{{ $labelClass ?? 'form-control' }}"
       {{ $inputOptions ?? '' }}
>