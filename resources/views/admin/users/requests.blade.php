@extends('layouts.admin')
@section('title', 'User Requests')

@section('content')
    <table class="table table-striped" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th><span>ID</span></th>
            <th><span>Request Time</span></th>
            <th><span>Path</span></th>
        </tr>
        </thead>
        <tbody>
        @if(count($requests) > 0)
            @foreach($requests as $request)
                <tr>
                    <td>
                        {{ $request->id }}
                    </td>
                    <td>
                        {{ $request->created_at->format('d.m.Y H:i:s') }}
                    </td>
                    <td>
                        {{ $request->request_uri }}
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="3">Keine Requests vorhanden</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap4.min.js"></script>
    @include('components.init_dataTables')
@endsection
