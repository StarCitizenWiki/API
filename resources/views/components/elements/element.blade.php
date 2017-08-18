<{{ $type }}
    @if(isset($id)) id="{{ $id or '' }}" @endif
    @if(isset($class)) class="{{ $class or '' }}" @endif
    {{ $options or '' }}>
    {{ $slot }}
</{{ $type }}>
