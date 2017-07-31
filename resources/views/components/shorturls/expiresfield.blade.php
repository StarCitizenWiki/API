@unless(is_null($expires))
    @if(Carbon\Carbon::parse($expires)->lte(\Carbon\Carbon::now()))
        <span class="text-warning">@lang('components/shorturls/expiresfield.expired')</span>
    @else
        {{ Carbon\Carbon::parse($expires)->format('d.m.Y H:i') }}
    @endif
@else
-
@endunless