@extends('errors.layouts.default')

@section('title', '400 - Bad Request')

@section('content')
    @unless(empty($exception->getMessage()) && config('app.debug') === true)
        😒 {{ $exception->getMessage() }}
    @else
    @endunless
    😒 @lang('Bad Request')
@endsection