@extends('shorturl.layouts.app')

@section('body__content')
<main class="container" id="form">
    <div class="row justify-content-center" style="height: 100vh">
        <div class="col-10 col-md-6 align-self-center d-flex form-container">
            <div class="w-100">
                <img src="{{ URL::asset('/media/images/rsi_im/logo.png') }}" class="img-responsive mb-5">
                @include('components.errors')
                @if (session('hash'))
                    <div class="alert alert-success text-center">
                        {{config('app.shorturl_url')}}/{{ session('hash') }}
                    </div>
                @endif
                <form id="shorten-form" class="w-100" role="form" method="POST" action="{{ route('short_url_create_redirect') }}">
                    {{ csrf_field() }}
                    <div class="input-group input-group-lg mb-2">
                        <input type="url" name="url" id="url" class="form-control" placeholder="@lang('Lange Url')" required value="{{ old('url') }}">
                        <span class="input-group-btn">
                            <button class="btn btn-info" type="submit">
                                @lang('shorturl/index.shorten')
                            </button>
                        </span>
                        <span class="input-group-btn">
                            <button class="btn btn-secondary" type="button" data-toggle="collapse" href="#customize" aria-expanded="false" aria-controls="customize"><i class="far fa-cog"></i></button>
                        </span>
                    </div>
                    <div class="collapse" id="customize">
                        <div class="input-group mt-3">
                            <span class="input-group-addon" id="hash-label">@lang('Name (Optional)'):</span>
                            <input type="text" class="form-control" id="hash" name="hash" aria-describedby="hash-label" placeholder="@lang('A-Za-z_- erlaubt')" value="{{ old('hash') }}">
                        </div>
                        <div class="input-group mt-3">
                            <span class="input-group-addon" id="expired_at-label">@lang('Ablaufdatum'):</span>
                            <input type="datetime-local" class="form-control" id="expired_at" name="expired_at" aria-describedby="expired_at-label" style="flex-direction: inherit;" value="{{ old('expired_at') }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 d-flex fixed-bottom">
            <ul class="nav justify-content-end w-100">
                <li class="nav-item">
                    <a class="nav-link text-info" href="{{ route('short_url_resolve_form') }}">@lang('Url auflösen')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info" href="#whitelist-modal" data-toggle="modal" data-target="#whitelist-modal">@lang('Erlaubte Domains')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info" href="{{ config('app.api_url') }}">@lang('Api')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info" href="https://star-citizen.wiki/Star_Citizen_Wiki:Impressum">@lang('Impressum')</a>
                </li>
            </ul>
        </div>
    </div>
    @include('shorturl.components.whitelisted_domains_modal')
</main>
@endsection