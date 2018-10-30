<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    <style>
        :root {
            --font-family-sans-serif: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
        }

        html, body {
            height: 100%;
            background-color: #343a40;
            font-family: sans-serif;
            line-height: 1.15;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            -ms-overflow-style: scrollbar;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #fff;
            display: table;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            background-attachment: fixed !important;
            background-size: cover !important;
        }

        .container {
            text-align: center;
            margin-top: 6rem;
            position: relative;
            z-index: 1;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        h1 {
            font-size: 5em;
            text-shadow: 0 0 20px #333;
            margin-bottom: 0;
            letter-spacing: 5px;
        }

        h2 {
            font-size: 3em;
            text-shadow: 0 0 20px #333;
            letter-spacing: 5px;
        }

        .debug {
            margin-top: 5rem;
            max-width: 70%;
            margin-left: auto;
            margin-right: auto;
            padding: 1rem;
            background: #333;
            margin-bottom: 5rem;
            text-align: left;
        }

        @yield('style')
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <img src="{{ asset('media/images/Star_Citizen_Wiki_Logo_White.png') }}" style="max-width: 120px;">
            <h1>@yield('top')</h1>
            <h2>@yield('content')</h2>

            @if(config('app.debug') === true)
            <div class="debug">
                @yield('debug')
            </div>
            @endif
        </div>
    </div>
</body>
</html>
