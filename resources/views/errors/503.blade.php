@extends('errors.layouts.default')

@section('title', '503 - ' . __('Wartungsarbeiten'))

@section('top', 503)

@section('content')
    @lang('Wartungsarbeiten').
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/503.jpg') }}');
    }
@endsection