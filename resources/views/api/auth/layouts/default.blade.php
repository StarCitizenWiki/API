@extends('api.layouts.default')

@section('sidebar__content')
    @include('api.auth.menu.main')
@endsection

@section('topNav__content')
    @include('api.menu.login_logout')
    <div class="nav d-flex d-md-none flex-column flex-sm-row">
        @include('api.auth.menu.main')
    </div>
@endsection

@section('P__content')
    <div class="col-12 col-md-12 col-lg-10 col-xl-5 mx-auto mt-3 mt-lg-5 mb-3">
        @yield('content')
    </div>
@endsection