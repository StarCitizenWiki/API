@extends('shorturl.layouts.app')

@section('body__content')
<main class="container" id="form">
    <div class="row justify-content-center" style="height: 100vh">
        <div class="col-10 col-md-6 align-self-center d-flex form-container">
            <div class="w-100">
                <img src="{{ URL::asset('/media/images/rsi_im/logo.png') }}" class="img-responsive mb-5">
                @include('components.errors')
                @if (session('hash_name'))
                    <div class="alert alert-success text-center">
                        {{config('app.shorturl_url')}}/{{ session('hash_name') }}
                    </div>
                @endif
                <form id="shorten-form" class="w-100" role="form" method="POST" action="{{ route('short_url_create_redirect') }}">
                    {{ csrf_field() }}
                    <div class="input-group input-group-lg mb-2">
                        <input type="url" name="url" id="url" class="form-control" placeholder="@lang('shorturl/index.long_url')" required value="{{ old('url') }}">
                        <span class="input-group-btn">
                            <button class="btn btn-info" type="submit">
                                @lang('shorturl/index.shorten')
                            </button>
                        </span>
                        <span class="input-group-btn">
                            <button class="btn btn-secondary" type="button" data-toggle="collapse" href="#customize" aria-expanded="false" aria-controls="customize"><i class="fa fa-cog"></i></button>
                        </span>
                    </div>
                    <div class="collapse" id="customize">
                        <div class="input-group mt-3">
                            <span class="input-group-addon" id="hash_name-label">@lang('shorturl/index.custom_name'):</span>
                            <input type="text" class="form-control" id="hash_name" name="hash_name" aria-describedby="hash_name-label" placeholder="@lang('shorturl/index.custom_name_placeholder')" value="{{ old('hash_name') }}">
                        </div>
                        <div class="input-group mt-3">
                            <span class="input-group-addon" id="expires-label">@lang('shorturl/index.expires'):</span>
                            <input type="datetime-local" class="form-control" id="expires" name="expires" aria-describedby="expires-label" style="flex-direction: inherit;" value="{{ old('expires') }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 d-flex fixed-bottom">
            <ul class="nav justify-content-end w-100">
                <li class="nav-item">
                    <a class="nav-link text-info" href="{{ route('short_url_resolve_form') }}">@lang('shorturl/index.resolve')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info" href="#whitelist-modal" data-toggle="modal" data-target="#whitelist-modal">@lang('shorturl/index.whitelist')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info" href="{{ config('app.api_url') }}">@lang('shorturl/index.api')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info" href="https://star-citizen.wiki/Star_Citizen_Wiki:Impressum">@lang('shorturl/index.legal')</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="modal fade" id="whitelist-modal" tabindex="-1" role="dialog" aria-labelledby="whitelist-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="whitelist-modal-label">URL Whitelist</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul>
                        @foreach($whitelistedURLs as $whitelistedURL)
                            <li>{{ $whitelistedURL->url }}</li>
                        @endforeach
                    </ul>
                    <hr>
                    <a href="mailto:api@star-citizen.wiki?subject=RSI.IM URL Whitelist Request&body=Whitelist Request for the following Domain(s):">@lang('shorturl/index.add_whitelist_request')</a>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection