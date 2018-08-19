@extends('errors.layouts.default')

@section('title', 'Server Error.')

@section('content')
    😰 @lang('This should not have happened.')
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