@extends('admin.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title', trans('auth/login.header'))

@section('topNav--class', 'd-none')

@section('main--class', 'justify-content-center align-items-center d-flex mvh-100')

@section('P__content')
    <div class="col-sm-6 col-md-3 mb-5">
        @component('components.heading', [
            'class' => 'mb-5 text-white text-center',
            'contentClass' => 'mt-5',
            'imageClass' => 'mb-2',
            'route' => route('api_index'),
        ])@endcomponent

        <div class="row">
            <div class="col-12 col-md-10 mx-auto">
                @include('components.errors')

                <div class="card bg-dark text-light-grey">
                    <h4 class="card-header">Admin @lang('auth/login.header')</h4>
                    <div class="card-body">

                        @component('components.forms.form', ['action' => route('admin_login')])
                            @component('components.forms.form-group', [
                                'label' => trans('admin/auth.username'),
                                'id' => 'username',
                                'required' => 1,
                                'autofocus' => 1,
                                'value' => old('username'),
                                'tabIndex' => 1
                            ])@endcomponent

                            @component('components.forms.form-group', [
                                'inputType' => 'password',
                                'label' => trans('admin/auth.password'),
                                'id' => 'password',
                                'required' => 1,
                                'tabIndex' => 2
                            ])@endcomponent

                            <button class="btn btn-outline-secondary btn-block">
                                @lang('auth/login.login')
                            </button>
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection