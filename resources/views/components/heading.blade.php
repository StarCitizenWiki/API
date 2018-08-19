@if(isset($class) && strlen($class) > 0)
<div class="{{ $class ?? '' }}">
@endif
    @unless(isset($hideImage))
        @if(isset($route) && strlen($route) > 0)
        <a href="{{ $route ?? '' }}" class="{{ $linkClass ?? '' }}">
        @endif
            <img src="{{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}" style="max-width: 120px;" class="{{ $imageClass ?? '' }}">
        @if(isset($route) && strlen($route) > 0)
        </a>
        @endif
    @endunless

    @unless(strlen($slot) === 0)
        <h1 class="{{ $titleClass ?? '' }}">{{ $slot }}</h1>
    @endunless

    @unless(empty($subTitle))
        <p class="lead {{ $subTitleClass ?? '' }}">{{ $subTitle ?? '' }}</p>
    @endunless
@if(isset($class) && strlen($class) > 0)
</div>
@endif