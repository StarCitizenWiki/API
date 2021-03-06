@extends('user.layouts.default')

@section('title', __('Fahrzeugfokusse'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Fahrzeugfokusse')</h4>
        <div class="card-body px-0 table-responsive">
            @include('components.errors')
            @include('components.messages')
            @include('user.components.translation_table')
        </div>
    </div>
@endsection