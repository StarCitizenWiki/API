@component('mail::message')
# @lang('Willkommen auf der Star Citizen Wiki Api!')
<br>
<br>
## @lang('Dein Api Key lautet:')

@component('mail::panel')
    <small>`{{ $user->api_token }}`</small>
@endcomponent
@lang('Bitte gib deinen Schlüssel nicht an dritte Weiter.')
<br>
<br>
@component('mail::button', ['url' => config('app.api_url')])
    @lang('Zur Dokumentation')
@endcomponent
@endcomponent