<nav class="navbar navbar-expand-md navbar-dark col {{ $class or '' }}">
    @if (isset($title) && strlen($title) > 0)
        <a class="navbar-brand {{ $titleClass or '' }}" href="{{ $titleLink or '#' }}">{{ $title }}</a>
    @endif

    <button class="navbar-toggler {{ $togglerClass or '' }}"
            type="button"
            data-toggle="collapse"
            data-target="#{{ $navID or 'nav-top-menu' }}"
            aria-controls="{{ $navID or 'nav-top-menu' }}"
            aria-expanded="false"
            aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div id="{{ $navID or 'nav-top-menu' }}" class="collapse navbar-collapse justify-content-end align-end{{ $contentClass or '' }}">
        <ul class="navbar-nav {{$navbarClass or ''}}">
            {{ $slot }}
        </ul>
    </div>
</nav>