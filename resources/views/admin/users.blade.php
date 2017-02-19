@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Users')
@section('lead', 'Users')

@section('content')
    @include('layouts.heading');
    <div class="container-fluid">
        <div class="row">
            <div class="col-10 offset-2 mt-5">
                @if(count($users) > 0)
                    <div class="text-left">
                        <ul class="list-unstyled">
                            <li>
                                <div class="row mb-1">
                                    <span class="col-1">ID</span>
                                    <span class="col-5">API Key</span>
                                    <span class="col-2">E-Mail</span>
                                    <span class="col-1">Whitelisted</span>
                                    <span class="col-1">Blacklisted</span>
                                    <span class="col-1">RPM</span>
                                </div>
                            </li>
                        @foreach($users as $user)
                            <li>
                                <div class="row mb-1">
                                    <span class="col-1">{{ $user->id }}</span>
                                    <span class="col-5">{{ $user->api_token }}</span>
                                    <span class="col-2">{{ $user->email }}</span>
                                    <span class="col-1"><i class="fa {{ $user->whitelisted?'fa-check':'fa-times' }}"></i> </span>
                                    <span class="col-1"><i class="fa {{ $user->blacklisted?'fa-check':'fa-times' }}"></i> </span>
                                    <span class="col-1">{{ $user->requests_per_minute }}</span>
                                </div>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                @else
                    Keine Benutzer vohanden
                @endunless
            </div>
        </div>
    </div>
@endsection


