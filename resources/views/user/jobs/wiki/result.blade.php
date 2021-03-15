@extends('user.layouts.default_wide')

@section('title', __('Fahrzeugpreise'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Resultat')</h4>
        <div class="card-body px-0 table-responsive">
            <div class="alert alert-info">
                <p>Folgende Daten wurden in der CSV Datei gefunden</p>
            </div>

            <hr>

            <h5 class="pl-2">Kaufpreise</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Name</th>
                        <th>Typ</th>
                        <th>Preis</th>
                        <th>Händler</th>
                        <th>Landezone</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($buyables as $buyable)
                    <tr>
                        <td>{{ $buyable['Spielversion'] }}</td>
                        <td>{{ $buyable['Name'] }}</td>
                        <td>{{ $buyable['Typ'] }}</td>
                        <td>{{ $buyable['Kaufpreis'] }}</td>
                        <td>{{ $buyable['Händler'] }}</td>
                        <td>{{ $buyable['Landezone'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <hr>
            <h5 class="pl-2">Mietpreise</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Name</th>
                        <th>Typ</th>
                        <th>Händler</th>
                        <th>Landezone</th>
                        <th>1 Tag</th>
                        <th>3 Tage</th>
                        <th>7 Tage</th>
                        <th>30 Tage</th>
                        <th>REC</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rentables as $rentable)
                    <tr>
                        <td>{{ $rentable['Spielversion'] }}</td>
                        <td>{{ $rentable['Name'] }}</td>
                        <td>{{ $rentable['Typ'] }}</td>
                        <td>{{ $rentable['Händler'] }}</td>
                        <td>{{ $rentable['Landezone'] }}</td>
                        <td>{{ $rentable['1 Tag'] ?? '-' }}</td>
                        <td>{{ $rentable['3 Tage'] ?? '-' }}</td>
                        <td>{{ $rentable['7 Tage'] ?? '-' }}</td>
                        <td>{{ $rentable['30 Tage'] ?? '-' }}</td>
                        <td>{{ $rentable['REC'] ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @component('components.forms.form', [
                'action' => route('web.user.jobs.wiki.upload_wiki'),
                'class' => 'd-flex w-100',
            ])
                <a class="btn btn-outline-danger mr-auto" href="{{ route('web.user.dashboard') }}">Abbrechen</a>
                <button class="btn btn-success">@lang('Daten im Wiki veröffentlichen')</button>
            @endcomponent
        </div>
    </div>
@endsection
