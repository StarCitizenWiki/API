@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Benutzer')</h4>
        <div class="card-body px-0 table-responsive">
            @include('components.messages')
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    @can('web.admin.internals.view')
                        <th>@lang('ID')</th>
                    @endcan
                    <th>@lang('Registrierdatum')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('E-Mail')</th>
                    <th>@lang('Notiz')</th>
                    <th>@lang('API Schlüssel')</th>
                    <th class="text-center">@lang('Status')</th>
                    @can('web.admin.users.update')
                        <th>&nbsp;</th>
                    @endcan
                </tr>
                </thead>
                <tbody>

                @forelse($users as $user)
                    <tr @if($user->trashed()) class="text-muted" @endif>
                        @can('web.admin.internals.view')
                            <td>
                                {{ $user->getRouteKey() }}
                            </td>
                        @endcan
                        <td title="{{ $user->created_at->format('d.m.Y H:i:s') }}">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td>
                            {{ $user->name }}
                        </td>
                        <td>
                            {{ $user->email }}
                        </td>
                        <td>
                            {{ $user->notes }}
                        </td>
                        <td>
                            {{ $user->api_token }}
                        </td>
                        <td class="text-center">
                            @if($user->trashed())
                                @component('components.elements.icon', [
                                    'class' => 'text-muted'
                                ])
                                    @slot('options')
                                        title="@lang('Gelöscht')"
                                    @endslot
                                    trash-alt
                                @endcomponent
                            @elseif($user->isUnthrottled())
                                @component('components.elements.icon', [
                                    'class' => 'text-success'
                                ])
                                    @slot('options')
                                        title="@lang('Nicht limitiert')"
                                    @endslot
                                    circle
                                @endcomponent
                            @elseif($user->isBlocked())
                                @component('components.elements.icon', [
                                    'class' => 'text-danger'
                                ])
                                    @slot('options')
                                        title="@lang('Gesperrt')"
                                    @endslot
                                    stop-circle
                                @endcomponent
                            @else
                                @component('components.elements.icon')
                                    @slot('options')
                                        title="@lang('Normal')"
                                    @endslot
                                    minus
                                @endcomponent
                            @endif
                        </td>
                        @can('web.admin.users.update')
                            <td>
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.admin.users.edit', $user->getRouteKey()) }}
                                    @endslot
                                    @if($user->trashed())
                                        @slot('restore_url')
                                            {{ route('web.admin.users.update', $user->getRouteKey()) }}
                                        @endslot
                                    @else
                                        @slot('delete_url')
                                            {{ route('web.admin.users.destroy', $user->getRouteKey()) }}
                                        @endslot
                                    @endif
                                    {{ $user->getRouteKey() }}
                                @endcomponent
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">@lang('Keine Benutzer vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $users->links() }}</div>
    </div>
@endsection