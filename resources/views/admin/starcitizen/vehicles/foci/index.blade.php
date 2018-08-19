@extends('admin.layouts.default')

@section('title', __('Fahrzeugfokusse'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Fahrzeugfokusse')</h4>
        <div class="card-body px-0 table-responsive">
            @include('admin.components.translation_table')
        </div>
    </div>
@endsection