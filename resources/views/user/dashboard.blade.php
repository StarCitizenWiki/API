@extends('user.layouts.default_wide')

@section('title', __('Dashboard'))

{{-- Page Content --}}
@section('content')
    @can('web.user.users.view')
        <section class="row equal-height">
            <div class="col-12 col-md-12 col-lg-6 col-xl-3 mb-4">
                @component('user.components.card', [
                    'class' => 'bg-dark text-light',
                    'icon' => 'users',
                    'contentClass' => 'bg-white text-dark p-2 table-responsive',
                ])
                    @slot('title')
                        @lang('Benutzer') ({{ $users['overall'] }})
                        <small class="float-right mt-1">
                            <a href="{{ route('web.user.users.index') }}" class="text-light">
                                @component('components.elements.icon')
                                    external-link
                                @endcomponent
                            </a>
                        </small>
                    @endslot

                    <table class="table table-sm mb-2 border-top-0">
                        <tr>
                            <th>@lang('Benutzer'):</th>
                            <th class="text-right" title="@lang('Registrierungen')">
                                @component('components.elements.icon')
                                    user-plus
                                @endcomponent
                            </th>
                            <th class="text-right" title="@lang('Logins')">
                                @component('components.elements.icon')
                                    sign-in
                                @endcomponent
                            </th>
                        </tr>
                        <tr>
                            <td>@lang('In der letzten Stunde')</td>
                            <td class="text-right">{{ $users['registrations']['counts']['last_hour'] }}</td>
                            <td class="text-right">{{ $users['logins']['counts']['last_hour'] }}</td>
                        </tr>
                        <tr>
                            <td>@lang('Heute')</td>
                            <td class="text-right">{{ $users['registrations']['counts']['today'] }}</td>
                            <td class="text-right">{{ $users['logins']['counts']['today'] }}</td>
                        </tr>
                    </table>
                @endcomponent
            </div>

            <div class="col-12 col-md-12 col-lg-6 col-xl-6 mb-4">
                @component('user.components.card', [
                    'class' => 'bg-dark text-light',
                    'contentClass' => 'bg-white text-dark p-2 table-responsive',
                    'title' => __('Benutzerübersicht'),
                    'icon' => 'table',
                ])
                    <table class="table table-sm mb-2 border-top-0">
                        <tr>
                            @can('web.user.internals.view')
                                <th>@lang('ID')</th>
                            @endcan
                            <th>@lang('Name')</th>
                            <th>@lang('Registriert')</th>
                            @can('web.user.users.update')
                                <th>&nbsp;</th>
                            @endcan
                        </tr>
                        @foreach($users['last'] as $user)
                            <tr>
                                @can('web.user.internals.view')
                                    <td>{{ $user->getRouteKey() }}</td>
                                @endcan
                                <td title="{{ $user->email }}">{{ $user->username }}</td>
                                <td>{{ $user->created_at }}</td>
                                @can('web.user.users.update')
                                    <td class="text-center">
                                        <a href="{{ route('web.user.users.edit', $user->getRouteKey()) }}">
                                            @component('components.elements.icon')
                                                pencil
                                            @endcomponent
                                        </a>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </table>
                @endcomponent
            </div>
        </section>
    @endcan
@endsection
