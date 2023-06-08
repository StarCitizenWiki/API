@extends('errors.layouts.default')

@section('title', '401 - ' . __('Unbefugter Zugriff'))

@section('top', 401)

@section('content')
    @lang('Unbefugter Zugriff')
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/401.jpg') }}');
    }
@endsection

@section('debug')
    @if(isset($exception))
        Message: {{ $exception->getMessage() }}<br>
        Stack: <br>
        {!! $exception->getTraceAsString() !!}
    @endif
@endsection