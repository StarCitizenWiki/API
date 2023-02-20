@extends('errors.layouts.default')

@section('title', '403 - Verboten')

@section('top', 403)

@section('content')
    @lang('Verboten')
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/403.jpg') }}');
    }
@endsection

@section('debug')
@if(isset($exception))
Message: {{ $exception->getMessage() }}<br>
Stack: <br>
{!! $exception->getTraceAsString() !!}
@endif
@endsection