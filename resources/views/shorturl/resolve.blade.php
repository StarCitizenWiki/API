@extends('shorturl.layouts.app')

@section('body__content')
    <main class="container" id="form">
        <div class="row justify-content-center" style="height: 100vh">
            <div class="col-10 col-md-6 align-self-center d-flex form-container">
                <div class="w-100">
                    <img src="{{ URL::asset('/media/images/rsi_im/logo.png') }}" class="img-responsive mb-5">
                    @include('components.errors')

                    <form id="shorten-form" class="w-100" role="form" method="POST" action="{{ route('shorturl.web.resolve') }}">
                        {{ csrf_field() }}
                        <div class="input-group input-group-lg mb-2">
                            <input type="url" name="url" id="url" class="form-control @if (session('url')) text-success @endif" placeholder="@lang('gekürzte Url oder Name')" required value="{{ old('url') }}">
                            <span class="input-group-btn">
                                <button class="btn btn-info" type="submit">
                                    @lang('Auflösen')
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-12 d-flex fixed-bottom">
                <ul class="nav justify-content-end w-100">
                    <li class="nav-item">
                        <a class="nav-link text-info" href="{{ route('shorturl.index') }}">
                            @lang('Verkürzen')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-info" href="https://{{ config('app.api_url') }}">
                            @lang('Api')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-info" href="https://star-citizen.wiki/Star_Citizen_Wiki:Impressum">
                            @lang('Impressum')
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </main>
@endsection