@extends('layouts.admin')
@section('title', 'Short URLs Whitelist')

@section('header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.13/css/dataTables.bootstrap4.min.css" integrity="sha256-8q/3ffDrRz4p4BiTZBtd2pgHADVDicr2W2Xvd43ABkI=" crossorigin="anonymous" />
@endsection

@section('content')
    <table class="table table-striped" id="urlTable" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th><span>ID</span></th>
            <th><span>URL</span></th>
            <th><span>Öffentlich</span></th>
            <th><span>Erstellt</span></th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @if(count($urls) > 0)
            @foreach($urls as $url)
                <tr>
                    <td>
                        {{ $url->id }}
                    </td>
                    <td>
                        {{ $url->url }}
                    </td>
                    <td>
                        {{ $url->internal ? 'Nein' : 'Ja' }}
                    </td>
                    <td>
                        {{ Carbon\Carbon::parse($url->created_at)->format('d.m.Y') }}
                    </td>
                    <td>
                        @component('components.edit_delete_block')
                            @slot('delete_url')
                                {{ route('admin_urls_whitelist_delete') }}
                            @endslot
                            {{ $url->id }}
                        @endcomponent
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap4.min.js"></script>
    @include('components.init_dataTables')
@endsection
