<html>
    <head>
        <title>@yield('title')</title>
        @if ($settings['bootstrap-css'])
            <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
        @endif
    </head>
    <body>
        @yield('content')

        @if ($settings['bootstrap-js'])
            <script src="{{ URL::asset('/js/app.js') }}"></script>
        @endif

        @yield('scripts')
    </body>
</html>