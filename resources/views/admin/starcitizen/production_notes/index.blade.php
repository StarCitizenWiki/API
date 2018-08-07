@extends('admin.layouts.default')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Produktionsnotizen')</h4>
        <div class="card-body px-0 table-responsive">
            @include('admin.components.translation_table')
        </div>
    </div>
@endsection