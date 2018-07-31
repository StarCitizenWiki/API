@extends('errors.layouts.default')

@section('title', 'Server Error.')

@section('content')
    @unless(empty($exception->getMessage()) && config('app.debug') === true)
        😱 {{ $exception->getMessage() }}
    @else
        😰 @lang('This should not have happened.')<br><a href="mailto:{{ config('mail.from.address') }}">Contact us</a>
    @endunless
@endsection