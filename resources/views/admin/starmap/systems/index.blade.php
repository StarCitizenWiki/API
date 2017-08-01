@extends('layouts.admin')
@section('title')
    @lang('admin/starmap/systems/index.header')
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
    <div class="mt-5 col-12 col-md-8 mx-auto mb-5">
        <div class="row mb-5">
            <div class="col-12 col-md-4">
                @component('admin.components.card')
                    @slot('icon')
                        circle-o-notch
                    @endslot
                    @slot('content')
                        {{ count($systems) }}
                    @endslot
                        @lang('admin/starmap/systems/index.systems')
                @endcomponent
            </div>
            <div class="col-12 col-md-4">
                @component('admin.components.card')
                    @slot('icon')
                        download
                    @endslot
                    @slot('content')
                        {{ \App\Models\Starsystem::where('exclude', false)->max('created_at') }}
                    @endslot
                        @lang('admin/starmap/systems/index.last_download')
                @endcomponent
            </div>
            <div class="col-12 col-md-4">
                @component('admin.components.card')
                    @slot('icon')
                        times
                    @endslot
                    @slot('content')
                        {{ count(\App\Models\Starsystem::where('exclude', true)->get()) }}
                    @endslot
                        @lang('admin/starmap/systems/index.excluded')
                @endcomponent
            </div>
        </div>


        <table class="table">
            <thead>
                <tr>
                    <th>@lang('admin/starmap/systems/index.id')</th>
                    <th>@lang('admin/starmap/systems/index.code')</th>
                    <th>@lang('admin/starmap/systems/index.last_download')</th>
                    <th>@lang('admin/starmap/systems/index.name')</th>
                    <th>@lang('admin/starmap/systems/index.type')</th>
                    <th>@lang('admin/starmap/systems/index.affiliation')</th>
                    <th>@lang('admin/starmap/systems/index.description')</th>
                    <th>@lang('admin/starmap/systems/index.cig_time_modified')</th>
                </tr>
            </thead>
            <tbody>
            @foreach($systems as $system)
                <tr>
                    <td>{{ $system->id }}</td>
                    <td>{{ $system->code }}</td>
                    <td>{{ $system->created_at }}</td>
                    <td>{{ $system->name }}</td>
                    <td>{{ $system->type }}</td>
                    <td>{{ $system->affiliation_code }}</td>
                    <td>{{ $system->description }}</td>
                    <td>{{ $system->cig_time_modified }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    @include('components.init_dataTables')
@endsection