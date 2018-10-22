@extends('errors.layouts.default')

@section('title', '404 - Not Found')

@section('top', 404)

@section('content')
    @lang('You drift into the endless nothingness of Space&hellip;')
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/404.jpg') }}');
    }
@endsection

@section('debug')
@if(isset($exception))
Message: {{ $exception->getMessage() }}<br>
Stack: <br>
{!! $exception->getTraceAsString() !!}
@endif
@endsection