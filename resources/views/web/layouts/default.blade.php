@extends('layouts.default')

@include('web.layouts.config')

@section('P__content')
    <div class="col-12 col-md-12 col-lg-10 mx-auto mt-3 mt-lg-5">
        @yield('content')
    </div>
@endsection