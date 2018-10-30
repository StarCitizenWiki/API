@extends('errors.layouts.default')

@section('title', '400 - Bad Request')

@section('top', 400)

@section('content')
    @lang('Bad Request')
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/400.jpg') }}');
    }
@endsection

@section('debug')
@if(isset($exception))
Message: {{ $exception->getMessage() }}<br>
Stack: <br>
{!! $exception->getTraceAsString() !!}
@endif
@endsection