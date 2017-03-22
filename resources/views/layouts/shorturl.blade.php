<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('/media/images/rsi_im/favicon.ico') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>RSI.im - Star Citizen Wiki Short URL Service</title>
        <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
        <link rel="stylesheet" href="{{ URL::asset('/css/rsi_im/app.css') }}">
    </head>
    <body>
        @yield('content')

        @yield('scripts')
        <script>window.Tether = function () {};</script>
        <script src="{{ URL::asset('/js/app.js') }}"></script>
    </body>
</html>