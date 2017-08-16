<li class="nav-item {{ $class or '' }}">{{--
    --}}@if(empty($route) || '-' === $route){{--
    --}}<span class="nav-link {{ $contentClass or '' }}" {{ $options or '' }}>{{--
        --}}{{ $slot or '' }}{{--
    --}}</span>{{--
    --}}@else{{--
    --}}<a href="{{--
        --}}@if($route === '#' || str_contains($route, '//')){{--
            --}}{{ $route }}{{--
        --}}@else{{--
            --}}{{ route($route) }}{{--
        --}}@endif" class="nav-link @if(Route::currentRouteName() == $route) active @endif {{ $contentClass or '' }}" {{ $options or '' }}>{{--
        --}}{{ $slot or '' }}{{--
    --}}</a>{{--
    --}}@endif{{--
    --}}{{ $body or '' }}
</li>