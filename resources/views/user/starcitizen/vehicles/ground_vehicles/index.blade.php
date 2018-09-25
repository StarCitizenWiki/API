@extends('user.layouts.default_wide')

@section('title', __('Fahrzeuge'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Fahrzeuge')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        @can('web.user.internals.view')
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
                        @can('web.user.starcitizen.vehicles.update')
                            <th data-orderable="false">&nbsp;</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>

                @forelse($groundVehicles as $groundVehicle)
                    <tr>
                        @can('web.user.internals.view')
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
                            {{ optional($groundVehicle->manufacturer)->name_short }}
                        </td>
                        <td>
                            @foreach($groundVehicle->foci as $focus)
                                {{ $focus->english()->translation }}<br>
                            @endforeach
                        </td>
                        <td>
                            {{ $groundVehicle->type->english()->translation ?? 'None' }}
                        </td>
                        <td>
                            {{ $groundVehicle->productionStatus->english()->translation }}
                        </td>
                        <td>
                            {{ optional($groundVehicle->productionNote)->english()->translation ?? 'None' }}
                        </td>
                        <td data-order="{{ $groundVehicle->updated_at->timestamp }}">
                            {{ $groundVehicle->updated_at->diffForHumans() }}
                        </td>
                        @can('web.user.starcitizen.vehicles.update')
                            <td class="text-center">
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.user.starcitizen.vehicles.ground-vehicles.edit', $groundVehicle->getRouteKey()) }}
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
    @include('components.init_dataTables')
@endsection