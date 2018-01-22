@extends('errors.layouts.default')

@section('title', '403 - Forbidden')

@section('content')
    @unless(empty($exception->getMessage()))
        ✋ {{ $exception->getMessage() }}
    @else
        ✋ @lang('Forbidden')
    @endunless
@endsection