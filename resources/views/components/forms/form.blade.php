<form method="POST"
      @if(isset($id)) id="{{ $id ?? '' }}" @endif
      @if(isset($action)) action="{{ $action ?? '' }}" @endif
      @if(isset($class)) class="{{ $class ?? '' }}" @endif
      @if(isset($enctype)) enctype="{{ $enctype ?? '' }}" @endif>
    {{ csrf_field() }}
    @if (isset($method) && $method !== 'POST')
        @include('components.forms.fields.'.$method)
    @endif
    {{ $slot }}
</form>