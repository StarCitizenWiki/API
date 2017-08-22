@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', '__LOC__Login')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => route('api_index'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">__LOC__API Login</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => route('auth_login')])
                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => '__LOC__Email',
                    'id' => 'email',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => old('email'),
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => '__LOC__Password',
                    'id' => 'password',
                    'required' => 1,
                    'tabIndex' => 2,
                ])@endcomponent

                <button class="btn btn-outline-secondary">
                    __LOC__Login
                </button>
                <a href="{{ route('password.request') }}" class="btn btn-link pull-right text-light-grey">
                    __LOC__Forgot_Password
                </a>
            @endcomponent
        </div>
    </div>
@endsection