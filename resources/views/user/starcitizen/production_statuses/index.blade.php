@extends('user.layouts.default')

@section('title', __('Produktionsstatus'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Produktionsstatus')</h4>
        <div class="card-body px-0 table-responsive">
            @include('user.components.translation_table')
        </div>
    </div>
@endsection