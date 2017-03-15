<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('/media/images/rsi_im/favicon.ico') }}">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>RSI.im - Star Citizen Wiki Short URL Service</title>
        <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
        <link rel="stylesheet" href="{{ URL::asset('/css/rsi_im/app.css') }}">

        <!-- Scripts -->
        <script>
            window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
            ]) !!};
        </script>
    </head>
    <body>
        <main class="container" id="form">
            <div class="row justify-content-center" style="height: 100vh">
                <div class="col-10 col-md-6 align-self-center d-flex form-container">
                    <div class="w-100">
                        <img src="{{ URL::asset('/media/images/rsi_im/logo.png') }}" class="img-responsive mb-5">
                        @include('snippets.errors')
                        @if (session('hash_name'))
                            <div class="alert alert-success text-center mb-5">
                                https://{{SHORT_URL_DOMAIN}}/{{ session('hash_name') }}
                            </div>
                        @endif
                        <form id="shorten-form" class="w-100" role="form" method="POST" action="{{ route('shorten') }}">
                            {{ csrf_field() }}
                            <div class="input-group input-group-lg mb-2">
                                <input type="url" name="url" id="url" class="form-control" placeholder="Long URL" required>
                                <span class="input-group-btn">
                                    <button class="btn btn-info" type="submit">Shorten</button>
                                </span>
                                <span class="input-group-btn">
                                    <button class="btn btn-secondary" type="button" data-toggle="collapse" href="#customize" aria-expanded="false" aria-controls="customize"><i class="fa fa-cog"></i></button>
                                </span>
                            </div>
                            <div class="collapse mt-3" id="customize">
                                <div class="input-group">
                                    <span class="input-group-addon" id="hash_name-label">Custom Name:</span>
                                    <input type="text" class="form-control" id="hash_name" name="hash_name" aria-describedby="hash_name-label" placeholder="Alphanumeric and -_">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.js" integrity="sha256-jVfFb7AbGi7S/SLNl8SB4/MYaf549eEs+NlIWMoARHg=" crossorigin="anonymous"></script>
        <script src="{{ URL::asset('/js/app.js') }}"></script>
    </body>
</html>
