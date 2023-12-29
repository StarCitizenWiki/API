@extends('web.layouts.default')

@section('title', __('Produktionsnotizen'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Produktionsnotizen')</h4>
        <div class="card-body px-0 table-responsive">
            @include('components.errors')
            @include('components.messages')
            @include('components.translation_table')
        </div>
    </div>
@endsection