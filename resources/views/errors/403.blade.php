@extends('errors.layouts.default')

@section('title', '403 - Forbidden')

@section('content')
    @unless(empty($exception->getMessage()))
        ✋ {{ $exception->getMessage() }}
    @else
        ✋ __LOC__Forbidden
    @endunless
@endsection