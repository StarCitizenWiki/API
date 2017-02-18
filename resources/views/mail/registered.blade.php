@component('mail::message')
# Star Citizen Wiki API

**API Key:** `{{ $user->api_key }}`

@component('mail::button', ['url' => config('app.url')])
Dokumentation
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
