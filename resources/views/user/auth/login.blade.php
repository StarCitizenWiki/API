@extends('user.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title', __('Login'))

@section('topNav--class', 'd-none')

@section('main--class', 'mt-5')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => url('/'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('Login')</h4>
        <div class="card-body">
            <p>
                @lang('Login via Star Citizen Wiki OAuth')
            </p>
            <a href="{{ route('web.user.auth.login.start') }}" class="btn btn-secondary btn-block">@lang('Login')</a>
        </div>
    </div>
@endsection