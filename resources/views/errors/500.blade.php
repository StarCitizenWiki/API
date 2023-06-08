@extends('errors.layouts.default')

@section('title', '500 - ' . __('Server Error'))

@section('top', 500)

@section('content')
    @lang('Das hätte nicht passieren dürfen').
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/500.jpg') }}');
    }
@endsection

@section('debug')
    @if(isset($exception))
        Message: {{ $exception->getMessage() }}<br>
        Stack: <br>
        {!! $exception->getTraceAsString() !!}
    @endif
@endsection