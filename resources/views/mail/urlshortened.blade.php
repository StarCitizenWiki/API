@component('mail::message')
# Star Citizen Wiki URL Shortened

**URL:** `{{ $url->url }}`

**Hash Name:** `{{ $url->hash_name }}`

**Owner:** `{{ \App\Models\User::find($url->user_id)->email }}`

<br>
{{ config('app.name') }}
@endcomponent
