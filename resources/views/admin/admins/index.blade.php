@extends('admin.layouts.default_wide')

@section('title', __('Admin Übersicht'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Admin Übersicht')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    @can('web.admin.internals.view')
                        <th>@lang('ID')</th>
                        <th>@lang('Provider ID')</th>
                    @endcan
                    <th>@lang('Benutzername')</th>
                    <th>@lang('E-Mail')</th>
                    <th>@lang('Gruppen')</th>
                    <th>@lang('Blockiert')</th>
                    <th>@lang('Provider')</th>
                    <th>@lang('Änderungen')</th>
                    <th>@lang('Letzter Login')</th>
                    <th data-orderable="false">&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                @forelse($admins as $admin)
                    <tr>
                        @can('web.admin.internals.view')
                            <td>
                                {{ $admin->id }}
                            </td>
                            <td>
                                {{ $admin->provider_id }}
                            </td>
                        @endcan
                        <td>
                            <a href="{{ config('api.wiki_url') }}/Benutzer:{{ $admin->username }}" target="_blank">{{ $admin->username }}</a>
                        </td>
                        <td>
                            {{ $admin->email }}
                        </td>
                        <td>
                            {{ $admin->groups->implode('name', ', ') }}
                        </td>
                        <td>
                            {{ $admin->blocked ? __('Ja') : __('Nein') }}
                        </td>
                        <td>
                            {{ $admin->provider }}
                        </td>
                        <td>
                            {{ $admin->changelogs_count }}
                        </td>
                        <td title="{{ $admin->last_login->format('d.m.Y H:i:s') }}">
                            {{ $admin->last_login->diffForHumans() }}
                        </td>
                        <td class="text-center">
                            @component('components.edit_delete_block')
                                @can('web.admin.admins.update')
                                    @slot('edit_url')
                                        {{ route('web.admin.admins.edit', $admin->getRouteKey()) }}
                                    @endslot
                                @endcan
                                {{ $admin->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">@lang('Keine Administratoren vorhanden')</td>
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