<!DOCTYPE html>
<html style="overflow-y: scroll">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title')</title>
        @if ($bootstrapModules['enableCSS'])
            <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
        @endif

        <!-- Scripts -->
        <script>
            window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
        </script>
    </head>
    <body>
        <ul class="nav nav-pills mt-2 mr-2 justify-content-end ">
            @if(App::isLocal() || (!is_null(Auth::user()) && Auth::user()->isAdmin()))
                <li class="nav-item dropdown mr-2">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Admin</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ url('admin/users') }}">User</a>
                        <a class="dropdown-item" href="{{ url('admin/routes') }}">Routes</a>
                    </div>
                </li>
            @endif
            @if (Auth::guest())
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/login') }}"><i class="fa fa-sign-in"></i> Login</a>
                </li>
            @else
                <li class="nav-item mr-2">
                    <a class="nav-link" href="{{ url('/account') }}">Account</a>
                </li>
                <li class="nav-item mr-2">
                    <a class="nav-link" href="{{ url('/logout') }}"
                       onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                       <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                           {{ csrf_field() }}
                       </form>
                       <i class="fa fa-sign-out"></i> Logout
                    </a>
                </li>
            @endif
        </ul>
        @yield('content')

        @if ($bootstrapModules['enableJS'])
            <script src="{{ URL::asset('/js/app.js') }}"></script>
        @endif

        @yield('scripts')
    </body>
</html>
