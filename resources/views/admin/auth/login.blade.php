@extends('admin.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title', __('Admin Login'))

@section('topNav--class', 'd-none')

@section('main--class', 'mt-5')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => url('/'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('Admin Login')</h4>
        <div class="card-body">
            <p>
                @lang('Login via Star Citizen Wiki OAuth')
            </p>
            <a href="{{ route('web.admin.auth.login.start') }}" class="btn btn-secondary btn-block">@lang('Login')</a>
        </div>
    </div>
@endsection