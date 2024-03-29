@extends('web.layouts.default_wide')

@section('title', __('Dashboard'))

{{-- Page Content --}}
@section('content')
    @include('components.errors')
    @include('components.messages')
    @guest
        <div class="card col-12 col-md-6 mx-auto pt-3">
            <img src="{{ asset('media/images/api_index.jpg') }}" class="card-img-top">
            <div class="card-body">
                <h4 class="card-title">@lang('Das Projekt')</h4>
                <div class="card-text">
                    <p>
                        @lang('Die Star Citizen Wiki API dient als Schnittstelle zwischen dem')
                        <a href="https://star-citizen.wiki" class="font-italic">@lang('Wiki')</a>
                        @lang('und diversen anderen Datenquellen.')
                    </p>
                    <p>
                        @lang('Du hast Interesse an Programmierung?')
                        <br>
                        @lang('Wir suchen immer engagierte Leute für unser Projekt.')
                    </p>
                    <p>
                        <a href="mailto:info@star-citizen.wiki" class="font-italic">@lang('Schreib')</a>
                        @lang('uns, oder besuch uns auf unserem')
                        <a href="https://discord.star-citizen.wiki" class="font-italic">@lang('Discord-Server')</a>!
                    </p>
                </div>
            </div>
        </div>
    @endguest
    @can('web.dashboard.view')
    @can('web.users.view')
        <section class="row equal-height">
            <div class="col-12 col-md-12 col-lg-6 col-xl-4 mb-4">
                @component('components.card', [
                    'icon' => 'users',
                    'contentClass' => 'table-responsive',
                ])
                    @slot('title')
                        @lang('Benutzer') ({{ $users['overall'] }})
                        <small class="float-right mt-1">
                            <a href="{{ route('web.users.index') }}" class="text-light">
                                @component('components.elements.icon')
                                    external-link-alt
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
                                    sign-in-alt
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
                @component('components.card', [
                    'contentClass' => 'table-responsive',
                    'icon' => 'users-viewfinder',
                    'title' => __('Benutzer Übersicht'),
                ])
                    <table class="table table-sm mb-2 border-top-0">
                        <tr>
                            @can('web.internals.view')
                                <th>@lang('ID')</th>
                            @endcan
                            <th>@lang('Name')</th>
                            <th title="@lang('API Benachrichtigungen')">@lang('API')</th>
                            <th title="@lang('CommLink Benachrichtigungen')">@lang('CommLinks')</th>
                            <th>@lang('Letzter Login')</th>
                            <th>@lang('Registriert')</th>
                            @can('web.users.update')
                                <th>&nbsp;</th>
                            @endcan
                        </tr>
                        @foreach($users['last'] as $user)
                            <tr>
                                @can('web.internals.view')
                                    <td>{{ $user->id }}</td>
                                @endcan
                                <td title="E-Mail" data-content="{{ $user->email }}" data-toggle="popover">
                                    {{ $user->username }}
                                </td>
                                <td>
                                    @if($user->receiveApiNotifications())
                                        @component('components.elements.icon')
                                            check
                                        @endcomponent
                                    @else
                                        @component('components.elements.icon')
                                            close
                                        @endcomponent
                                    @endif
                                </td>
                                <td>
                                    @if($user->receiveCommLinkNotifications())
                                    @component('components.elements.icon')
                                        check
                                    @endcomponent
                                    @else
                                        @component('components.elements.icon')
                                            close
                                        @endcomponent
                                    @endif
                                </td>
                                <td data-content="{{ $user->last_login->format('d.m.Y H:i:s') }}" data-toggle="popover">
                                    {{ $user->last_login->diffForHumans() }}
                                </td>
                                <td data-content="{{ $user->created_at->format('d.m.Y H:i:s') }}" data-toggle="popover">
                                    {{ $user->created_at->diffForHumans() }}
                                </td>
                                @can('web.users.update')
                                    <td class="text-center">
                                        <a href="{{ route('web.users.edit', $user->getRouteKey()) }}">
                                            @component('components.elements.icon')
                                                pen
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
            @component('components.card', [
                'icon' => 'language',
                'title' => __('DeepL Statistik'),
            ])
                <dl class="mb-0">
                    <dt>@lang('Übersetzungszeichenlimit'):</dt>
                    <dd>{{ $deepl['usage']['limit'] }}</dd>

                    <dt>@lang('Nutzung'):</dt>
                    <dd>{{ $deepl['usage']['count'] }}</dd>

                    <dt class="mb-1">@lang('Genutzt diesen Monat'):</dt>
                    <dd class="progress mb-0">
                        <div class="progress-bar {{ $deepl['bar']['style'] }}"
                             data-content="{{ $deepl['bar']['width'] }}%"
                             role="progressbar"
                             style="width: {{ $deepl['bar']['width'] }}%"
                             aria-valuenow="{{ $deepl['usage']['count'] }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $deepl['usage']['limit'] }}"
                             data-toggle="popover"
                        >
                            {{ round($deepl['bar']['width']) }}%
                        </div>
                    </dd>
                </dl>
            @endcomponent
        </div>

        <div class="col-12 col-md-12 col-lg-6 col-xl-7 mb-4">
            @component('components.card', [
                'icon' => 'network-wired',
                'title' => __('Comm-Link Jobs'),
            ])
            <div class="row">
                <div class="col-12 col-xl-5">
                    @can('web.jobs.start_translation')
                        @component('components.forms.form', [
                            'action' => route('web.dashboard.translate-comm-links'),
                            'class' => 'mb-3',
                        ])
                            <button class="btn btn-block btn-outline-secondary">@lang('Comm-Links Übersetzen')</button>
                        @endcomponent
                    @endcan

                    @can('web.jobs.start_wiki_page_creation')
                        @component('components.forms.form', [
                            'action' => route('web.dashboard.create-wiki-pages'),
                            'class' => 'mb-3',
                        ])
                            <button class="btn btn-block btn-outline-secondary">@lang('Comm-Link Wiki Seiten erstellen')</button>
                        @endcomponent
                    @endcan

                    @can('web.jobs.start_proofread_update')
                        @component('components.forms.form', [
                            'action' => route('web.dashboard.update-proofread-status'),
                        ])
                            <button class="btn btn-block btn-outline-secondary">@lang('Lektorierungsstatus aktualisieren')</button>
                        @endcomponent
                    @endcan
                </div>

                @can('web.jobs.start_download')
                <div class="col-12 col-xl-7">
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.download-comm-links'),
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
                @endcan
            </div>
            @endcomponent
        </div>

        <div class="col-12 col-md-12 col-lg-6 col-xl-2 mb-4">
            @component('components.card', [
                'icon' => 'person-digging',
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

    <section class="row equal-height">
        <div class="col-12 col-lg-6 col-xl-3 mb-4">
            @component('components.card', [
                'icon' => 'rocket',
                'title' => __('Vehicle Jobs'),
            ])
                @can('web.jobs.start_ship_matrix_download')
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.download-ship-matrix'),
                        'class' => 'mb-3',
                    ])
                        <button class="btn btn-block btn-outline-secondary">@lang('ShipMatrix importieren')</button>
                    @endcomponent
                @endcan
                @can('web.jobs.start_msrp_import')
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.import-vehicle-msrp'),
                        'class' => 'mb-3',
                    ])
                        <button class="btn btn-block btn-outline-secondary">@lang('Schiffspreise importieren')</button>
                    @endcomponent
                @endcan
                @can('web.jobs.start_msrp_import')
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.import-vehicle-loaner'),
                        'class' => 'mb-3',
                    ])
                        <button class="btn btn-block btn-outline-secondary">@lang('Loaner importieren')</button>
                    @endcomponent
                @endcan
            @endcomponent
        </div>
        <div class="col-12 col-lg-6 col-xl-3 mb-4">
            @component('components.card', [
                'icon' => 'database',
                'title' => __('Galactapedia Jobs'),
            ])
                @can('web.jobs.import_galactapedia_job')
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.import-galactapedia-categories'),
                        'class' => 'mb-3',
                    ])
                        <button class="btn btn-block btn-outline-secondary">@lang('Kategorien importieren')</button>
                    @endcomponent
                @endcan
                @can('web.jobs.import_galactapedia_job')
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.import-galactapedia-articles'),
                        'class' => 'mb-3',
                    ])
                        <button class="btn btn-block btn-outline-secondary">@lang('Artikel importieren')</button>
                    @endcomponent
                @endcan
                @can('web.jobs.import_galactapedia_job')
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.import-galactapedia-article-properties'),
                        'class' => 'mb-3',
                    ])
                        <button class="btn btn-block btn-outline-secondary">@lang('Eigenschaften importieren')</button>
                    @endcomponent
                @endcan
                @can('web.jobs.import_galactapedia_job')
                    @component('components.forms.form', [
                        'action' => route('web.dashboard.create-galactapedia-pages'),
                        'class' => 'mb-3',
                    ])
                        <button class="btn btn-block btn-outline-secondary">@lang('Wiki Seiten erstellen')</button>
                    @endcomponent
                @endcan
            @endcomponent
        </div>
        @can('web.jobs.sc-import')
        <div class="col-12 col-lg-6 col-xl-3 mb-4">
            @component('components.card', [
                'icon' => 'database',
                'title' => __('Spieldaten Jobs'),
            ])
                @component('components.forms.form', [
                    'action' => route('web.dashboard.import-sc-items'),
                    'class' => 'mb-3',
                ])
                    <button class="btn btn-block btn-outline-secondary">@lang('Items importieren')</button>
                @endcomponent

                @component('components.forms.form', [
                    'action' => route('web.dashboard.import-sc-vehicles'),
                    'class' => 'mb-3',
                ])
                    <button class="btn btn-block btn-outline-secondary">@lang('Fahrzeuge importieren')</button>
                @endcomponent
                @component('components.forms.form', [
                    'action' => route('web.dashboard.import-sc-shops'),
                    'class' => 'mb-3',
                ])
                    <button class="btn btn-block btn-outline-secondary">@lang('Shops importieren')</button>
                @endcomponent
                @component('components.forms.form', [
                    'action' => route('web.dashboard.translate-sc-items'),
                    'class' => 'mb-3',
                ])
                    <button class="btn btn-block btn-outline-secondary">@lang('Items übersetzen')</button>
                @endcomponent
            @endcomponent
        </div>
        @endcan
    </section>
    @endcan
@endsection