@extends('errors.layouts.default')

@section('title', 'Server Error.')

@section('content')
    @lang('😱 This should not have happened.')<br><a href="mailto:{{ config('mail.from.address') }}">Contact us</a>
@endsection