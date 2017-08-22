@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', '__LOC__Reset_Password')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => route('auth_login'),
    ])@endcomponent

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('auth/passwords/reset.header')</h4>
        <div class="card-body">
            @component('components.forms.form', ['action' => route('password.request')])
                <input type="hidden" name="token" value="{{ $token or '' }}">

                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => '__LOC__Email',
                    'id' => 'email',
                    'required' => 1,
                    'autofocus' => 1,
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])
                    @slot('value'){{ $email or old('email') }}@endslot
                @endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => '__LOC__Password',
                    'id' => 'password',
                    'tabIndex' => 2,
                    'required' => 1,
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => '__LOC__Password_Confirmation',
                    'id' => 'password_confirmation',
                    'tabIndex' => 3,
                    'required' => 1,
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">
                    __LOC__Reset_Password
                </button>
            @endcomponent
        </div>
    </div>
@endsection