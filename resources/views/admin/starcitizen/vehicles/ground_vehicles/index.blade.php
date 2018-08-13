@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Fahrzeuge')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        @can('web.admin.internals.view')
                            <th>@lang('ID')</th>
                        @endcan
                        <th>@lang('CIG ID')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Hersteller')</th>
                        <th>@lang('Fokus')</th>
                        <th>@lang('Typ')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Notiz')</th>
                        <th>@lang('Update')</th>
                        @can('web.admin.starcitizen.vehicles.update')
                            <th>&nbsp;</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>

                @forelse($ground_vehicles as $ground_vehicle)
                    <tr>
                        @can('web.admin.internals.view')
                            <td>
                                {{ $ground_vehicle->id }}
                            </td>
                        @endcan
                        <td>
                            {{ $ground_vehicle->cig_id }}
                        </td>
                        <td>
                            {{ $ground_vehicle->name }}
                        </td>
                        <td>
                            {{ optional($ground_vehicle->manufacturer)->name_short }}
                        </td>
                        <td>
                            @foreach($ground_vehicle->foci as $focus)
                                {{ $focus->english()->translation }}<br>
                            @endforeach
                        </td>
                        <td>
                            {{ $ground_vehicle->type->english()->translation ?? 'None' }}
                        </td>
                        <td>
                            {{ $ground_vehicle->productionStatus->english()->translation }}
                        </td>
                        <td>
                            {{ optional($ground_vehicle->productionNote)->english()->translation ?? 'None' }}
                        </td>
                        <td>
                            {{ $ground_vehicle->updated_at->diffForHumans() }}
                        </td>
                        @can('web.admin.starcitizen.vehicles.update')
                            <td>
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.admin.starcitizen.vehicles.ships.edit', $ground_vehicle->getRouteKey()) }}
                                    @endslot
                                    {{ $ground_vehicle->getRouteKey() }}
                                @endcomponent
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">@lang('Keine Fahrzeuge vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    @include('components.init_dataTables')
@endsection