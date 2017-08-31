@extends('admin.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title', __('Admin Login'))

@section('topNav--class', 'd-none')

@section('main--class', 'mt-5')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => route('api_index'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('Admin Login')</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => route('admin_login')])
                @component('components.forms.form-group', [
                    'label' => __('Benutzername'),
                    'id' => 'username',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => old('username'),
                    'tabIndex' => 1
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => __('Passwort'),
                    'id' => 'password',
                    'required' => 1,
                    'tabIndex' => 2
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">@lang('Login')</button>
            @endcomponent
        </div>
    </div>
@endsection