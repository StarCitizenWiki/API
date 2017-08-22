@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', '__LOC__Register')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => route('api_index'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">__LOC__Register</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => route('auth_register')])
                @component('components.forms.form-group', [
                    'label' => '__LOC__Name/Project/Org',
                    'id' => 'name',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => old('name'),
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => '__LOC__Email',
                    'id' => 'email',
                    'required' => 1,
                    'value' => old('email'),
                    'tabIndex' => 2,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => '__LOC__Password',
                    'id' => 'password',
                    'required' => 1,
                    'tabIndex' => 3,
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => '__LOC__Password_Confirmation',
                    'id' => 'password_confirmation',
                    'required' => 1,
                    'tabIndex' => 4,
                ])@endcomponent

                <button class="btn btn-outline-secondary btn-block">
                    __LOC__Register
                </button>
            @endcomponent
        </div>
    </div>
@endsection