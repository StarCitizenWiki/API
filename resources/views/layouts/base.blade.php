<!DOCTYPE html>
<html class="@yield('html--class')" id="@yield('html--id')" @yield('html--options')>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('P__title')</title>

        @yield('head__content')
    </head>
    <body class="@yield('body--class')" id="@yield('body--id')" @yield('body--options')>
        @yield('body__pre')
        @yield('body__content')
        @yield('body__after')
        @if(config('services.plausible.enabled'))
            <script defer data-domain="{{parse_url(config('app.url'))['host']}}" src="{{config('services.plausible.domain')}}/js/plausible.js"></script>
        @endif
    </body>
</html>