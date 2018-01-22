<form method="POST"
      @if(isset($id)) id="{{ $id or '' }}" @endif
      @if(isset($action)) action="{{ $action or '' }}" @endif
      @if(isset($class)) class="{{ $class or '' }}" @endif>
    {{ csrf_field() }}
    @if (isset($method) && $method !== 'POST')
        @include('components.forms.fields.'.$method)
    @endif
    {{ $slot }}
</form>