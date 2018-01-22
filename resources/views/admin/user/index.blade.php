@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Benutzer')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
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
                                <i class="far fa-trash-alt text-muted" title="@lang('Gelöscht')"></i>
                            @elseif($user->isWhitelisted())
                                <i class="far fa-circle text-success" title="@lang('Nicht limitiert')"></i>
                            @elseif($user->isBlacklisted())
                                <i class="far fa-stop-circle text-danger" title="@lang('Gesperrt')"></i>
                            @else
                                <i class="far fa-minus" title="@lang('Normal')"></i>
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