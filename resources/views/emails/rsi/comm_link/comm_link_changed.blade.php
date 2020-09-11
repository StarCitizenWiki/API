@component('mail::message')
# Geänderte Comm-Links:

## Comm-Links mit neuem Inhalt: {{ $withoutContent->count() }}
Dies sind Comm-Links, welche bisher keinen Inhalt hatten.
@component('mail::panel')
<ul>
    @forelse($withoutContent as $item)
        <li>
            {{ $item->commLink->cig_id }}: {{ $item->commLink->title }}
            <span style="float: right">
                <a href="{{ route('web.api.comm-links.show', $item->commLink->cig_id) }}">API</a> &mdash;
                <a href="{{ config('services.mediawiki.url') }}/Comm-Link:{{ $item->commLink->cig_id }}">Wiki</a>
            </span>
        </li>
    @empty
        <li>Keine Comm-Links geändert</li>
    @endforelse
</ul>
@endcomponent

<br>
<br>

## Comm-Links mit geändertem Inhalt: {{ $withContent->count() }}
Dies sind Comm-Links, dessen Inhalt geändert wurden.
@component('mail::panel')
<ul>
    @forelse($withContent as $item)
        <li>
            {{ $item->commLink->cig_id }}: {{ $item->commLink->title }}
            <span style="float: right">
                <a href="{{ route('web.api.comm-links.show', $item->commLink->cig_id) }}">API</a> &mdash;
                <a href="{{ config('services.mediawiki.url') }}/Comm-Link:{{ $item->commLink->cig_id }}">Wiki</a>
            </span>
        </li>
    @empty
        <li>Keine Comm-Links geändert</li>
    @endforelse
</ul>
@endcomponent
@endcomponent