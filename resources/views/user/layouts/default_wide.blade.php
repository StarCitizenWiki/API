@extends('layouts.default')

@include('user.layouts.config')

@section('P__content')
    <div class="col-12 my-3">
        @yield('content')
    </div>
@endsection