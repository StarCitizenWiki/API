@extends('web.layouts.default_wide')

@section('title', __('Fahrzeuge'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Fahrzeuge')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        @can('web.internals.view')
                            <th>@lang('ID')</th>
                        @endcan
                        <th>@lang('CIG ID')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Preis')</th>
                        <th>@lang('Hersteller')</th>
                        <th>@lang('Fokus')</th>
                        <th>@lang('Typ')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Notiz')</th>
                        <th>@lang('Update')</th>
                        @can('web.starcitizen.vehicles.update')
                            <th data-orderable="false"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($groundVehicles as $groundVehicle)
                        <tr>
                            @can('web.internals.view')
                                <td>
                                    {{ $groundVehicle->id }}
                                </td>
                            @endcan
                            <td>
                                {{ $groundVehicle->cig_id }}
                            </td>
                            <td>
                                {{ $groundVehicle->name }}
                            </td>
                            <td>
                                {{ ($groundVehicle->msrp ?? '-') }}$
                            </td>
                            <td>
                                {{ optional($groundVehicle->manufacturer)->name_short }}
                            </td>
                            <td>
                                {{
                                    $groundVehicle->foci->transform(function(\App\Models\StarCitizen\Vehicle\Focus\Focus $focus) {
                                        return optional($focus->german())->translation ?? $focus->english()->translation ?? __('Keiner');
                                    })->implode(', ')
                                }}
                            </td>
                            <td>
                                {{ optional($groundVehicle->type->german())->translation ?? $groundVehicle->type->english()->translation ?? __('Keiner') }}
                            </td>
                            <td>
                                {{ optional($groundVehicle->productionStatus->german())->translation ?? $groundVehicle->productionStatus->english()->translation ?? __('Keiner') }}
                            </td>
                            <td>
                                {{ optional($groundVehicle->productionNote->german())->translation ?? $groundVehicle->productionNote->english()->translation ?? __('Keine') }}
                            </td>
                            <td data-order="{{ $groundVehicle->updated_at->timestamp }}">
                                {{ $groundVehicle->updated_at->diffForHumans() }}
                            </td>
                            @can('web.starcitizen.vehicles.update')
                                <td class="text-center">
                                    @component('components.edit_delete_block')
                                        @slot('edit_url')
                                            {{ route('web.starcitizen.vehicles.ground-vehicles.edit', $groundVehicle->getRouteKey()) }}
                                        @endslot
                                        {{ $groundVehicle->getRouteKey() }}
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
    @if(count($groundVehicles) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection