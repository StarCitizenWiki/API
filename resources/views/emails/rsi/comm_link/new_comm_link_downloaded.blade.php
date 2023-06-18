@component('mail::message')
# @lang('Neu importierte Comm-Links'):

@component('mail::panel')
<ul>
    @foreach($commLinks as $commLink)
        <li style="margin-top: 0.5rem">
            {{ $commLink->commLink->cig_id }}: {{ $commLink->commLink->title }}
            <ul style="display: flex">
                <li style="display: inline"><a href="{{ config('api.rsi_url') }}{{ $commLink->commLink->url }}">@lang('RSI')</a></li>
                <li style="display: inline">- <a href="{{ route('web.api.comm-links.show', $commLink->commLink->cig_id) }}">@lang('API')</a> -</li>
                <li style="display: inline"><a href="{{ config('services.mediawiki.url') }}/Comm-Link:{{ $commLink->commLink->cig_id }}">@lang('Wiki')</a></li>
            </ul>
        </li>
    @endforeach
</ul>
@endcomponent
@endcomponent