@component('mail::message')
# Geänderte Comm Links:

## Comm Links mit neuem Inhalt: {{ $withoutContent->count() }}
Dies sind Comm Links, welche bisher keinen Inhalt hatten.
@component('mail::panel')
<ul>
    @forelse($withoutContent as $item)
        <li>
            {{ $item->commLink->cig_id }}: {{ $item->commLink->title }} &mdash; <a href="{{ route('web.admin.rsi.comm-links.show', $item->commLink->cig_id) }}">Link</a>
        </li>
    @empty
        <li>Keine Comm Links geändert</li>
    @endforelse
</ul>
@endcomponent

<br>
<br>

## Comm Links mit geändertem Inhalt: {{ $withContent->count() }}
Dies sind Comm Links, dessen Inhalt geändert wurden.
@component('mail::panel')
<ul>
    @forelse($withContent as $item)
        <li>
            {{ $item->commLink->cig_id }}: {{ $item->commLink->title }} &mdash; <a href="{{ route('web.admin.rsi.comm-links.show', $item->commLink->cig_id) }}">Link</a>
        </li>
    @empty
        <li>Keine Comm Links geändert</li>
    @endforelse
</ul>
@endcomponent
@endcomponent