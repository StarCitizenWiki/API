@component('mail::message')
# Hello!
You are receiving this email because you registered an account on {{ config('app.api_url') }}.
Please don't forget to change your password.

**API Key:** `{{ $user->api_token }}`

**Password:** `{{ $password }}`

@component('mail::button', ['url' => config('app.api_url')])
Dokumentation
@endcomponent

<br>
{{ config('app.name') }}
@endcomponent
