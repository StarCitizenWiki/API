@extends('errors.layouts.default')

@section('title', '404 - Not Found')

@section('content')
    @unless(empty($exception->getMessage()) && config('app.debug') === true)
        😰 {{ $exception->getMessage() }}
    @else
        😰 @lang('Not Found')
    @endunless
@endsection