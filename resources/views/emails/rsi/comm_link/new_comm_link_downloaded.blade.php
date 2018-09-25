@component('mail::message')
# Neu importierte Comm Links:

@component('mail::panel')
<ul>
    @foreach($commLinks as $commLink)
        <li>
            {{ $commLink->commLink->cig_id }}: {{ $commLink->commLink->title }} &mdash; <a href="{{ route('web.user.rsi.comm-links.show', $commLink->commLink->cig_id) }}">Link</a>
        </li>
    @endforeach
</ul>
@endcomponent
@endcomponent