@extends('layouts.default')

@include('api.layouts.config')

@section('P__content')
    <div class="col-12 col-lg-10 col-xl-6 mt-3 mt-lg-5 mb-3 mx-auto">
        @yield('content')
    </div>
@endsection