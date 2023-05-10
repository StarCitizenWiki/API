@extends('user.layouts.default_wide')

@section('title', __('Herstellerübersicht'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Hersteller')</h4>
        <div class="card-body px-0 table-responsive">
            @include('components.messages')
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        @can('web.user.internals.view')
                            <th>@lang('ID')</th>
                        @endcan
                        <th>@lang('CIG ID')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Code')</th>
                        <th>@lang('Raumschiffe')</th>
                        <th>@lang('Fahrzeuge')</th>
                        @can('web.user.starcitizen.manufacturers.update')
                            <th data-orderable="false">&nbsp;</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>

                @forelse($manufacturers as $manufacturer)
                    <tr>
                        @can('web.user.internals.view')
                            <td>
                                {{ $manufacturer->id }}
                            </td>
                        @endcan
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
                            {{ $manufacturer->ships_count }}
                        </td>
                        <td>
                            {{ $manufacturer->vehicles_count }}
                        </td>
                        @can('web.user.starcitizen.manufacturers.update')
                            <td class="text-center">
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.user.starcitizen.manufacturers.edit', $manufacturer->getRouteKey()) }}
                                    @endslot
                                    {{ $manufacturer->getRouteKey() }}
                                @endcomponent
                            </td>
                            @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine Hersteller vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <h4 class="card-header">@lang('Hersteller') In-Game</h4>
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>@lang('Name')</th>
                    <th>@lang('Code')</th>
                    <th>@lang('Raumschiffe')</th>
                    <th>@lang('Fahrzeuge')</th>
                    <th>@lang('Items')</th>
                </tr>
                </thead>
                <tbody>

                @forelse($manufacturers_ingame as $manufacturer)
                    <tr>
                        <td>
                            {{ $manufacturer->name }}
                        </td>
                        <td>
                            {{ $manufacturer->code }}
                        </td>
                        <td>
                            {{ $manufacturer->shipsCount() }}
                        </td>
                        <td>
                            {{ $manufacturer->groundVehiclesCount() }}
                        </td>
                        <td>
                            {{ $manufacturer->itemsCount() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine Hersteller vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection