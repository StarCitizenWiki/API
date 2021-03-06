@extends('user.layouts.default_wide')

@section('title', __('Benachrichtigungsübersicht'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Benachrichtigungen')</h4>
        <div class="card-body px-0 table-responsive">
            @include('components.messages')
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    @can('web.user.internals.view')
                        <th>@lang('ID')</th>
                    @endcan
                    <th>@lang('Level')</th>
                    <th>@lang('Erstellt')</th>
                    <th>@lang('Inhalt')</th>
                    <th>@lang('Ausgabedatum')</th>
                    <th>@lang('Ablaufdatum')</th>
                    <th>@lang('Ausgabe')</th>
                    @can('web.user.notifications.update')
                        <th>&nbsp;</th>
                    @endcan
                </tr>
                </thead>
                <tbody>

                    @forelse($notifications as $notification)
                        <tr @if($notification->expired()) class="text-muted" @endif>
                            @can('web.user.internals.view')
                                <td>
                                    {{ $notification->getRouteKey() }}
                                </td>
                            @endcan
                            <td class="@unless($notification->expired()) text-{{ $notification->getBootstrapClass() }} @else text-muted @endunless">
                                {{ $notification->getLevelAsText() }}
                            </td>
                            <td title="{{ $notification->published_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->published_at->format('d.m.Y') }}
                            </td>
                            <td>
                                {{ $notification->content }}
                            </td>
                            <td title="{{ $notification->published_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->published_at->format('d.m.Y') }}
                            </td>
                            <td title="{{ $notification->expired_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->expired_at->format('d.m.Y') }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($notification->output_status)
                                        <button class="btn btn-link @if($notification->expired())text-muted @endif">
                                            @component('components.elements.icon')
                                                desktop
                                            @endcomponent
                                        </button>
                                    @endif
                                    @if($notification->output_email)
                                        <button class="btn btn-link @if($notification->expired())text-muted @endif">
                                            @component('components.elements.icon')
                                                envelope
                                            @endcomponent
                                        </button>
                                    @endif
                                    @if($notification->output_index)
                                        <button class="btn btn-link @if($notification->expired())text-muted @endif">
                                            @component('components.elements.icon')
                                                bullhorn
                                            @endcomponent
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @can('web.user.notifications.update')
                            <td>
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.user.notifications.edit', $notification->getRouteKey()) }}
                                    @endslot
                                    @slot('delete_url')
                                        {{ route('web.user.notifications.destroy', $notification->getRouteKey()) }}
                                    @endslot
                                    {{ $notification->getRouteKey() }}
                                @endcomponent
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">@lang('Keine Benachrichtigungen vorhanden')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $notifications->links() }}</div>
    </div>
@endsection

@section('body__after')
    @parent
    @if(count($notifications) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection