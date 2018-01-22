@extends('layouts.admin')
@section('title')
    @lang('admin/starmap/jumppointtunnels/index.header')
@endsection
@section('header')
    <style>
        .display-5 {
            font-size: 2.5rem;
        }

        .date {
            white-space: nowrap;
        }

        .stack {
            font-size: 0.85em;
        }

        .date {
            min-width: 75px;
        }

        .text {
            word-break: break-all;
        }

        a.llv-active {
            z-index: 2;
            background-color: #f5f5f5;
            border-color: #777;
        }
    </style>
@endsection

@section('content')
    <div class="row mb-5">
        <div class="col-12 col-md-4">
            @component('admin.components.card')
                @slot('icon')
                    circle-o-notch
                @endslot
                @slot('content')
                    {{ count($jumppointtunnels) }}
                @endslot
                @lang('admin/starmap/jumppointtunnels/index.jumppointtunnels')
            @endcomponent
        </div>
        <div class="col-12 col-md-4">
            @component('admin.components.card')
                @slot('icon')
                    download
                @endslot
                @slot('content')
                    {{ \App\Models\Jumppoint::where('exclude', false)->max('created_at') }}
                @endslot
                @lang('admin/starmap/jumppointtunnels/index.last_download')
            @endcomponent
        </div>
        <div class="col-12 col-md-4">
            @component('admin.components.card')
                @slot('icon')
                    times
                @endslot
                @slot('content')
                    {{ count(\App\Models\Jumppoint::where('exclude', true)->get()) }}
                @endslot
                @lang('admin/starmap/jumppointtunnels/index.excluded')
            @endcomponent
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>@lang('admin/starmap/jumppointtunnels/index.id')</th>
                <th>@lang('admin/starmap/jumppointtunnels/index.created')</th>
                <th>@lang('admin/starmap/jumppointtunnels/index.size')</th>
                <th>@lang('admin/starmap/jumppointtunnels/index.entry_code')</th>
                <th>@lang('admin/starmap/jumppointtunnels/index.exit_code')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($jumppointtunnels as $jumppointtunnel)
                <tr>
                    <td>{{ $jumppointtunnel->id }}</td>
                    <td>{{ $jumppointtunnel->created_at }}</td>
                    <td>{{ $jumppointtunnel->size }}</td>
                    <td>{{ $jumppointtunnel->entry_code }}</td>
                    <td>{{ $jumppointtunnel->exit_code }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
@endsection

@section('scripts')
    @include('components.init_dataTables')
@endsection
