@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', __('Registrieren'))

@section('main--class', 'mt-5')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => url('/'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('Registrieren')</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => route('auth.register')])
                @component('components.forms.form-group', [
                    'label' => __('Projekt / Organisation / Name'),
                    'id' => 'name',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => old('name'),
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => __('E-Mail'),
                    'id' => 'email',
                    'required' => 1,
                    'value' => old('email'),
                    'tabIndex' => 2,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => __('Passwort'),
                    'id' => 'password',
                    'required' => 1,
                    'tabIndex' => 3,
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => __('Passwort bestätigen'),
                    'id' => 'password_confirmation',
                    'required' => 1,
                    'tabIndex' => 4,
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">@lang('Registrieren')</button>
            @endcomponent
        </div>
    </div>
@endsection