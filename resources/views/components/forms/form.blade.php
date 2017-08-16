<form method="{{ $method or '' }}" id="{{ $id or '' }}" class="{{ $class or '' }}" action="{{ $action or '' }}">
    {{ csrf_field() }}
    {{ $slot }}
</form>