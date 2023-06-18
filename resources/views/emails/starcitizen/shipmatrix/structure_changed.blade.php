@component('mail::message')
@component('mail::notification')
@slot('class', 'notification-warning')

@slot('title')
{{ __('Schiff Matrix Struktur geändert') }}
@endslot
@slot('titleClass', 'notification-title-warning')
{{ __('Die Struktur der Schiff Matrix wurde geändert. Import kann nicht fortgesetzt werden.') }}
@endslot
@endcomponent
@endcomponent