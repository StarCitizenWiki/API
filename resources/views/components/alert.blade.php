<div class="alert alert-{{ $type or 'default' }}">
    @if(isset($title))
        <h4>{{ $title }}</h4>
    @endif
    {{ $slot }}
</div>