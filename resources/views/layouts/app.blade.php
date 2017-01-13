<html>
    <head>
        <title>@yield('title')</title>
        @if ($bootstrapModules['enableCSS'])
            <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
        @endif
    </head>
    <body>
        @yield('content')

        @if ($bootstrapModules['enableJS'])
            <script src="{{ URL::asset('/js/app.js') }}"></script>
        @endif

        @yield('scripts')
    </body>
</html>