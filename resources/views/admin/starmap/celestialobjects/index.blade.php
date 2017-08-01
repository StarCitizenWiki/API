@extends('layouts.admin')
@section('title')
    @lang('admin/starmap/celestialobjects/index.header')
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
                    {{ count($celestialobjects) }}
                @endslot
                @lang('admin/starmap/celestialobjects/index.celestialobjects')
            @endcomponent
        </div>
        <div class="col-12 col-md-4">
            @component('admin.components.card')
                @slot('icon')
                    download
                @endslot
                @slot('content')
                    {{ \App\Models\CelestialObject::where('exclude', false)->max('created_at') }}
                @endslot
                @lang('admin/starmap/celestialobjects/index.last_download')
            @endcomponent
        </div>
        <div class="col-12 col-md-4">
            @component('admin.components.card')
                @slot('icon')
                    times
                @endslot
                @slot('content')
                    {{ count(\App\Models\CelestialObject::where('exclude', true)->get()) }}
                @endslot
                @lang('admin/starmap/celestialobjects/index.excluded')
            @endcomponent
        </div>


        <table class="table">
            <thead>
            <tr>
                <th>@lang('admin/starmap/celestialobjects/index.id')</th>
                <th>@lang('admin/starmap/celestialobjects/index.code')</th>
                <th>@lang('admin/starmap/celestialobjects/index.state')</th>
                <th>@lang('admin/starmap/celestialobjects/index.last_download')</th>
                <th>@lang('admin/starmap/celestialobjects/index.name')</th>
                <th>@lang('admin/starmap/celestialobjects/index.type')</th>
                <th>@lang('admin/starmap/celestialobjects/index.designation')</th>
                <th>@lang('admin/starmap/celestialobjects/index.affiliation')</th>
                <th>@lang('admin/starmap/celestialobjects/index.description')</th>
                <th>@lang('admin/starmap/celestialobjects/index.cig_time_modified')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($celestialobjects as $celestialobject)
            <tr>
                <td>{{ $celestialobject->id }}</td>
                <td>{{ $celestialobject->code }}</td>
                <td>

                    @if($celestialobject->isExcluded())
                    <span class="badge badge-default">
                                @lang('admin/starmap/celestialobjects/index.excluded')
                    </span>
                    @elseif(\Illuminate\Support\Facades\Session::has('success'))
                    <span class="badge badge-info">
                                @lang('admin/starmap/celestialobjects/index.downloading')
                    </span>
                    @else
                    <span class="badge badge-warning">
                                @lang('admin/starmap/celestialobjects/index.downloaded')
                    </span>
                    @endif
                </td>
                <td>{{ $celestialobject->created_at }}</td>
                <td>{{ $celestialobject->name }}</td>
                <td>{{ $celestialobject->type }}</td>
                <td>{{ $celestialobject->designation }}</td>
                <td>{{ $celestialobject->affiliation_code }}</td>
                <td>{{ $celestialobject->description }}</td>
                <td>{{ $celestialobject->cig_time_modified }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    @include('components.init_dataTables')
@endsection
