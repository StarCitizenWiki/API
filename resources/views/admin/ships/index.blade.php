@extends('layouts.admin')
@section('title', 'Ships')

@section('content')
    <div class="mt-5 col-12 col-md-8 mx-auto">
        <table class="table mt-5">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Last Download</th>
                </tr>
            </thead>
            <tbody>
            @foreach($ships as $ship)
                <tr>
                    <td>{{ $ship->getFilename() }}</td>
                    <td>
                        {{ \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\File::lastModified($ship))->format('d.m.Y H:i:s') }}
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