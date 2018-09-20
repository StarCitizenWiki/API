@extends('admin.layouts.default_wide')

@section('title', __('Dashboard'))

{{-- Page Content --}}
@section('content')
    <section class="row equal-height">
        @can('web.admin.notifications.create')
            <div class="col-12 col-xl-6 mb-4">
                @component('admin.components.card', [
                    'class' => 'bg-dark',
                    'titleClass' => 'text-light',
                    'icon' => 'bullhorn',
                    'contentClass' => 'bg-white pb-md-0',
                    'title' => __('Benachrichtigung erstellen'),
                ])
                    @include('components.errors')
                    @component('components.forms.form', [
                        'class' => 'row',
                        'method' => 'POST',
                        'action' => route('web.admin.notifications.store'),
                    ])
                        <div class="col-12 col-md-7 order-2 order-lg-1">
                            @component('components.forms.form-group', [
                                'inputType' => 'textarea',
                                'label' => __('Notification'),
                                'id' => 'content',
                                'rows' => 5,
                            ])@endcomponent
                            <button class="btn btn-outline-secondary">@lang('Erstellen')</button>
                        </div>
                        <div class="col-12 col-md-5 order-1 order-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'select',
                                'inputClass' => 'custom-select w-100',
                                'label' => __('Typ'),
                                'id' => 'level',
                            ])
                                @slot('selectOptions')
                                    <option value="0">@lang('Info')</option>
                                    <option value="1">@lang('Warnung')</option>
                                    <option value="2">@lang('Fehler')</option>
                                    <option value="3">@lang('Kritisch')</option>
                                @endslot
                            @endcomponent

                            @component('components.forms.form-group', [
                                'inputType' => 'datetime-local',
                                'label' => __('Ablaufdatum'),
                                'id' => 'expired_at',
                                'value' => \Carbon\Carbon::now()->addDay()->format("Y-m-d\TH:i"),
                                'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
                            ])

                            @endcomponent

                            <div class="form-group">
                                <span class="d-block">@lang('Ausgabetyp'):</span>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="status" name="output[]"
                                           value="status" checked>
                                    <label class="custom-control-label" for="status">@lang('Statusseite')</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="index" name="output[]"
                                           value="index">
                                    <label class="custom-control-label" for="index">@lang('Startseite')</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email" name="output[]"
                                           value="email">
                                    <label class="custom-control-label" for="email">@lang('E-Mail')</label>
                                </div>
                            </div>
                        </div>
                    @endcomponent
                @endcomponent
            </div>
        @endcan

        @can('web.admin.notifications.view')
            <div class="col-12 col-xl-6 mb-4">
                @component('admin.components.card', [
                    'class' => 'bg-dark text-light',
                    'icon' => 'comment',
                    'contentClass' => 'bg-white text-dark text-center p-2 p-xxl-2 table-responsive',
                ])
                    @slot('title')
                        @lang('Aktive Benachrichtigungen')
                        <small class="float-right mt-1">
                            <a href="{{ route('web.admin.notifications.index') }}" class="text-light">
                                @component('components.elements.icon')
                                    external-link
                                @endcomponent
                            </a>
                        </small>
                    @endslot
                    <table class="table table-sm mb-0 text-left border-top-0">
                        <tr>
                            <th>@lang('Typ')</th>
                            <th>@lang('Inhalt')</th>
                            <th>@lang('Ablaufdatum')</th>
                            <th>@lang('Ausgabe')</th>
                            @can('web.admin.notifications.update')
                                <th>&nbsp;</th>
                            @endcan
                        </tr>
                        @forelse($notifications['last'] as $notification)
                            <tr>
                                <td class="text-{{ $notification->getBootstrapClass() }}">@lang(\App\Models\Api\Notification::NOTIFICATION_LEVEL_TYPES[$notification->level])</td>
                                <td title="{{ $notification->content }}">
                                    {{ str_limit($notification->content, 40) }}
                                </td>
                                <td title="{{ $notification->expired_at->format('d.m.Y H:i:s') }}">
                                    <span class="d-none d-xl-block">{{ $notification->expired_at->format('d.m.Y') }}</span>
                                    <span class="d-block d-xl-none">{{ $notification->expired_at->format('d.m.Y H:i:s') }}</span>
                                </td>
                                <td>
                                    @if($notification->output_status)
                                        @component('components.elements.icon', ['class' => 'mr-2'])
                                            desktop
                                        @endcomponent
                                    @endif
                                    @if($notification->output_email)
                                        @component('components.elements.icon', ['class' => 'mr-2'])
                                            envelope
                                        @endcomponent
                                    @endif
                                    @if($notification->output_index)
                                        @component('components.elements.icon')
                                            bullhorn
                                        @endcomponent
                                    @endif
                                </td>
                                @can('web.admin.notifications.update')
                                    <td class="text-center">
                                        <a href="{{ route('web.admin.notifications.edit', $notification->getRouteKey()) }}">
                                            @component('components.elements.icon')
                                                pencil
                                            @endcomponent
                                        </a>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">@lang('Keine Benachrichtigungen vorhanden')</td>
                            </tr>
                        @endforelse
                    </table>
                @endcomponent
            </div>
        @endcan
    </section>

    @can('web.admin.users.view')
        <section class="row equal-height">
            <div class="col-12 col-md-5 col-xl-3 mb-4">
                @component('admin.components.card', [
                    'class' => 'bg-dark text-light',
                    'icon' => 'users',
                    'contentClass' => 'bg-white text-dark p-2 table-responsive',
                ])
                    @slot('title')
                        @lang('Benutzer') ({{ $users['overall'] }})
                        <small class="float-right mt-1">
                            <a href="{{ route('web.admin.users.index') }}" class="text-light">
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

            <div class="col-12 col-md-7 col-xl-6 mb-4">
                @component('admin.components.card', [
                    'class' => 'bg-dark text-light',
                    'contentClass' => 'bg-white text-dark p-2 table-responsive',
                    'title' => __('Benutzerübersicht'),
                    'icon' => 'table',
                ])
                    <table class="table table-sm mb-2 border-top-0">
                        <tr>
                            @can('web.admin.internals.view')
                                <th>@lang('ID')</th>
                            @endcan
                            <th>@lang('Name')</th>
                            <th>@lang('Registriert')</th>
                            @can('web.admin.users.update')
                                <th>&nbsp;</th>
                            @endcan
                        </tr>
                        @foreach($users['last'] as $user)
                            <tr>
                                @can('web.admin.internals.view')
                                    <td>{{ $user->getRouteKey() }}</td>
                                @endcan
                                <td title="{{ $user->email }}">{{ $user->name }}</td>
                                <td>{{ $user->created_at }}</td>
                                @can('web.admin.users.update')
                                    <td class="text-center">
                                        <a href="{{ route('web.admin.users.edit', $user->getRouteKey()) }}">
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
