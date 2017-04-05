@extends('layouts.admin')
@section('title', 'Users')

@section('header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.13/css/dataTables.bootstrap4.min.css" integrity="sha256-8q/3ffDrRz4p4BiTZBtd2pgHADVDicr2W2Xvd43ABkI=" crossorigin="anonymous" />
@endsection

@section('content')
    <table class="table table-striped" id="userTable" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th><span>ID</span></th>
                <th><span>Name</span></th>
                <th><span>Erstellt</span></th>
                <th><span>Letzter Login</span></th>
                <th><span>Letzte Abfrage</span></th>
                <th class="text-center"><span>Status</span></th>
                <th><span>E-Mail</span></th>
                <th><span>API Key</span></th>
                <th><span>Notizen</span></th>
                <th><span>RPM</span></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        @if(count($users) > 0)
            @foreach($users as $user)
                <tr>
                    <td>
                        {{ $user->id }}
                    </td>
                    <td>
                        {{ $user->name }}
                    </td>
                    <td>
                        {{ Carbon\Carbon::parse($user->created_at)->format('d.m.Y') }}
                    </td>
                    <td>
                        {{ Carbon\Carbon::parse($user->last_login)->format('d.m.Y') }}
                    </td>
                    <td>
                        @unless(is_null($user->api_token_last_used))
                            {{ Carbon\Carbon::parse($user->api_token_last_used)->format('d.m.Y H:i:s') }}
                        @else
                            Nie
                        @endunless
                    </td>
                    <td class="text-center">
                        @if($user->deleted_at)
                            <span class="badge badge-info">Gelöscht</span>
                        @elseif($user->isWhitelisted())
                            <span class="badge badge-success">Unlimitiert</span>
                        @elseif($user->isBlacklisted())
                            <span class="badge badge-danger">Gesperrt</span>
                        @else
                            <span class="badge badge-default">Normal</span>
                        @endif
                    </td>
                    <td>
                        {{ $user->email }}
                    </td>
                    <td>
                        <i class="fa fa-key" data-placement="top" data-toggle="popover" title="Key" data-content="{{ $user->api_token }}" tabindex="0"></i>
                    </td>
                    <td>
                        <i class="fa fa-book" data-placement="top" data-toggle="popover" title="Notizen" data-content="{{ $user->notes }}" data-trigger="focus" tabindex="1"></i>
                    </td>
                    <td>
                        <code>
                        @if($user->isWhitelisted() || $user->isBlacklisted())
                            -
                        @else
                            {{ $user->requests_per_minute }}
                        @endif
                        </code>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="">
                            <a href="{{ route('admin_users_edit_form', $user->id) }}" class="btn btn-warning">
                                <i class="fa fa-pencil"></i>
                            </a>
                            @unless($user->trashed())
                            <a href="#" class="btn btn-danger"
                                onclick="event.preventDefault();
                                document.getElementById('delete-form{{ $user->id }}').submit();">
                                <form id="delete-form{{ $user->id }}" action="{{ route('admin_users_delete') }}" method="POST" style="display: none;">
                                    <input name="_method" type="hidden" value="DELETE">
                                    <input name="id" type="hidden" value="{{ $user->id }}">
                                    {{ csrf_field() }}
                                </form>
                                <i class="fa fa-trash-o"></i>
                            </a>
                            @else
                                <a href="#" class="btn btn-success"
                                   onclick="event.preventDefault();
                                           document.getElementById('restore-form{{ $user->id }}').submit();">
                                    <form id="restore-form{{ $user->id }}" action="{{ route('admin_users_restore') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="id" value="{{ $user->id }}">
                                    </form>
                                    <i class="fa fa-repeat"></i>
                                </a>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="7">Keine Benutzer vorhanden</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover()
        });
    </script>
    @include('components.init_dataTables')
@endsection
