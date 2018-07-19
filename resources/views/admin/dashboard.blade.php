@extends('admin.layouts.default_wide')

{{-- Page Content --}}
@section('content')
    <section class="row equal-height">
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
                    'action' => route('web.admin.notifications.create'),
                ])
                    <div class="col-12 col-md-7 order-2 order-lg-1">
                        @component('components.forms.form-group', [
                            'inputType' => 'textarea',
                            'label' => __('Notification'),
                            'id' => 'content',
                            'rows' => 6,
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
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="status" name="output[]"
                                       value="status" checked>
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">@lang('Statusseite')</span>
                            </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="index" name="output[]"
                                       value="index">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">@lang('Startseite')</span>
                            </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="email" name="output[]"
                                       value="email">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">@lang('E-Mail')</span>
                            </label>
                        </div>
                    </div>
                @endcomponent
            @endcomponent
        </div>

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
                            <i class="far fa-external-link"></i>
                        </a>
                    </small>
                @endslot
                <table class="table table-sm mb-0 text-left border-top-0">
                    <tr>
                        <th>@lang('Typ')</th>
                        <th>@lang('Inhalt')</th>
                        <th>@lang('Ablaufdatum')</th>
                        <th>@lang('Ausgabe')</th>
                        <th></th>
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
                            <td class="text-center"><a href="{{ route('web.admin.notifications.edit', $notification->getRouteKey()) }}"><i class="far fa-pencil"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">@lang('Keine Notifications vorhanden')</td>
                        </tr>
                    @endforelse
                </table>
            @endcomponent
        </div>
    </section>

    <section class="row equal-height">
        <div class="col-12 col-md-6 col-xl-3 mb-4">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'icon' => 'book',
                'contentClass' => 'bg-white text-dark p-2 table-responsive',
            ])
                @slot('title')
                    @lang('Logs')
                    <small class="float-right mt-1">
                        <a href="" class="text-light">
                            <i class="far fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-2 border-top-0">
                    <tr>
                        <th>@lang('Logs'):</th>
                        <th class="text-right" title="@lang('Error')"><i class="far fa-exclamation-triangle"></i></th>
                        <th class="text-right" title="@lang('Warning')"><i class="far fa-exclamation"></i></th>
                        <th class="text-right" title="@lang('Info')"><i class="far fa-info"></i></th>
                        <th class="text-right" title="@lang('Debug')"><i class="far fa-bug"></i></th>
                    </tr>
                    <tr>
                        <td>@lang('In der letzten Stunde')</td>
                        <td class="text-right @if(count($logs['error']['last_hour']) > config('api.log.error.danger_hour')) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['error']['last_hour']) > config('api.log.error.warning_hour')) {{--
                    --}}text-warning{{--
                --}} @else {{--
                    --}}text-success{{--
                --}} @endif">{{ count($logs['error']['last_hour']) }}</td>
                        <td class="text-right @if(count($logs['warning']['last_hour']) > config('api.log.warning.danger_hour')) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['warning']['last_hour']) > config('api.log.warning.warning_hour')) {{--
                    --}}text-warning{{--
                --}} @else {{--
                    --}}text-success{{--
                --}} @endif">{{ count($logs['warning']['last_hour']) }}</td>
                        <td class="text-right">{{ count($logs['info']['last_hour']) }}</td>
                        <td class="text-right">@if(config('app.log_level') == 'debug'){{ count($logs['debug']['last_hour']) }}@else
                                - @endif</td>
                    </tr>
                    <tr>
                        <td>@lang('Heute')</td>
                        <td class="text-right @if(count($logs['error']['today']) > config('api.log.error.danger_day')) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['error']['today']) > config('api.log.error.warning_day')) {{--
                    --}}text-warning{{--
                --}} @else {{--
                    --}}text-success{{--
                --}} @endif">{{ count($logs['error']['today']) }}</td>
                        <td class="text-right @if(count($logs['warning']['today']) > config('api.log.warning.danger_day')) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['warning']['today']) > config('api.log.warning.warning_day')) {{--
                    --}}text-warning{{--
                --}} @else {{--
                    --}}text-success{{--
                --}} @endif">{{ count($logs['warning']['today']) }}</td>
                        <td class="text-right">{{ count($logs['info']['today']) }}</td>
                        <td class="text-right">@if(config('app.log_level') == 'debug'){{ count($logs['debug']['today']) }}@else
                                - @endif</td>
                    </tr>
                </table>
            @endcomponent
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-4">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'icon' => 'users',
                'contentClass' => 'bg-white text-dark p-2 table-responsive',
            ])
                @slot('title')
                    @lang('Benutzer') ({{ $users['overall'] }})
                    <small class="float-right mt-1">
                        <a href="{{ route('web.admin.users.index') }}" class="text-light">
                            <i class="far fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-2 border-top-0">
                    <tr>
                        <th>@lang('Benutzer'):</th>
                        <th class="text-right" title="@lang('Registrierungen')"><i class="far fa-user-plus"></i></th>
                        <th class="text-right" title="@lang('Logins')"><i class="far fa-sign-in"></i></th>
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

        <div class="col-12 col-md-6 col-xl-6 mb-4">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'contentClass' => 'bg-white text-dark p-2 table-responsive',
                'title' => __('Benutzerübersicht'),
                'icon' => 'table',
            ])
                <table class="table table-sm mb-2 border-top-0">
                    <tr>
                        <th>@lang('ID')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Registriert')</th>
                        <th></th>
                    </tr>
                    @foreach($users['last'] as $user)
                        <tr>
                            <td>{{ $user->getRouteKey() }}</td>
                            <td title="{{ $user->email }}">{{ $user->name }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td class="text-center"><a href="{{ route('web.admin.users.edit', $user->getRouteKey()) }}"><i class="far fa-pencil"></i></a></td>
                        </tr>
                    @endforeach
                </table>
            @endcomponent
        </div>
    </section>
@endsection
