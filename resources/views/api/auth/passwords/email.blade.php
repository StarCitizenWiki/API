@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', __('Passwort zurücksetzen'))

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
        <h4 class="card-header">@lang('Passwort zurücksetzen')</h4>
        <div class="card-body">
            @component('components.forms.form', ['action' => route('password.email')])

                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => __('E-Mail'),
                    'id' => 'email',
                    'labelClass' => 'control-label',
                    'value' => old('email'),
                    'required' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">@lang('E-Mail senden')</button>
            @endcomponent
        </div>
    </div>
@endsection
