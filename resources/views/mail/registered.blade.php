@component('mail::message')
# Star Citizen Wiki API

**API Key:** `{{ $user->api_token }}`

**Password:** `{{ $user->password }}`

@component('mail::button', ['url' => config('app.api_url')])
Dokumentation
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
