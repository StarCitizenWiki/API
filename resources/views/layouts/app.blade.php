<!DOCTYPE html>
<html style="overflow-y: scroll">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Star Citizen Wiki API - @yield('title')</title>
        @if ($bootstrapModules['enableCSS'])
            <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
        @endif

        <!-- Scripts -->
        <script>
            window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
        </script>
        @yield('header')
    </head>
    <body>
        <ul class="nav nav-pills mt-2 mr-2 justify-content-end ">
            @if(App::isLocal() || (!is_null(Auth::user()) && Auth::user()->isAdmin()))
                <li class="nav-item mr-2">
                    <a class="nav-link" href="{{ route('admin_dashboard') }}">@lang('layouts/app.admin')</a>
                </li>
            @endif
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
        </ul>
        @yield('content')

        @if ($bootstrapModules['enableJS'])
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.js" integrity="sha256-jVfFb7AbGi7S/SLNl8SB4/MYaf549eEs+NlIWMoARHg=" crossorigin="anonymous"></script>
            <script src="{{ URL::asset('/js/app.js') }}"></script>
        @endif

        @yield('scripts')
    </body>
</html>
