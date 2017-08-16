<div class="container{{ $type == 'fluid' ? '-fluid' : '' }} {{ $class or '' }}" id="{{ $id or '' }}" {{ $options or '' }}>
    {{ $slot or '' }}
</div>