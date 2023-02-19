@extends('errors.layouts.default')

@section('title', '503 - Gleich wieder da')

@section('top')
    @lang('Wartungsarbeiten')
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/503.jpg') }}');
    }
@endsection