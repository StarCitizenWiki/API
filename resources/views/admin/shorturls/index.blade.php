@extends('layouts.admin')
@section('title', 'Short URLs')

@section('header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.13/css/dataTables.bootstrap4.min.css" integrity="sha256-8q/3ffDrRz4p4BiTZBtd2pgHADVDicr2W2Xvd43ABkI=" crossorigin="anonymous" />
@endsection

@section('content')
    <table class="table table-striped" id="urlTable" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th><span>ID</span></th>
            <th><span>URL</span></th>
            <th><span>Hash</span></th>
            <th><span>Owner</span></th>
            <th><span>Erstellt</span></th>
            <th><span>Ablauf</span></th>
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
                    {{ $url->hash_name }}
                </td>
                <td>
                    {{ $url->user()->first()->email }}
                </td>
                <td>
                    {{ Carbon\Carbon::parse($url->created_at)->format('d.m.Y') }}
                </td>
                <td>
                    @component('components.shorturls.expiresfield', ['expires' => $url->expires])@endcomponent
                </td>
                <td>
                    @component('components.edit_delete_block')
                        @slot('edit_url')
                            {{ route('admin_urls_edit_form', $url->id) }}
                        @endslot
                        @slot('delete_url')
                                {{ route('admin_urls_delete') }}
                        @endslot
                        {{ $url->id }}
                    @endcomponent
                </td>
            </tr>
            @endforeach
        @else
        <tr>
            <td colspan="7">Keine Short URLs vorhanden</td>
        </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('scripts')
    @include('components.init_dataTables')
@endsection
