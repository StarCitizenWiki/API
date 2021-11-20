@component('mail::message')
# Neu importierte Comm-Links:

@component('mail::panel')
<ul>
    @foreach($commLinks as $commLink)
        <li style="margin-top: 0.5rem">
            {{ $commLink->commLink->cig_id }}: {{ $commLink->commLink->title }}
            <ul>
                <li style="display: inline"><a href="{{ config('api.rsi_url') }}{{ $commLink->$commLink->url }}}">RSI</a></li>
                <li style="display: inline">&mdash; <a href="{{ config('services.mediawiki.url') }}/Comm-Link:{{ $commLink->commLink->cig_id }}">Wiki</a> &mdash;</li>
                <li style="display: inline"><a href="{{ route('web.api.comm-links.show', $commLink->commLink->cig_id) }}">API</a></li>
            </ul>
        </li>
    @endforeach
</ul>
@endcomponent
@endcomponent