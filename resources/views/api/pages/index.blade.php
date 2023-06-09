@extends('api.layouts.default')

{{-- Page Title --}}
@section('title', __('Startseite'))

{{-- Page Content --}}
@section('content')
    <div class="card">
        <img src="{{ asset('media/images/api_index.jpg') }}" class="card-img-top">
        <div class="card-body">
            <h4 class="card-title">@lang('Das Projekt')</h4>
            <div class="card-text">
                <p>
                    @lang('Die Star Citizen Wiki API dient als Schnittstelle zwischen dem')
                        <a href="https://star-citizen.wiki" class="font-italic">@lang('Wiki')</a>
                    @lang('und diversen anderen Datenquellen.')
                </p>
                <p>
                    @lang('Du hast Interesse an Programmierung?')
                    <br>
                    @lang('Wir suchen immer engagierte Leute für unser Projekt.')
                </p>
                <p>
                    <a href="mailto:info@star-citizen.wiki" class="font-italic">@lang('Schreib')</a>
                        @lang('uns, oder besuch uns auf unserem')
                    <a href="discord.star-citizen.wiki" class="font-italic">@lang('Discord-Server')</a>!
                </p>
            </div>
        </div>
    </div>
@endsection