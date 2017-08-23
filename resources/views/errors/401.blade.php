@extends('errors.layouts.default')

@section('title', '401 - Unauthorized')

@section('content')
    @unless(empty($exception->getMessage()))
        🚨 {{ $exception->getMessage() }}
    @else
        🚨 @lang('Unauthorized')
    @endunless
@endsection