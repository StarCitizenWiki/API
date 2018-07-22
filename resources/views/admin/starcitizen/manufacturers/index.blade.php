@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Raumschiffe')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('CIG ID')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('Code')</th>
                    <th>@lang('Known For')</th>
                    <th>@lang('Description')</th>
                    <th>@lang('Ships')</th>
                    <th>@lang('Vehicles')</th>
                </tr>
                </thead>
                <tbody>

                @forelse($manufacturers as $manufacturer)
                    <tr>
                        <td>
                            {{ $manufacturer->id }}
                        </td>
                        <td>
                            {{ $manufacturer->cig_id }}
                        </td>
                        <td>
                            {{ $manufacturer->name }}
                        </td>
                        <td>
                            {{ $manufacturer->name_short }}
                        </td>
                        <td>
                            {{ $manufacturer->english()->known_for }}
                        </td>
                        <td>
                            {{ $manufacturer->english()->description }}
                        </td>
                        <td>
                            {{ count($manufacturer->ships) }}
                        </td>
                        <td>
                            {{ count($manufacturer->groundVehicles) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine Benutzer vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection