@extends('layouts.app')
@section('title', 'Short URLs')

@section('header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.13/css/dataTables.bootstrap4.min.css" integrity="sha256-8q/3ffDrRz4p4BiTZBtd2pgHADVDicr2W2Xvd43ABkI=" crossorigin="anonymous" />
@endsection

@section('content')
    @include('layouts.heading')
    @if (session('hash_name'))
        <div class="container-fluid">
            <div class="row">
                <div class="col-10 col-md-3 mx-auto">
                    <div class="alert alert-success text-center">
                        https://{{config('app.shorturl_url')}}/{{ session('hash_name') }}
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="container-fluid">
        <div class="row">
            <div class="col-10 mx-auto mt-5">
                <table class="table table-striped" id="urlTable" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th><span>Short</span></th>
                        <th><span>URL</span></th>
                        <th><span>Hash</span></th>
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
                                https://{{config('app.shorturl_url')}}/{{ $url->hash_name }}
                            </td>
                            <td>
                                {{ $url->url }}
                            </td>
                            <td>
                                {{ $url->hash_name }}
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
                                        {{ route('account_urls_edit_form', $url->id) }}
                                    @endslot
                                    @slot('delete_url')
                                        {{ route('account_urls_delete') }}
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
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('components.init_dataTables')
@endsection
