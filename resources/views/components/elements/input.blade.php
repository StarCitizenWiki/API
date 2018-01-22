<input type="{{ $type or 'text' }}"
       name="{{ $name or $id }}"
       aria-label="{{ $id or '' }}"

       @if(isset($tabIndex)) tabindex="{{ $tabIndex or 0 }}" @endif
       @if(isset($value)) value="{{ $value or '' }}" @endif
       @if(isset($id)) id="{{ $id or '' }}" @endif
       @if(isset($required) && $required == '1') required @endif
       @if(isset($autofocus) && $autofocus == '1') autofocus @endif
       class="{{ $labelClass or 'form-control' }}"
       {{ $inputOptions or '' }}>