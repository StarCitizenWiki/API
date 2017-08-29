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
                    'action' => route('admin_notification_add'),
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
                'icon' => 'comment-o',
                'contentClass' => 'bg-white text-dark text-center p-0',
            ])
                @slot('title')
                    @lang('Benachrichtigungen')
                    <small class="pull-right mt-1">
                        <a href="{{ route('admin_notification_list') }}" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot
                <table class="table table-responsive table-sm mb-0 text-left">
                    <tr>
                        <th>@lang('Typ')</th>
                        <th>@lang('Inhalt')</th>
                        <th>@lang('Ablaufdatum')</th>
                        <th>@lang('Ausgabe')</th>
                    </tr>
                    @forelse($notifications['last'] as $notification)
                        <tr @if($notification->expired()) class="text-muted" @endif>
                            <td @unless($notification->expired()) class="text-{{ $notification->getBootstrapClass() }}" @endunless>@lang(\App\Models\Notification::NOTIFICATION_LEVEL_TYPES[$notification->level])</td>
                            <td title="{{ $notification->content }}">
                                <a href="{{ route('admin_notification_edit_form', $notification->getRouteKey()) }}" @if($notification->expired()) class="text-muted" @endif>{{ str_limit($notification->content, 40) }}</a>
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
                'contentClass' => 'bg-white text-dark p-2',
            ])
                @slot('title')
                    @lang('Logs')
                    <small class="pull-right mt-1">
                        <a href="" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-2">
                    <tr>
                        <th class="border-top-0">@lang('Logs'):</th>
                        <th class="border-top-0 text-right" title="@lang('Error')"><i class="fa fa-exclamation-triangle"></i>
                        </th>
                        <th class="border-top-0 text-right" title="@lang('Warning')"><i class="fa fa-exclamation"></i></th>
                        <th class="border-top-0 text-right" title="@lang('Info')"><i class="fa fa-info"></i></th>
                        <th class="border-top-0 text-right" title="@lang('Debug')"><i class="fa fa-bug"></i></th>
                    </tr>
                    <tr>
                        <td>@lang('In der letzten Stunde')</td>
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
                        <td>@lang('Heute')</td>
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
                    @lang('Benutzer') ({{ $users['overall'] }})
                    <small class="pull-right mt-1">
                        <a href="{{ route('admin_user_list') }}" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-2">
                    <tr>
                        <th class="border-top-0">@lang('Benutzer'):</th>
                        <th class="border-top-0 text-right" title="@lang('Registrierungen')"><i class="fa fa-user-plus"></i></th>
                        <th class="border-top-0 text-right" title="@lang('Logins')"><i class="fa fa-sign-in"></i></th>
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
        <div class="col-12 col-md-6 col-xl-3 mb-4">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'icon' => 'code',
                'contentClass' => 'bg-white text-dark p-2',
            ])
                @slot('title')
                    @lang('Api Anfragen') ({{ $api_requests['counts']['overall'] }})
                    <small class="pull-right mt-1">
                        <a href="{{ route('admin_request_list') }}" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-0">
                    <tr>
                        <th class="border-top-0">@lang('Anfragen'):</th>
                        <th class="border-top-0 text-right"><i class="fa fa-caret-square-o-right"></i></th>
                    </tr>
                    <tr>
                        <td>@lang('In der letzten Stunde')</td>
                        <td class="text-right">{{ $api_requests['counts']['last_hour'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('Heute')</td>
                        <td class="text-right">{{ $api_requests['counts']['today'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('Insgesamt')</td>
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
                    @lang('ShortUrls') ({{ $short_urls['counts']['overall'] }})
                    <small class="pull-right mt-1">
                        <a href="{{ route('admin_url_list') }}" class="text-light">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </small>
                @endslot

                <table class="table table-sm mb-0">
                    <tr>
                        <th class="border-top-0">@lang('Erstellt'):</th>
                        <th class="border-top-0 text-right"><i class="fa fa-plus-square"></i></th>
                    </tr>
                    <tr>
                        <td>@lang('In der letzten Stunde')</td>
                        <td class="text-right">{{ $short_urls['counts']['last_hour'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('Heute')</td>
                        <td class="text-right">{{ $short_urls['counts']['today'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('Insgesamt')</td>
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
                'title' => __('Benutzerübersicht'),
                'icon' => 'table',
            ])
                <table class="table table-responsive table-sm mb-0">
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
                'title' => __('Api Request Übersicht'),
                'icon' => 'table',
            ])
                <table class="table table-responsive table-sm mb-0">
                    <tr>
                        <th>@lang('Benutzer')</th>
                        <th>@lang('Datum')</th>
                        <th>@lang('Pfad')</th>
                    </tr>
                    @forelse($api_requests['last'] as $api_request)
                        <tr>
                            <td>{{ $api_request->user->name }}</td>
                            <td>{{ $api_request->created_at }}</td>
                            <td>{{ $api_request->request_uri }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">@lang('Keine Anfragen vorhanden')</td>
                        </tr>
                    @endforelse
                </table>
            @endcomponent
        </div>
        <div class="col-12 col-xl-4 mb-4 mb-xl-0">
            @component('admin.components.card', [
                'class' => 'bg-dark text-light',
                'contentClass' => 'bg-white text-dark p-2',
                'title' => __('ShortUrl Übersicht'),
                'icon' => 'table',
            ])
                <table class="table table-responsive table-sm mb-0">
                    <tr>
                        <th>@lang('ID')</th>
                        <th>@lang('Url')</th>
                        <th>@lang('Erstelldatum')</th>
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
