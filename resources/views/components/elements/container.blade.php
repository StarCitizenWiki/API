<div class="container{{ $type == 'fluid' ? '-fluid' : '' }} {{ $class or '' }}"
     @if(isset($id)) id="{{ $id or '' }}" @endif
    {{ $options or '' }}>
    {{ $slot}}
</div>