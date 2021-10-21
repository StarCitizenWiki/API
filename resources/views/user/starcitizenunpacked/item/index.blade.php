@extends('user.layouts.default_wide')

@section('title', __('Star Citizen Items'))

@section('head__content')
    @parent
    <style>
        span ul {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div id="item-live-search"><item-live-search api-token="{{ $apiToken ?? '' }}"></item-live-search></div>
    </div>
@endsection
