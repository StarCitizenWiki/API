@component('mail::message')
# Star Citizen Wiki API

**API Key:** `{{ $user->api_key }}`

Wichtig!
Der API Key dient gleichzeitig als Account-Passwort. Gebe deinen Key niemals weiter und benutze ihn nicht als `GET` Parameter.

@component('mail::button', ['url' => 'https://api.star-citizen.wiki'])
Dokumentation
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
