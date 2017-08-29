@extends('user.layouts.default')

{{-- Page Title --}}
@section('title', __('Account bearbeiten'))

@section('content')
@include('components.errors')

<div class="card">
    <h4 class="card-header">@lang('Account bearbeiten')</h4>
    <div class="card-body">
        <h6 class="card-title">@lang('Stammdaten'):</h6>
        @component('components.forms.form', [
            'method' => 'PATCH',
            'action' => route('account_update'),
        ])
            @component('components.forms.form-group', [
                'id' => 'name',
                'label' => __('Projekt / Organisation / Name'),
                'value' => $user->name,
                'autofocus' => 1,
                'tabIndex' => 1,
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'inputType' => 'email',
                        'id' => 'email',
                        'label' => __('E-Mail'),
                        'value' => $user->email,
                        'tabIndex' => 2,
                        'inputOptions' => 'spellcheck=false',
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'inputType' => 'select',
                        'inputClass' => 'custom-select w-100',
                        'id' => 'receive_notification_level',
                        'label' => __('Benachrichtigungslevel'),
                        'tabIndex' => 3,
                    ])
                        @slot('selectOptions')
                            <option value="-1" @if($user->receive_notification_level == -1) selected @endif>@lang('Keine')</option>
                            <option value="0" @if($user->receive_notification_level == 0) selected @endif>@lang('Info')</option>
                            <option value="1" @if($user->receive_notification_level == 1) selected @endif>@lang('Warnung')</option>
                            <option value="2" @if($user->receive_notification_level == 2) selected @endif>@lang('Fehler')</option>
                            <option value="3" @if($user->receive_notification_level == 3) selected @endif>@lang('Kritisch')</option>
                        @endslot
                        <small class="form-text text-muted">@lang('Level der Benachrichtigungen per Mail')</small>
                    @endcomponent
                </div>
            </div>

            <a href="#edit-password" class="d-block h6 mt-5 card-title" data-toggle="collapse">@lang('Passwort bearbeiten'):</a>
            <div class="{{ $errors->has('password') ? 'show' : 'collapse' }}" id="edit-password">
                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => __('Passwort'),
                    'id' => 'password',
                    'tabIndex' => 4,
                    'inputOptions' => 'data-minlength="8"',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => __('Passwort bestätigen'),
                    'id' => 'password_confirmation',
                    'tabIndex' => 5,
                    'inputClass' => $errors->has('password') ? 'form-control is-invalid' : 'form-control',
                    'inputOptions' => 'data-minlength="8"',
                ])@endcomponent
            </div>

            <button class="btn btn-outline-success btn-block-xs-only float-right">@lang('Speichern')</button>
        @endcomponent
    </div>
</div>
@endsection