@extends('user.layouts.default_wide')

@section('title', __('Benutzer Übersicht'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Benutzer Übersicht')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    @can('web.user.internals.view')
                        <th>@lang('ID')</th>
                        <th>@lang('Provider ID')</th>
                    @endcan
                    <th>@lang('Benutzername')</th>
                    <th>@lang('E-Mail')</th>
                    <th>@lang('Gruppen')</th>
                    <th>@lang('Blockiert')</th>
                    <th>@lang('Provider')</th>
                    <th>@lang('Änderungen')</th>
                    <th title="@lang('API Benachrichtigungen')">@lang('API')</th>
                    <th title="@lang('CommLink Benachrichtigungen')">@lang('CommLinks')</th>
                    <th>@lang('Letzter Login')</th>
                    <th data-orderable="false">&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                @forelse($users as $user)
                    <tr>
                        @can('web.user.internals.view')
                            <td>
                                {{ $user->id }}
                            </td>
                            <td>
                                {{ $user->provider_id }}
                            </td>
                        @endcan
                        <td>
                            <a href="{{ $user->userNameWikiLink() }}" target="_blank">{{ $user->username }}</a>
                        </td>
                        <td>
                            {{ $user->email }}
                        </td>
                        <td>
                            {{ $user->groups->map(function($group) { return __($group->name); })->implode(', ') }}
                        </td>
                        <td>
                            {{ $user->blocked ? __('Ja') : __('Nein') }}
                        </td>
                        <td>
                            {{ $user->provider }}
                        </td>
                        <td>
                            {{ $user->changelogs_count }}
                        </td>
                        <td>
                            @if($user->receiveApiNotifications())
                            &check;
                            @else
                            &cross;
                            @endif
                        </td>
                        <td>
                            @if($user->receiveCommLinkNotifications())
                            &check;
                            @else
                            &cross;
                            @endif
                        </td>
                        <td title="{{ $user->last_login->format('d.m.Y H:i:s') }}">
                            {{ $user->last_login->diffForHumans() }}
                        </td>
                        <td class="text-center">
                            @component('components.edit_delete_block')
                                @can('web.user.users.update')
                                    @slot('edit_url')
                                        {{ route('web.user.users.edit', $user->getRouteKey()) }}
                                    @endslot
                                @endcan
                                {{ $user->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">@lang('Keine Benutzer vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    @include('components.init_dataTables')
@endsection