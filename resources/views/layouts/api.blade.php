@extends('layouts.default')

{{-- Page Title --}}
@section('P__title')
    @hasSection('title')
        Star Citizen Wiki API - @yield('title')
    @else
        Star Citizen Wiki API
    @endif
@endsection


{{-- Head --}}
@section('head__content')
    <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
@endsection


{{-- Body --}}
    {{-- Sidebar --}}
    @section('sidebar__pre')
        <a href="/" class="w-100 hidden-sm">
            <img src="{{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}" class="d-block mx-auto my-5 img-fluid" style="max-width: 100px;">
        </a>
    @endsection

    @section('sidebar__after')
        @component('layouts.components.sidebar_section')
            @slot('class', 'mt-5')

            @slot('options')
                style="position: absolute; bottom: 0; margin-bottom: 0 !important;"
            @endslot

            <li class="nav-item">
                <a href="https://star-citizen.wiki/" class="nav-link text-white">
                    Star Citizen Wiki - &copy; {{ date("Y") }}
                </a>
            </li>
        @endcomponent
    @endsection


    {{-- Main Content --}}
    @section('topNav--class', 'bg-blue-grey')

    @section('topNav__content')
        @if (Auth::guest())
            <li class="nav-item">
                <a class="nav-link" href="{{ route('auth_login') }}"><i class="fa fa-sign-in"></i> @lang('layouts/app.login')</a>
            </li>
        @else
            <li class="nav-item dropdown mr-2">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Account</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('account') }}">@lang('layouts/app.dashboard')</a>
                    <a class="dropdown-item" href="{{ route('account_urls_list') }}">@lang('layouts/app.short_urls')</a>
                    <a class="dropdown-item" href="{{ route('account_urls_add_form') }}">@lang('layouts/app.add_short_url')</a>
                </div>

            </li>
            <li class="nav-item mr-2">
                <a class="nav-link" href="{{ route('auth_logout') }}"
                   onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                    <form id="logout-form" action="{{ route('auth_logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                    <i class="fa fa-sign-out"></i> @lang('layouts/app.logout')
                </a>
            </li>
        @endif
    @endsection


@section('body__after')
    <script src="{{ mix('/js/app.js') }}"></script>
    @yield('scripts')
@endsection