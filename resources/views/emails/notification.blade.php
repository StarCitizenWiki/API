@component('mail::message')
#{{ __('Statusbenachrichtigung der Star Citizen Wiki Api:') }}
@component('mail::notification')
@slot('class')
@if($notification->getLevelAsText() === 'critical' || $notification->getLevelAsText() === 'danger')
notification-danger
@elseif($notification->getLevelAsText() === 'warning')
notification-warning
@else
notification-info
@endif
@endslot
@slot('title')
{{ trans($notification->getLevelAsText()) }}
<span style="float: right;">{{ $notification->published_at->format('d.m.Y H:i:s') }}</span>
@endslot
@slot('titleClass')
@if($notification->getLevelAsText() === 'critical' || $notification->getLevelAsText() === 'danger')
notification-title-danger
@elseif($notification->getLevelAsText() === 'warning')
notification-title-warning
@else
notification-title-info
@endif
@endslot
{{ $notification->content }}
@endcomponent
@endcomponent