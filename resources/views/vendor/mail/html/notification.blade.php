<table class="notification {{ $class or '' }}" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="notification-title {{ $titleClass or '' }}">{{ $title or '' }}</td>
    </tr>
    <tr>
        <td class="notification-content {{ $contentClass or '' }}">{{ $slot }}</td>
    </tr>
</table>