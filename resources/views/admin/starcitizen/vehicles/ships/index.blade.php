@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Raumschiffe')</h4>
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

                @forelse($ships as $ship)
                    <tr>
                        @can('web.admin.internals.view')
                            <td>
                                {{ $ship->id }}
                            </td>
                        @endcan
                        <td>
                            {{ $ship->cig_id }}
                        </td>
                        <td>
                            {{ $ship->name }}
                        </td>
                        <td>
                            {{ optional($ship->manufacturer)->name_short }}
                        </td>
                        <td>
                            @foreach($ship->foci as $focus)
                                {{ $focus->english()->translation }}<br>
                            @endforeach
                        </td>
                        <td>
                            {{ $ship->type->english()->translation ?? 'None' }}
                        </td>
                        <td>
                            {{ $ship->productionStatus->english()->translation }}
                        </td>
                        <td>
                            {{ optional($ship->productionNote)->english()->translation ?? 'None' }}
                        </td>
                        <td>
                            {{ $ship->updated_at->diffForHumans() }}
                        </td>
                        @can('web.admin.starcitizen.vehicles.update')
                            <td>
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.admin.starcitizen.vehicles.ships.edit', $ship->getRouteKey()) }}
                                    @endslot
                                    {{ $ship->getRouteKey() }}
                                @endcomponent
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">@lang('Keine Raumschiffe vorhanden')</td>
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