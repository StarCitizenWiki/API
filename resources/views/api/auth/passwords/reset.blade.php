@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', trans('auth/passwords/reset.header'))

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
                    'label' => trans('auth/login.email'),
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
                    'label' => trans('auth/passwords/reset.password'),
                    'id' => 'password',
                    'tabIndex' => 2,
                    'required' => 1,
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => trans('auth/passwords/reset.password_confirmation'),
                    'id' => 'password_confirmation',
                    'tabIndex' => 3,
                    'required' => 1,
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">
                    @lang('auth/passwords/reset.reset_password')
                </button>
            @endcomponent
        </div>
    </div>
@endsection