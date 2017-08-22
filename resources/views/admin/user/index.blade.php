@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">__LOC__ User</h4>
        <div class="card-body px-0">
            <table class="table table-striped table-responsive mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Registriert</th>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Notiz</th>
                    <th>API-Key</th>
                    <th>Status</th>
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
                        <td>
                            @if($user->trashed())
                                <span class="badge badge-info">
                                    @lang('admin/users/index.deleted')
                                </span>
                            @elseif($user->isWhitelisted())
                                <span class="badge badge-success">
                                    @lang('admin/users/index.whitelisted')
                                </span>
                            @elseif($user->isBlacklisted())
                                <span class="badge badge-danger">
                                    @lang('admin/users/index.blacklisted')
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    @lang('admin/users/index.normal')
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
                        <td colspan="7">__LOC__Users_Found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $users->links() }}</div>
    </div>
@endsection