<table class="notification {{ $class ?? '' }}" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="notification-title {{ $titleClass ?? '' }}">{{ $title ?? '' }}</td>
    </tr>
    <tr>
        <td class="notification-content {{ $contentClass ?? '' }}">{{ $slot }}</td>
    </tr>
</table>