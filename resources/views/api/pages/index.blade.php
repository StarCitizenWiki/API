@extends('api.layouts.default')

{{-- Page Title --}}
@section('title', __('Startseite'))

{{-- Page Content --}}
@section('content')
    <div class="mb-3 mb-lg-5">
        @foreach($notifications as $notification)
            <div class="alert alert-{{ $notification->getBootstrapClass() }}">
                <span class="mr-1">{{ $notification->created_at->format('d.m.Y H:i') }}</span>
                &mdash;
                <span class="ml-1">{{ $notification->content }}</span>
            </div>
        @endforeach
    </div>

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
                    <a href="ts3server://ts.star-citizen.wiki" class="font-italic">Teamspeak-Server</a>!
                </p>
            </div>
        </div>
    </div>
@endsection