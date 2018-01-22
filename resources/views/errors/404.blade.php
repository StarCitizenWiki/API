@extends('errors.layouts.default')

@section('title', '404 - Not Found')

@section('content')
    @unless(empty($exception->getMessage()))
        😰 {{ $exception->getMessage() }}
    @else
        😰 @lang('Not Found')
    @endunless
@endsection