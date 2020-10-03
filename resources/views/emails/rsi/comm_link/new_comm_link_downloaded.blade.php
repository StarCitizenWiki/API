@component('mail::message')
# Neu importierte Comm-Links:

@component('mail::panel')
<ul>
    @foreach($commLinks as $commLink)
        <li>
            {{ $commLink->commLink->cig_id }}: {{ $commLink->commLink->title }}
            <span style="float: right">
                <a href="{{ route('web.api.comm-links.show', $commLink->commLink->cig_id) }}">API</a> &mdash;
                <a href="{{ config('services.mediawiki.url') }}/Comm-Link:{{ $commLink->commLink->cig_id }}">Wiki</a>
            </span>
        </li>
    @endforeach
</ul>
@endcomponent
@endcomponent