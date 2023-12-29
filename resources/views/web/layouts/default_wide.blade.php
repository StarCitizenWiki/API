@extends('layouts.default')

@include('web.layouts.config')

@section('P__content')
    <div class="col-12 my-3">
        @yield('content')
    </div>
@endsection