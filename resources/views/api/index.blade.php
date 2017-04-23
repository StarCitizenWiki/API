@extends('layouts.app')
@section('title')
    @lang('api/index.header')
@endsection

@section('content')
    @include('layouts.heading')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mt-3">
                <div class="col-12 col-md-4 mx-auto">
                    <div class="alert alert-danger">
                        @lang('api/index.notice')
                    </div>
                    @include('components.errors')
                </div>
                @if (Auth::guest())
                    <form role="form" method="POST" action="{{ route('auth_register') }}">
                        {{ csrf_field() }}
                        <div class="input-group input-group-lg mx-auto col-10 col-lg-6 col-xl-4">
                            <input id="email" type="email" class="center-block form-control input-lg" name="email" value="{{ old('email') }}" required placeholder="@lang('api/index.email')">
                            <span class="input-group-btn">
                                <button class="btn btn-lg btn-primary" type="submit">@lang('api/index.request_api_key')</button>
                            </span>
                        </div>
                    </form>
                @else
                    <p class="text-center">
                        <b>@lang('api/index.api_key'):</b>&nbsp;<code>{{ Auth::user()->api_token }}</code>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4 mt-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4><i class="fa fa-book"></i> @lang('api/index.documentation')</h4></div>
                    <div class="panel-body mt-3 mr-5">
                        <ul class="list-unstyled">
                            <li>
                                <i class="fa fa-question-circle"></i>
                                <span class="ml-2">
                                    <a href="{{ route('api_faq') }}" class="text-gray-dark">
                                        @lang('api/index.faq')
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fa fa-cloud"></i>
                                <span class="ml-2">
                                    <a href="" class="text-gray-dark">
                                        @lang('api/index.rsi_api')
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fa fa-rocket"></i>
                                <span class="ml-2">
                                    <a href="" class="text-gray-dark">
                                        @lang('api/index.wiki_api')
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fa fa-link"></i>
                                <span class="ml-2">
                                    <a href="" class="text-gray-dark">
                                        @lang('api/index.url_api')
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fa fa-image"></i>
                                <span class="ml-2">
                                    <a href="" class="text-gray-dark">
                                        @lang('api/index.media_api')
                                    </a>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4><i class="fa fa-pencil"></i> @lang('api/index.the_project')</h4></div>
                    <div class="panel-body mt-3 mr-5">
                        @lang('api/index.about')
                        <a href="mailto:info@star-citizen.wiki" class="text-gray-dark font-italic">@lang('api/index.write')</a> @lang('api/index.about_2')
                        <a href="ts3server://ts.star-citizen.wiki" class="text-gray-dark font-italic">@lang('api/index.teamspeak_server')</a>!
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4><i class="fa fa-star"></i> @lang('api/index.follow_us')</h4></div>
                    <div class="panel-body mt-3 mr-5">
                        <ul class="list-unstyled">
                            <li>
                                <i class="fa fa-globe"></i>
                                <span class="ml-2">
                                    <a href="https://star-citizen.wiki/" class="text-gray-dark">star-citizen.wiki</a>
                                </span>
                            </li>
                            <li>
                                <i class="fa fa-twitter"></i>
                                <span class="ml-2">
                                    <a href="https://twitter.com/SC_Wiki" class="text-gray-dark">
                                        SC_Wiki
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fa fa-facebook-square"></i>
                                <span class="ml-2">
                                    <a href="https://facebook.com/StarCitizenWiki" class="text-gray-dark">
                                        StarCitizenWiki
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fa fa-building-o"></i>
                                <span class="ml-2">
                                    <a href="https://robertsspaceindustries.com/orgs/WIKI" class="text-gray-dark">
                                        WIKI
                                    </a>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-5 mt-5">
                <p class="pull-right mt-5"><a href="https://star-citizen.wiki">Star Citizen Wiki</a> &dash; <a href="mailto:api@star-citizen.wiki"><i class="fa fa-envelope-o"></i></a> &dash; &copy; {{ date("Y") }}</p>
            </div>
        </div>
    </div>


    <div class="container mt-2">

    </div>
@endsection
