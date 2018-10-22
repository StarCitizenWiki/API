@extends('user.layouts.default_wide')

@section('title', __('Dashboard'))

{{-- Page Content --}}
@section('content')
    @include('components.errors')
    @include('components.messages')
    @can('web.user.users.view')
        <section class="row equal-height">
            <div class="col-12 col-md-12 col-lg-6 col-xl-4 mb-4">
                @component('user.components.card', [
                    'icon' => 'users',
                    'contentClass' => 'table-responsive',
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

            <div class="col-12 col-md-12 col-lg-6 col-xl-8 mb-4">
                @component('user.components.card', [
                    'contentClass' => 'table-responsive',
                    'title' => __('Benutzerübersicht'),
                    'icon' => 'table',
                ])
                    <table class="table table-sm mb-2 border-top-0">
                        <tr>
                            @can('web.user.internals.view')
                                <th>@lang('ID')</th>
                            @endcan
                            <th>@lang('Name')</th>
                            <th title="@lang('API Benachrichtigungen')">@lang('API')</th>
                            <th title="@lang('CommLink Benachrichtigungen')">@lang('CommLinks')</th>
                            <th>@lang('Letzter Login')</th>
                            <th>@lang('Registriert')</th>
                            @can('web.user.users.update')
                                <th>&nbsp;</th>
                            @endcan
                        </tr>
                        @foreach($users['last'] as $user)
                            <tr>
                                @can('web.user.internals.view')
                                    <td>{{ $user->id }}</td>
                                @endcan
                                <td title="{{ $user->email }}">{{ $user->username }}</td>
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
                                <td title="{{ $user->last_login->format('d.m.Y H:i:s') }}">{{ $user->last_login->diffForHumans() }}</td>
                                <td title="{{ $user->created_at->format('d.m.Y H:i:s') }}">{{ $user->created_at->diffForHumans() }}</td>
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
    <section class="row equal-height">
        <div class="col-12 col-md-12 col-lg-6 col-xl-3 mb-4">
            @component('user.components.card', [
                'title' => __('DeepL Statistik'),
            ])
                <dl class="mb-0">
                    <dt>@lang('Übersetzungszeichenlimit'):</dt>
                    <dd>{{ $deepl['usage']['limit'] }}</dd>

                    <dt>@lang('Nutzung'):</dt>
                    <dd>{{ $deepl['usage']['count'] }}</dd>

                    <dt class="mb-1">@lang('Genutzt diesen Monat'):</dt>
                    <dd class="progress mb-0">
                        <div class="progress-bar {{ $deepl['bar']['style'] }}" title="{{ $deepl['bar']['width'] }}%" role="progressbar" style="width: {{ $deepl['bar']['width'] }}%" aria-valuenow="{{ $deepl['usage']['count'] }}" aria-valuemin="0" aria-valuemax="{{ $deepl['usage']['limit'] }}">
                            {{ round($deepl['bar']['width']) }}%
                        </div>
                    </dd>
                </dl>
            @endcomponent
        </div>

        <div class="col-12 col-md-12 col-lg-6 col-xl-6 mb-4">
                    @component('user.components.card', [
                        'title' => __('Comm-Link Jobs'),
                    ])
                    <div class="row">
                        <div class="col-12 col-xl-5">
                            @component('components.forms.form', [
                                'action' => route('web.user.dashboard.translate-comm-links'),
                                'class' => 'mb-3',
                            ])
                                <button class="btn btn-block btn-outline-secondary">@lang('Comm-Links Übersetzen')</button>
                            @endcomponent
                            @component('components.forms.form', [
                                'action' => route('web.user.dashboard.download-comm-link-images'),
                                'class' => 'mb-3',
                            ])
                                <button class="btn btn-block btn-outline-secondary">@lang('Comm-Link Bilder herunterladen')</button>
                            @endcomponent

                            @component('components.forms.form', [
                                'action' => route('web.user.dashboard.create-wiki-pages'),
                                'class' => 'mb-3',
                            ])
                                <button class="btn btn-block btn-outline-secondary">@lang('Comm-Link Wiki Seiten erstellen')</button>
                            @endcomponent
                            @component('components.forms.form', [
                                'action' => route('web.user.dashboard.update-proofread-status'),
                            ])
                                <button class="btn btn-block btn-outline-secondary">@lang('Lektorierungsstatus aktualisieren')</button>
                            @endcomponent
                        </div>

                        <div class="col-12 col-xl-7">
                            @component('components.forms.form', [
                                'action' => route('web.user.dashboard.download-comm-links'),
                                'class' => 'mb-3',
                            ])
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'label' => __('Comm-Link IDs'),
                                    'id' => 'ids',
                                ])
                                    @slot('inputOptions')
                                        pattern="[\d{5,}\,?\s?]+" title="12663, 12664, ..." placeholder="12663, 12664, ..."
                                    @endslot
                                    <small>@lang('Zu importierende Comm-Link IDs eingeben')</small>
                                @endcomponent
                                <button class="btn btn-block btn-outline-secondary">@lang('Comm-Links Herunterladen')</button>
                            @endcomponent
                        </div>
                    </div>
            @endcomponent
        </div>

        <div class="col-12 col-md-12 col-lg-6 col-xl-2 mb-4">
            @component('user.components.card', [
                'title' => __('Jobs'),
            ])
                <dl class="mb-0">
                    <dt>@lang('Alle'):</dt>
                    <dd>{{ $jobs['all'] }}</dd>

                    <dt>@lang('Aktiv'):</dt>
                    <dd>{{ $jobs['active'] }}</dd>

                    <dt>@lang('Fehlgeschlagen'):</dt>
                    <dd class="mb-0">{{ $jobs['failed'] }}</dd>
                </dl>
            @endcomponent
        </div>
    </section>
@endsection
