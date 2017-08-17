@extends('api.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title')
    @lang('auth/login.header')
@endsection

@section('topNav--class', 'd-none')

@section('main--class', 'justify-content-center align-items-center d-flex mvh-100')

@section('P__content')
    @component('components.elements.div', ['class' => 'col-sm-6 col-md-3 text-white mb-5'])
        @component('components.heading', [
            'class' => 'mb-4 text-white text-center',
            'contentClass' => 'mt-5',
            'imageClass' => 'mb-2',
            'route' => route('api_index')
        ])
            Star Citizen Wiki API
        @endcomponent

        @component('components.elements.div', ['class' => 'row'])
            @component('components.elements.div', ['class' => 'col-12 col-md-10 mx-auto'])
                @component('components.forms.form', [
                    'method' => 'POST',
                    'action' => route('auth_login')
                ])
                    @include('components.errors')

                    @component('components.forms.form-group', [
                        'inputType' => 'email',
                        'id' => 'email',
                        'required' => 1,
                        'autofocus' => 1,
                        'value' => old('email'),
                        'tabIndex' => 1
                    ])
                        @lang('auth/login.email'):
                    @endcomponent

                    @component('components.forms.form-group', [
                        'inputType' => 'password',
                        'id' => 'password',
                        'required' => 1,
                        'tabIndex' => 2
                    ])
                        @lang('auth/login.password'):
                    @endcomponent

                    @component('components.elements.div', ['class' => 'form-group mt-3'])
                        @component('components.elements.element', [
                            'type' => 'button',
                            'class' => 'btn'
                        ])
                            @lang('auth/login.login')
                        @endcomponent

                        @component('components.elements.a', [
                            'class' => 'btn btn-link pull-right text-white',
                            'href' => route('password.request')
                        ])
                            @lang('auth/login.forgot_password')
                        @endcomponent
                    @endcomponent
                @endcomponent
            @endcomponent
        @endcomponent
    @endcomponent
@endsection