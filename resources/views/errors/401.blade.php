@extends('errors.layouts.default')

@section('title', '401 - Unauthorized')

@section('content')
    @unless(empty($exception->getMessage()))
        🚨 {{ $exception->getMessage() }}
    @else
        🚨 __LOC__Unauthorized
    @endunless
@endsection