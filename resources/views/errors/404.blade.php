@extends('errors.layouts.default')

@section('title', '404 - Not Found')

@section('content')
    @unless(empty($exception->getMessage()))
        😰 {{ $exception->getMessage() }}
    @else
        😰 __LOC__Not Found
    @endunless
@endsection