@extends('user.layouts.default_wide')

@section('title', __('Raumschiffe'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Raumschiffe')</h4>
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

                @forelse($ships as $ship)
                    <tr>
                        @can('web.user.internals.view')
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
                            {{
                                $ship->foci->transform(function(\App\Models\Api\StarCitizen\Vehicle\Focus\Focus $focus) {
                                    return optional($focus->german())->translation ?? $focus->english()->translation ?? __('Keiner');
                                })->implode(', ')
                            }}
                        </td>
                        <td>
                            {{ optional($ship->type->german())->translation ?? $ship->type->english()->translation ?? __('Keiner') }}
                        </td>
                        <td>
                            {{ optional($ship->productionStatus->german())->translation ?? $ship->productionStatus->english()->translation ?? __('Keiner') }}
                        </td>
                        <td>
                            {{ optional($ship->productionNote->german())->translation ?? $ship->productionNote->english()->translation ?? __('Keine') }}
                        </td>
                        <td data-order="{{ $ship->updated_at->timestamp }}">
                            {{ $ship->updated_at->diffForHumans() }}
                        </td>
                        @can('web.user.starcitizen.vehicles.update')
                            <td class="text-center">
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.user.starcitizen.vehicles.ships.edit', $ship->getRouteKey()) }}
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
    @if(count($ships) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection