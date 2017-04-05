<!DOCTYPE html>
<html style="overflow-y: scroll; min-height: 100%;">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Star Citizen Wiki API Admin - @yield('title')</title>
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
    <body style="min-height: 100vh;">
        <div class="container-fluid" style="min-height: 100vh;">
            <div class="row" style="min-height: 100vh;">
                <div class="col-12 col-md-2 bg-inverse pb-4" style="min-height: 100vh;">
                    <img src="https://star-citizen.wiki/images/thumb/e/ef/Star_Citizen_Wiki_Logo.png/157px-Star_Citizen_Wiki_Logo.png" class="d-block mx-auto my-4 img-fluid">
                    <ul class="nav flex-column">
                        <li class="nav-item ">
                            <a class="nav-link text-muted disabled" href="#">App</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="//{{ config('app.api_url') }}"><i class="fa fa-cogs mr-1"></i> API</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white" href="//{{ config('app.tools_url') }}"><i class="fa fa-wrench mr-1"></i> Tools</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="//{{ config('app.shorturl_url') }}"><i class="fa fa-link mr-1"></i> ShortURL</a>
                        </li>
                    </ul>
                    <ul class="nav flex-column mt-4">
                        <li class="nav-item ">
                            <a class="nav-link text-muted disabled" href="#">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="{{ route('admin_dashboard') }}"><i class="fa fa-dashboard mr-1"></i> Dashboard</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white" href="{{ route('admin_routes_list') }}"><i class="fa fa-random mr-1"></i> Routes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('admin_users_list') }}"><i class="fa fa-users mr-1"></i> User</a>
                        </li>
                    </ul>
                    <ul class="nav flex-column mt-4">
                        <li class="nav-item ">
                            <a class="nav-link text-muted disabled" href="#">URLs</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white" href="{{ route('admin_urls_list') }}"><i class="fa fa-link mr-1"></i> ShortURLs</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white" href="{{ route('admin_urls_whitelist_list') }}"><i class="fa fa-list mr-1"></i> Whitelist</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white" href="{{ route('admin_urls_whitelist_add_form') }}"><i class="fa fa-plus-circle mr-1"></i> Add Whitelist</a>
                        </li>
                    </ul>
                    <ul class="nav flex-column mt-4">
                        <li class="nav-item ">
                            <a class="nav-link text-muted disabled" href="#">Starmap</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white" href="{{ route('admin_starmap_systems_list') }}"><i class="fa fa-circle-o-notch mr-1"></i> Systems</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white" href="{{ route('admin_starmap_systems_add_form') }}"><i class="fa fa-plus-circle mr-1"></i> Add System</a>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-10 mt-5">
                    <h1 class="my-4 text-center">@yield('title')</h1>
                    @yield('content')
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.js" integrity="sha256-jVfFb7AbGi7S/SLNl8SB4/MYaf549eEs+NlIWMoARHg=" crossorigin="anonymous"></script>
        <script src="{{ URL::asset('/js/app.js') }}"></script>
        @yield('scripts')
    </body>
</html>
