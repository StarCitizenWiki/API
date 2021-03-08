<nav class="navbar navbar-dark col {{ $class ?? '' }}">
    @if (isset($title) && strlen($title) > 0)
        <a class="navbar-brand {{ $titleClass ?? '' }}" href="{{ $titleLink ?? '#' }}">{{ $title }}</a>
    @endif

    <button class="navbar-toggler {{ $togglerClass ?? '' }}"
            type="button"
            data-toggle="collapse"
            data-target="#{{ $navID ?? 'nav-top-menu' }}"
            aria-controls="{{ $navID ?? 'nav-top-menu' }}"
            aria-expanded="false"
            aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div id="{{ $navID ?? 'nav-top-menu' }}" class="collapse navbar-collapse justify-content-end align-end{{ $contentClass ?? '' }}">
        <ul class="navbar-nav {{$navbarClass ?? ''}}">
            {{ $slot }}
        </ul>
    </div>
</nav>