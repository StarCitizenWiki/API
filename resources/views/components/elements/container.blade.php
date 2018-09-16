<div class="container{{ $type == 'fluid' ? '-fluid' : '' }} {{ $class ?? '' }}"
     @if(isset($id)) id="{{ $id ?? '' }}" @endif
    {{ $options ?? '' }}>
    {{ $slot}}
</div>