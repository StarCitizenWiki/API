@extends('web.layouts.default')

@section('title', __('Fahrzeuggrößen'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Fahrzeuggrößen')</h4>
        <div class="card-body px-0 table-responsive">
            @include('components.errors')
            @include('components.messages')
            @include('components.translation_table')
        </div>
    </div>
@endsection