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
        <img src="https://cdn.star-citizen.wiki/images/thumb/f/f1/BANU_Banu_Merchantman_auf_Landeplattform_Mya_Markt.jpg/800px-BANU_Banu_Merchantman_auf_Landeplattform_Mya_Markt.jpg" class="card-img-top">
        <div class="card-body">
            <h4 class="card-title">@lang('Das Projekt')</h4>
            <div class="card-text">
                @lang('api/index.about')

                <a href="mailto:info@star-citizen.wiki" class="text-italic">@lang('api/index.write')</a>
                @lang('api/index.about_2')
                <a href="ts3server://ts.star-citizen.wiki" class="text-italic">@lang('api/index.teamspeak_server')</a>
            </div>
            @if (Auth::guest())
                <a href="{{ route('web.user.auth.register_form') }}" class="mt-4 btn btn-outline-primary">@lang('Registrieren')</a>
            @endif
        </div>
    </div>
@endsection