@unless(is_null($expired_at))
    @if(Carbon\Carbon::parse($expired_at)->lte(\Carbon\Carbon::now()))
        <span class="text-warning">@lang('Abgelaufen')</span>
    @else
        {{ Carbon\Carbon::parse($expired_at)->format('d.m.Y H:i') }}
    @endif
    @else
        -
@endunless