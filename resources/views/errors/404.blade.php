@extends('errors.layouts.default')

@section('title', '404 - ' . __('Nicht gefunden'))

@section('top', 404)

@section('content')
    @lang('Du driftest in das endlose Nichts des Weltraums...')
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