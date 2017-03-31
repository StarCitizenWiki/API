@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Short URLs Whitelist')
@section('lead', 'Short URLs Whitelist')

@section('header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.13/css/dataTables.bootstrap4.min.css" integrity="sha256-8q/3ffDrRz4p4BiTZBtd2pgHADVDicr2W2Xvd43ABkI=" crossorigin="anonymous" />
@endsection

@section('content')
    @include('layouts.heading')
    <div class="container-fluid">
        <div class="row">
            <div class="col-10 mx-auto mt-5">
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
                                    <div class="btn-group btn-group-sm" role="group" aria-label="">
                                        <a href="#" class="btn btn-danger"
                                           onclick="event.preventDefault();
                                                   document.getElementById('delete-form{{ $url->id }}').submit();">
                                            <form id="delete-form{{ $url->id }}" action="{{ route('admin_urls_whitelist_delete', $url->id) }}" method="POST" style="display: none;">
                                                <input name="_method" type="hidden" value="DELETE">
                                                {{ csrf_field() }}
                                            </form>
                                            <i class="fa fa-trash-o"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#urlTable').DataTable();
        } );
    </script>
@endsection