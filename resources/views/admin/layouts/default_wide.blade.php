@extends('layouts.default')

@include('admin.layouts.config')

@section('P__content')
    <div class="col-12 mt-3">
        @yield('content')
    </div>
@endsection