@extends('layouts.admin')
@section('title', 'Starmap Systems')
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
    <div class="mt-5 col-12 col-md-8 mx-auto">
        <div class="row mb-5">
            <div class="col-12 col-md-4">
                @component('admin.components.card')
                    @slot('icon')
                        circle-o-notch
                    @endslot
                    @slot('content')
                        {{ count($systems) }}
                    @endslot
                    Systeme
                @endcomponent
            </div>
            <div class="col-12 col-md-4">
                @component('admin.components.card')
                    @slot('icon')
                        download
                    @endslot
                    @slot('content')
                        @if(\Illuminate\Support\Facades\Storage::disk('starmap')->exists(\App\Models\Starsystem::makeFilenameFromCode('SOL')))
                            {{ \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\Storage::disk('starmap')->lastModified(\App\Models\Starsystem::makeFilenameFromCode('SOL')))->format('d.m.Y H:i:s') }}
                        @else
                            -
                        @endif
                    @endslot
                    Last Download
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
                    Excluded
                @endcomponent
            </div>
        </div>


        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Last Download</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($systems as $system)
                <tr>
                    <td>{{ $system->id }}</td>
                    <td>{{ $system->code }}</td>
                    <td>

                        @if($system->isExcluded())
                        <span class="badge badge-default">Excluded</span>
                        @elseif(\Illuminate\Support\Facades\Session::has('success'))
                        <span class="badge badge-info">Downloading</span>
                        @elseif(\Illuminate\Support\Facades\Storage::disk('starmap')->exists(\App\Models\Starsystem::makeFilenameFromCode($system->code)))
                        <span class="badge badge-success">Downloaded</span>
                        @else
                        <span class="badge badge-warning">Nicht vorhanden</span>
                        @endif
                    </td>
                    <td>
                        @if(\Illuminate\Support\Facades\Storage::disk('starmap')->exists(\App\Models\Starsystem::makeFilenameFromCode($system->code)))
                        {{ \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\Storage::disk('starmap')->lastModified(\App\Models\Starsystem::makeFilenameFromCode($system->code)))->format('d.m.Y H:i:s') }}
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @component('components.edit_delete_block')
                            @slot('edit_url')
                                {{ route('admin_starmap_systems_edit_form', $system->code) }}
                            @endslot
                            {{ $system->id }}
                        @endcomponent
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    @include('components.init_dataTables')
@endsection