@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', __('Login'))

@section('main--class', 'mt-5')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => url('/'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('Api Login')</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => route('auth.login')])
                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => __('E-Mail'),
                    'id' => 'email',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => old('email'),
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => __('Passwort'),
                    'id' => 'password',
                    'required' => 1,
                    'tabIndex' => 2,
                ])@endcomponent

                <button class="btn btn-outline-secondary">@lang('Login')</button>
                <a href="{{ route('password.request') }}" class="btn btn-link float-right text-light-grey">@lang('Passwort vergessen')</a>
            @endcomponent
        </div>
    </div>
@endsection