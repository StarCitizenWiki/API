@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Notifications')</h4>
        <div class="card-body px-0">
            <table class="table table-striped table-responsive mb-0">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('Level')</th>
                    <th>@lang('Erstellt')</th>
                    <th>@lang('Inhalt')</th>
                    <th>@lang('Ablaufdatum')</th>
                    <th>@lang('Ausgabe')</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                    @forelse($notifications as $notification)
                        <tr @if($notification->trashed()) class="text-muted" @endif>
                            <td>
                                {{ $notification->getRouteKey() }}
                            </td>
                            <td @unless($notification->trashed()) class="text-{{ $notification->getBootstrapClass() }} @endunless">
                                {{ \App\Models\Notification::NOTIFICATION_LEVEL_TYPES[$notification->level] }}
                            </td>
                            <td title="{{ $notification->created_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->created_at->format('d.m.Y') }}
                            </td>
                            <td>
                                {{ $notification->content }}
                            </td>
                            <td title="{{ $notification->expired_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->expired_at->format('d.m.Y') }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($notification->output_status)
                                        <button class="btn btn-link @if($notification->trashed())text-muted @endif">
                                            @component('components.elements.icon')
                                                desktop
                                            @endcomponent
                                        </button>
                                    @endif
                                    @if($notification->output_email)
                                        <button class="btn btn-link @if($notification->trashed())text-muted @endif">
                                            @component('components.elements.icon')
                                                envelope-o
                                            @endcomponent
                                        </button>
                                    @endif
                                    @if($notification->output_index)
                                        <button class="btn btn-link @if($notification->trashed())text-muted @endif">
                                            @component('components.elements.icon')
                                                bullhorn
                                            @endcomponent
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('admin_notifications_edit_form', $notification->getRouteKey()) }}
                                    @endslot
                                    @if($notification->trashed())
                                        @slot('restore_url')
                                            {{ route('admin_notifications_restore', $notification->getRouteKey()) }}
                                        @endslot
                                    @else
                                        @slot('delete_url')
                                            {{ route('admin_notifications_delete', $notification->getRouteKey()) }}
                                        @endslot
                                    @endif
                                    {{ $notification->getRouteKey() }}
                                @endcomponent
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">@lang('Keine Notifications vorhanden')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $notifications->links() }}</div>
    </div>
@endsection