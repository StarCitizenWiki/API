@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', '__LOC__Edit Account')

@section('content')
@include('components.errors')

<div class="card">
    <h4 class="card-header">__LOC__Edit_Account</h4>
    <div class="card-body">
        <h6 class="card-title">__LOC__Stammdaten:</h6>
        @component('components.forms.form', [
            'method' => 'PATCH',
            'action' => route('account_update'),
        ])
            @component('components.forms.form-group', [
                'id' => 'name',
                'label' => '__LOC__Name',
                'value' => $user->name,
                'autofocus' => 1,
                'tabIndex' => 1,
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'inputType' => 'email',
                'id' => 'email',
                'label' => '__LOC__Email',
                'value' => $user->email,
                'tabIndex' => 2,
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'inputType' => 'select',
                'id' => 'receive_notification_level',
                'label' => '__LOC__Notification_Level',
                'tabIndex' => 3,
            ])
                @slot('selectOptions')
                    <option value="-1" @if($user->receive_notification_level == -1) selected @endif>__LOC__Keine</option>
                    <option value="0" @if($user->receive_notification_level == 0) selected @endif>__LOC__Info and up</option>
                    <option value="1" @if($user->receive_notification_level == 1) selected @endif>__LOC__Warning and up</option>
                    <option value="2" @if($user->receive_notification_level == 2) selected @endif>__LOC__Danger and up</option>
                    <option value="3" @if($user->receive_notification_level == 3) selected @endif>__LOC__Critical</option>
                @endslot
                <small class="form-text text-muted">__LOC__Level der Benachrichtigungen per Mail</small>
            @endcomponent

            <a href="#edit-password" class="d-block h6 mt-5 card-title" data-toggle="collapse">__LOC__Edit Password:</a>
            <div class="{{ $errors->has('password') ? 'show' : 'collapse' }}" id="edit-password">
                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => '__LOC__Edit_Password',
                    'id' => 'password',
                    'tabIndex' => 4,
                    'inputOptions' => 'data-minlength="8"',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => '__LOC__Password_Confirmation',
                    'id' => 'password_confirmation',
                    'tabIndex' => 5,
                    'inputClass' => $errors->has('password') ? 'form-control is-invalid' : 'form-control',
                    'inputOptions' => 'data-minlength="8"',
                ])@endcomponent
            </div>

            <button class="btn btn-outline-success btn-block-xs-only pull-right">__LOC__Edit</button>
        @endcomponent
    </div>
</div>
@endsection