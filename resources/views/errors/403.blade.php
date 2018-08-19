@extends('errors.layouts.default')

@section('title', '403 - Forbidden')

@section('content')
    ✋ @lang('Forbidden')
@endsection

@section('debug')
    @if(config('app.debug') === true)
        <pre style="margin: 1rem 0; text-align: left">
Message: {{ $exception->getMessage() }}
            <br>
Stack: <br>
{!! $exception->getTraceAsString() !!}
        </pre>
    @endif
@endsection