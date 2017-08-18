@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', trans('auth/passwords/email.header'))

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
        <h4 class="card-header">@lang('auth/passwords/email.header')</h4>
        <div class="card-body">
            @component('components.forms.form', ['action' => route('password.email')])

                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => trans('auth/passwords/email.email'),
                    'id' => 'email',
                    'labelClass' => 'control-label',
                    'value' => old('email'),
                    'required' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">
                    @lang('auth/passwords/email.send_mail')
                </button>
            @endcomponent
        </div>
    </div>
@endsection
