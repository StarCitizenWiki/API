@if(isset($class) && strlen($class) > 0)
<div class="{{ $class or '' }}">
@endif
    @unless(isset($hideImage))
        @if(isset($route) && strlen($route) > 0)
        <a href="{{ $route or '' }}" class="{{ $linkClass or '' }}">
        @endif
            <img src="{{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}" style="max-width: 120px;" class="{{ $imageClass or '' }}">
        @if(isset($route) && strlen($route) > 0)
        </a>
        @endif
    @endunless

    @unless(strlen($slot) === 0)
        <h1 class="{{ $titleClass or '' }}">{{ $slot }}</h1>
    @endunless

    @unless(empty($subTitle))
        <p class="lead {{ $subTitleClass or '' }}">{{ $subTitle or '' }}</p>
    @endunless
@if(isset($class) && strlen($class) > 0)
</div>
@endif