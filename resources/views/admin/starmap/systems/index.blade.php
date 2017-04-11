@extends('layouts.admin')
@section('title', 'Starmap Systems')

@section('content')
    <div class="mt-5 col-12 col-md-8 mx-auto">
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