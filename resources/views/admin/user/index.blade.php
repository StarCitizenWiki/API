@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Benutzer')</h4>
        <div class="card-body px-0">
            <table class="table table-striped table-responsive mb-0">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('Registrierdatum')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('E-Mail')</th>
                    <th>@lang('Notiz')</th>
                    <th>@lang('API Key')</th>
                    <th class="text-center">@lang('Status')</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                @forelse($users as $user)
                    <tr @if($user->trashed()) class="text-muted" @endif>
                        <td>
                            {{ $user->id }}
                        </td>
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
                                <span class="badge badge-info">
                                    @lang('Gelöscht')
                                </span>
                            @elseif($user->isWhitelisted())
                                <span class="badge badge-success">
                                    @lang('Unlimitiert')
                                </span>
                            @elseif($user->isBlacklisted())
                                <span class="badge badge-danger">
                                    @lang('Gesperrt')
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    @lang('Normal')
                                </span>
                            @endif
                        </td>
                        <td>
                            @component('components.edit_delete_block')
                                @slot('edit_url')
                                    {{ route('admin_user_edit_form', $user->getRouteKey()) }}
                                @endslot
                                @if($user->trashed())
                                    @slot('restore_url')
                                        {{ route('admin_user_restore', $user->getRouteKey()) }}
                                    @endslot
                                @else
                                    @slot('delete_url')
                                        {{ route('admin_user_delete', $user->getRouteKey()) }}
                                    @endslot
                                @endif
                                {{ $user->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine Benutzer vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $users->links() }}</div>
    </div>
@endsection