@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', trans('auth/register.header'))

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => route('api_index'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">API @lang('auth/register.header')</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => route('auth_register')])
                @component('components.forms.form-group', [
                    'label' => trans('auth/register.org_project_name'),
                    'id' => 'name',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => old('name'),
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => trans('auth/login.email'),
                    'id' => 'email',
                    'required' => 1,
                    'value' => old('email'),
                    'tabIndex' => 2,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => trans('auth/login.password'),
                    'id' => 'password',
                    'required' => 1,
                    'tabIndex' => 3,
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => trans('auth/passwords/reset.password_confirmation'),
                    'id' => 'password_confirmation',
                    'required' => 1,
                    'tabIndex' => 4,
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">
                    @lang('auth/register.register')
                </button>
            @endcomponent
        </div>
    </div>
@endsection