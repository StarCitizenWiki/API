@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Users')
@section('lead', 'Users')

@section('content')
    @include('layouts.heading');
    <div class="container-fluid">
        <div class="row">
            <div class="col-8 mx-auto mt-5">
                <table class="table">
                    <thead>
                        <tr>
                            <th><span>ID</span></th>
                            <th><span>Name</span></th>
                            <th><span>Erstellt</span></th>
                            <th><span>Letzter Login</span></th>
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
                                    <i class="fa fa-key" data-placement="top" data-toggle="popover" title="Key" data-content="{{ $user->api_token }}" data-trigger="focus" tabindex="0"></i>
                                </td>                                <td>
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
                                        <a href="users/{{ $user->id }}/edit" class="btn btn-warning">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="#" class="btn btn-danger"
                                            onclick="event.preventDefault();
                                            document.getElementById('delete-form{{ $user->id }}').submit();">
                                            <form id="delete-form{{ $user->id }}" action="users/{{ $user->id }}/delete" method="POST" style="display: none;">
                                                <input name="_method" type="hidden" value="DELETE">
                                                {{ csrf_field() }}
                                            </form>
                                            <i class="fa fa-trash-o"></i>
                                        </a>

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
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha256-gL1ibrbVcRIHKlCO5OXOPC/lZz/gpdApgQAzskqqXp8=" crossorigin="anonymous"></script>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover()
        })
    </script>
@endsection
