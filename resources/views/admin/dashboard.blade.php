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
                'title' => 'Benachrichtigung erstellen',
            ])
                @include('components.errors')
                @component('components.forms.form', [
                    'class' => 'row',
                    'action' => route('admin_notification_add'),
                ])
                    <div class="col-12 col-md-7 order-2 order-lg-1">
                        @component('components.forms.form-group', [
                            'inputType' => 'textarea',
                            'label' => 'Notification',
                            'id' => 'content',
                            'rows' => 6,
                        ])@endcomponent
                        <button class="btn btn-outline-secondary">Erstellen</button>
                    </div>
                    <div class="col-12 col-md-5 order-1 order-lg-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'select',
                            'inputClass' => 'custom-select w-100',
                            'label' => 'Typ',
                            'id' => 'level',
                        ])
                            @slot('selectOptions')
                                <option value="0">Info</option>
                                <option value="1">Warnung</option>
                                <option value="2">Fehler</option>
                                <option value="3">Kritisch</option>
                            @endslot
                        @endcomponent

                        @component('components.forms.form-group', [
                            'inputType' => 'datetime-local',
                            'label' => 'Ablaufdatum',
                            'id' => 'expired_at',
                            'value' => \Carbon\Carbon::now()->addDay()->format("Y-m-d\TH:i"),
                            'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
                        ])

                        @endcomponent

                        <div class="form-group">
                            <span class="d-block">Ausgabetyp:</span>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="status" name="output[]"
                                       value="status" checked>
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Status</span>
                            </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="index" name="output[]"
                                       value="index">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Startseite</span>
                            </label>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="email" name="output[]"
                                       value="email">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">E-Mail</span>
                            </label>
                        </div>
                    </div>
                @endcomponent
            @endcomponent
        </div>

        <div class="col-12 col-xl-6 mb-4">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'icon' => 'comment-o',
                'contentClass' => 'bg-white text-dark text-center p-0',
            ])
                @slot('title')
                    Benachrichtigungen
                    <small class="pull-right mt-1">
                        <a href="{{ route('admin_notifications_list') }}" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot
                <table class="table table-responsive table-sm mb-0 text-left">
                    <tr>
                        <th>Typ</th>
                        <th>Inhalt</th>
                        <th>Ablaufdatum</th>
                        <th>Ausgabe</th>
                    </tr>
                    @forelse($notifications['last'] as $notification)
                        <tr @if($notification->expired()) class="text-muted" @endif>
                            <td class="text-{{ $notification->getBootstrapClass() }}">@lang(\App\Models\Notification::NOTIFICATION_LEVEL_TYPES[$notification->level])</td>
                            <td title="{{ $notification->content }}">
                                <a href="{{ route('admin_notifications_edit_form', $notification->getRouteKey()) }}">{{ str_limit($notification->content, 40) }}</a>
                            </td>
                            <td>{{ $notification->expired_at->format('d.m.Y H:i:s') }}</td>
                            <td>
                                @if($notification->output_status)
                                    @component('components.elements.icon', ['class' => 'mr-2'])
                                        desktop
                                    @endcomponent
                                @endif
                                @if($notification->output_email)
                                    @component('components.elements.icon', ['class' => 'mr-2'])
                                        envelope-o
                                    @endcomponent
                                @endif
                                @if($notification->output_index)
                                    @component('components.elements.icon')
                                        bullhorn
                                    @endcomponent
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No Not</td>
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
                'contentClass' => 'bg-white text-dark p-2',
            ])
                @slot('title')
                    Logs
                    <small class="pull-right mt-1">
                        <a href="" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-2">
                    <tr>
                        <th class="border-top-0">Logs:</th>
                        <th class="border-top-0 text-right" title="Error"><i class="fa fa-exclamation-triangle"></i>
                        </th>
                        <th class="border-top-0 text-right" title="Warning"><i class="fa fa-exclamation"></i></th>
                        <th class="border-top-0 text-right" title="Info"><i class="fa fa-info"></i></th>
                        <th class="border-top-0 text-right" title="Debug"><i class="fa fa-bug"></i></th>
                    </tr>
                    <tr>
                        <td>In der letzten Stunde</td>
                        <td class="text-right @if(count($logs['error']['last_hour']) > LOG_ERROR_DANGER_HOUR) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['error']['last_hour']) > LOG_ERROR_WARNING_HOUR) {{--
                    --}}text-warning{{--
                --}} @else {{--
                    --}}text-success{{--
                --}} @endif">{{ count($logs['error']['last_hour']) }}</td>
                        <td class="text-right @if(count($logs['warning']['last_hour']) > LOG_WARNING_DANGER_HOUR) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['warning']['last_hour']) > LOG_WARNING_WARNING_HOUR) {{--
                    --}}text-warning{{--
                --}} @else {{--
                    --}}text-success{{--
                --}} @endif">{{ count($logs['warning']['last_hour']) }}</td>
                        <td class="text-right">{{ count($logs['info']['last_hour']) }}</td>
                        <td class="text-right">@if(config('app.log_level') == 'debug'){{ count($logs['debug']['last_hour']) }}@else
                                - @endif</td>
                    </tr>
                    <tr>
                        <td>Heute</td>
                        <td class="text-right @if(count($logs['error']['today']) > LOG_ERROR_DANGER_DAY) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['error']['today']) > LOG_ERROR_WARNING_DAY) {{--
                    --}}text-warning{{--
                --}} @else {{--
                    --}}text-success{{--
                --}} @endif">{{ count($logs['error']['today']) }}</td>
                        <td class="text-right @if(count($logs['warning']['today']) > LOG_WARNING_DANGER_DAY) {{--
                    --}}text-danger{{--
                --}} @elseif(count($logs['warning']['today']) > LOG_WARNING_WARNING_DAY) {{--
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
                'contentClass' => 'bg-white text-dark p-2',
            ])
                @slot('title')
                    Benutzer ({{ $users['overall'] }})
                    <small class="pull-right mt-1">
                        <a href="" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-2">
                    <tr>
                        <th class="border-top-0">Benutzer:</th>
                        <th class="border-top-0 text-right" title="Registrierungen"><i class="fa fa-user-plus"></i></th>
                        <th class="border-top-0 text-right" title="Logins"><i class="fa fa-sign-in"></i></th>
                    </tr>
                    <tr>
                        <td>In der letzten Stunde</td>
                        <td class="text-right">{{ $users['registrations']['counts']['last_hour'] }}</td>
                        <td class="text-right">{{ $users['logins']['counts']['last_hour'] }}</td>
                    </tr>
                    <tr>
                        <td>Heute</td>
                        <td class="text-right">{{ $users['registrations']['counts']['today'] }}</td>
                        <td class="text-right">{{ $users['logins']['counts']['today'] }}</td>
                    </tr>
                </table>
            @endcomponent
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-4">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'icon' => 'code',
                'contentClass' => 'bg-white text-dark p-2',
            ])
                @slot('title')
                    API Requests ({{ $api_requests['counts']['overall'] }})
                    <small class="pull-right mt-1">
                        <a href="" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-0">
                    <tr>
                        <th class="border-top-0">Abfragen:</th>
                        <th class="border-top-0 text-right"><i class="fa fa-caret-square-o-right"></i></th>
                    </tr>
                    <tr>
                        <td>In der letzten Stunde</td>
                        <td class="text-right">{{ $api_requests['counts']['last_hour'] }}</td>
                    </tr>
                    <tr>
                        <td>Heute</td>
                        <td class="text-right">{{ $api_requests['counts']['today'] }}</td>
                    </tr>
                    <tr>
                        <td>Insgesamt</td>
                        <td class="text-right">{{ $api_requests['counts']['overall'] }}</td>
                    </tr>
                </table>
            @endcomponent
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-4">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'icon' => 'link',
                'contentClass' => 'bg-white text-dark p-2',
            ])
                @slot('title')
                    ShortURLs ({{ $short_urls['counts']['overall'] }})
                    <small class="pull-right mt-1">
                        <a href="" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-0">
                    <tr>
                        <th class="border-top-0">Erstellt:</th>
                        <th class="border-top-0 text-right"><i class="fa fa-plus-square"></i></th>
                    </tr>
                    <tr>
                        <td>In der letzten Stunde</td>
                        <td class="text-right">{{ $short_urls['counts']['last_hour'] }}</td>
                    </tr>
                    <tr>
                        <td>Heute</td>
                        <td class="text-right">{{ $short_urls['counts']['today'] }}</td>
                    </tr>
                    <tr>
                        <td>Insgesamt</td>
                        <td class="text-right">{{ $short_urls['counts']['overall'] }}</td>
                    </tr>
                </table>
            @endcomponent
        </div>
    </section>

    <div class="row equal-height">
        <div class="col-12 col-xl-4 mb-4 mb-xl-0">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'contentClass' => 'bg-white text-dark p-2',
                'title' => 'Benutzerübersicht',
                'icon' => 'table',
            ])
                <table class="table table-responsive table-sm mb-0">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Registriert</th>
                        <th></th>
                    </tr>
                    @foreach($users['last'] as $user)
                        <tr>
                            <td>{{ $user->getRouteKey() }}</td>
                            <td title="{{ $user->email }}">{{ $user->name }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td class="text-center"><i class="fa fa-external-link"></i></td>
                        </tr>
                    @endforeach
                </table>
            @endcomponent
        </div>
        <div class="col-12 col-xl-4 mb-4 mb-xl-0">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'contentClass' => 'bg-white text-dark p-2',
                'title' => 'API Request Übersicht',
                'icon' => 'table',
            ])
                <table class="table table-responsive table-sm mb-0">
                    <tr>
                        <th>User</th>
                        <th>Time</th>
                        <th>Path</th>
                    </tr>
                    @if(empty($api_requests['last']))
                        <tr>
                            <td colspan="3">No Requests</td>
                        </tr>
                    @endif
                    @foreach($api_requests['last'] as $api_request)
                        <tr>
                            <td>{{ $api_request->user->name }}</td>
                            <td>{{ $api_request->created_at }}</td>
                            <td>{{ $api_request->request_uri }}</td>
                        </tr>
                    @endforeach
                </table>
            @endcomponent
        </div>
        <div class="col-12 col-xl-4 mb-4 mb-xl-0">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'contentClass' => 'bg-white text-dark p-2',
                'title' => 'ShortURL Übersicht',
                'icon' => 'table',
            ])
                <table class="table table-responsive table-sm mb-0">
                    <tr>
                        <th>ID</th>
                        <th>URL</th>
                        <th>Erstellt</th>
                        <th></th>
                    </tr>
                    @foreach($short_urls['last'] as $short_url)
                        <tr>
                            <td><a href="">{{ $short_url->hash }}</a></td>
                            <td title="{{ $short_url->url }}">{{ parse_url($short_url->url)['host'] }}</td>
                            <td>{{ $short_url->created_at }}</td>
                            <td class="text-center"><i class="fa fa-external-link"></i></td>
                        </tr>
                    @endforeach
                </table>
            @endcomponent
        </div>
    </div>
@endsection
