@extends('layouts.full_width')

@include('admin.layouts.config')

@section('P__content')
    <div class="row">
        <div class="col-12 col-sm-8 col-md-5 col-xl-3 col-xxl-2 mx-auto mt-5">
            @yield('content')
        </div>
    </div>
@endsection