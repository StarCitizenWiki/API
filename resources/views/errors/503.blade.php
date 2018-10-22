@extends('errors.layouts.default')

@section('title', 'Be right back.')

@section('top')
    @lang('Wartungsarbeiten')
@endsection

@section('style')
    body {
        background: url('{{ asset('media/images/errors/503.jpg') }}');
    }
@endsection