@component('mail::message')
# Star Citizen Wiki URL Shortened

**ID:** `{{ $url->id }}`

**Route Key:** `{{ $url->getRouteKey() }}`

**Hash Name:** `{{ $url->hash }}`

**URL:** `{{ $url->url }}`

**Ablaufdatum:** `{{ $url->expired_at or '-' }}`

**Owner:** `{{ $url->user()->first()->email }}`
@endcomponent
