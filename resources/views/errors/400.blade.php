@extends('errors.layouts.default')

@section('title', '400 - Bad Request')

@section('content')
    @unless(empty($exception->getMessage()))
        😒 {{ $exception->getMessage() }}
    @else
        😒 @lang('Bad Request')
    @endunless
@endsection