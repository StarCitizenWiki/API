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
            color: #dee2e6;
            display: table;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
        }

        .container {
            text-align: center;
            margin-top: 6rem;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .card {
            margin-top: 3rem;
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-header {
            padding: 0.75rem 2.25rem;
            margin-bottom: 0;
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }

        h4 {
            font-size: 1.5rem;
            margin-top: 0;
        }

        .btn {
            cursor: pointer;
            display: block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0;
            -webkit-transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
            text-decoration: none;
        }

        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <img src="/media/images/Star_Citizen_Wiki_Logo_White.png" style="max-width: 120px;">
            <div class="card">
                <h4 class="card-header">@yield('content')</h4>
                <div class="card-body">
                    @yield('debug')
                    <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
