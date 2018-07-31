@extends('errors.layouts.default')

@section('title', '401 - Unauthorized')

@section('content')
    @unless(empty($exception->getMessage()) && config('app.debug') === true)
        🚨 {{ $exception->getMessage() }}
    @else
    @endunless
    🚨 @lang('Unauthorized')
@endsection