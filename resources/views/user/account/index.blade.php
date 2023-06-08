@extends('user.layouts.default')

{{-- Page Title --}}
@section('title', __('Account'))

@section('content')
    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.user.account.update'),
        'class' => 'card',
    ])
        <h4 class="card-header">@lang('Account')</h4>

        <div class="card-body">
            @include('components.errors')
            @include('components.messages')
            <h5 class="card-title">@lang('Benutzerdaten')</h5>
            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => 'username',
                        'inputOptions' => 'readonly',
                        'value' => $user->username,
                        'label' => __('Benutzername'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => 'email',
                        'inputOptions' => 'readonly',
                        'value' => $user->email ?? __('Nicht vorhanden'),
                        'label' => __('E-Mail'),
                    ])@endcomponent
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => 'groups',
                        'inputOptions' => 'readonly',
                        'label' => __('Gruppe'),
                    ])
                        @slot('value')
                            {{ $user->groups->map(function($group) { return __($group->name); })->implode(', ') }}
                        @endslot
                    @endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => 'language',
                        'inputType' => 'select',
                        'label' => __('Sprache'),
                    ])
                        @slot('selectOptions')
                            <option value="de" @if($user->settings->language === 'de')selected @endif>@lang('de_DE')</option>
                            <option value="en" @if($user->settings->language === 'en')selected @endif>@lang('en_EN')</option>
                        @endslot
                    @endcomponent
                </div>
            </div>

            <hr>

            <h5 class="card-title">@lang('API Daten')</h5>
            @component('components.forms.form-group', [
                'id' => 'api_token',
                'value' => $user->api_token,
                'label' => __('API Token'),
            ])
                @slot('inputOptions')
                    readonly onClick="this.select();"
                @endslot
            @endcomponent

            <hr>

            <h5 style="margin: 0;">@lang('Benachrichtigungen')</h5>
            <small id="email_help_block" class="form-text text-muted" style="margin-bottom: .8rem;">
                @lang('Benachrichtigungen funktionieren nur, wenn du eine E-Mail-Adresse im Wiki hinterlegt hast').
            </small>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="api_notifications" name="api_notifications" aria-describedby="api_notification_help_block" @if($user->settings->receiveApiNotifications()) checked @endif>
                            <label class="custom-control-label" for="api_notifications">@lang('API Benachrichtigungen erhalten')</label>
                            <small id="api_notification_help_block" class="form-text text-muted">
                                @lang('Erhalte Benachrichtigungen über Statusänderungen der API')
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="receive_comm_link_notifications" name="receive_comm_link_notifications" aria-describedby="email_help_block" @if($user->settings->receiveCommLinkNotifications()) checked @endif>
                            <label class="custom-control-label" for="receive_comm_link_notifications">@lang('Comm-Link Benachrichtigungen erhalten')</label>
                            <small id="email_help_block" class="form-text text-muted">
                                @lang('Erhalte Benachrichtigungen über neue Comm-Links')
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex">
            <button class="btn btn-outline-secondary ml-auto">@lang('Speichern')</button>
        </div>
    @endcomponent
@endsection
