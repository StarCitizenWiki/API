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
                    @lang('Die Star Citizen Wiki API dient als Schnittstelle zwischen dem Wiki und diversen anderen Datenquellen.')
                </p>
                <p>
                    @lang('Du hast Interesse an Programmierung und Webdesign?')
                    <br>
                    @lang('Wir suchen immer engagierte Leute für unser Projekt.')
                </p>
                <p>
                    <a href="mailto:info@star-citizen.wiki" class="font-italic">@lang('Schreib')</a>
                    @lang('uns, oder besuch uns auf unserem')
                    <a href="https://discord.gg/M9TT8kzXNe" class="font-italic">Discord-Server</a>!
                </p>
            </div>
        </div>
    </div>
@endsection