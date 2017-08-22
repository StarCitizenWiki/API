@component('mail::message')
    @component('mail::notification')
        @slot('class')
            @if($notification->type === 'critical' || $notification->type === 'danger')
                notification-danger
            @elseif($notification->type === 'warning')
                notification-warning
            @else
                notification-info
            @endif
        @endslot
        @slot('title')
            {{ trans($notification->type) }}
            <span style="float: right;">{{ $notification->created_at->format('d.m.Y H:i:s') }}</span>
        @endslot
        @slot('titleClass')
            @if($notification->type === 'critical' || $notification->type === 'danger')
                notification-title-danger
            @elseif($notification->type === 'warning')
                notification-title-warning
            @else
                notification-title-info
            @endif
        @endslot
        {{ $notification->content }}
    @endcomponent
@endcomponent
