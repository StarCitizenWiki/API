@extends('layouts.full_width')

@section('body--class', 'bg-dark')
@section('topNav--class', 'bg-blue-grey')
@section('topNav__titleLink', route('api_index'))

@include('api.layouts.config')

@section('P__content')
<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-5 col-xl-3 mt-5 mt-mb-0 mb-5">
        @yield('content')
    </div>
</div>
@endsection