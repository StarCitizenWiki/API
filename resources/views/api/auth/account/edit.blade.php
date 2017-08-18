@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', trans('auth/account/edit.header'))

@section('content')
@include('components.errors')

<div class="card">
    <h4 class="card-header">@lang('auth/account/edit.header')</h4>
    <div class="card-body">
        <h6 class="card-title">Stammdaten:</h6>
        @component('components.forms.form', [
            'method' => 'PATCH',
            'action' => route('account_update'),
        ])
            @component('components.forms.form-group', [
                'id' => 'name',
                'label' => trans('auth/account/edit.name'),
                'value' => $user->name,
                'autofocus' => 1,
                'tabIndex' => 1,
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'inputType' => 'email',
                'id' => 'email',
                'label' => trans('auth/account/edit.email'),
                'value' => $user->email,
                'tabIndex' => 2,
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'inputType' => 'select',
                'id' => 'receive_notification_level',
                'label' => trans('auth/account/edit.notification_level'),
                'tabIndex' => 3,
            ])
                @slot('selectOptions')
                    <option value="-1" @if($user->receive_notification_level == -1) selected @endif>Keine</option>
                    <option value="0" @if($user->receive_notification_level == 0) selected @endif>Info and up</option>
                    <option value="1" @if($user->receive_notification_level == 1) selected @endif>Warning and up</option>
                    <option value="2" @if($user->receive_notification_level == 2) selected @endif>Danger and up</option>
                    <option value="3" @if($user->receive_notification_level == 3) selected @endif>Critical</option>
                @endslot
                <small class="form-text text-muted">Level der Benachrichtigungen per Mail</small>
            @endcomponent

            <a href="#edit-password" class="d-block h6 mt-5 card-title" data-toggle="collapse">Edit Password:</a>
            <div class="{{ $errors->has('password') ? 'show' : 'collapse' }}" id="edit-password">
                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => ('auth/account/edit.password'),
                    'id' => 'password',
                    'tabIndex' => 4,
                    'inputOptions' => 'data-minlength="8"',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => trans('auth/account/edit.password_confirmation'),
                    'id' => 'password_confirmation',
                    'tabIndex' => 5,
                    'inputClass' => $errors->has('password') ? 'form-control is-invalid' : 'form-control',
                    'inputOptions' => 'data-minlength="8"',
                ])@endcomponent
            </div>

            <button class="btn btn-outline-success btn-block-xs-only pull-right">@lang('auth/account/edit.edit')</button>
        @endcomponent
    </div>
</div>
@endsection