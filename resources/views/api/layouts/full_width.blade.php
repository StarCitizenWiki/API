@extends('layouts.full_width')

@section('body--class', 'bg-dark')
@section('topNav--class', 'bg-blue-grey')
@section('topNav__titleLink', route('api_index'))

@include('api.layouts.config')

@section('P__content')
    <div class="row">
        <div class="col-12 col-sm-8 col-md-5 col-xl-3 col-xxl-2 mx-auto mt-5">
            @yield('content')
        </div>
    </div>
@endsection