@component('mail::message')

@component('mail::notification')
@slot('class', 'notification-warning')

@slot('title')
{{ __('Ship Matrix Struktur geändert') }}
@endslot
@slot('titleClass', 'notification-title-warning')
{{ __('Die Struktur der Ship Matrix wurde geändert. Import kann nicht fortgesetzt werden.') }}
@endcomponent
@endcomponent