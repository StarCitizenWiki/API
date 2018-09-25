@extends('admin.layouts.default_wide')

@section('title', __('Herstellerübersicht'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Hersteller')</h4>
        <div class="card-body px-0 table-responsive">
            @include('components.messages')
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        @can('web.admin.internals.view')
                            <th>@lang('ID')</th>
                        @endcan
                        <th>@lang('CIG ID')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Code')</th>
                        <th>@lang('Raumschiffe')</th>
                        <th>@lang('Fahrzeuge')</th>
                        @can('web.admin.starcitizen.manufacturers.update')
                            <th data-orderable="false">&nbsp;</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>

                @forelse($manufacturers as $manufacturer)
                    <tr>
                        @can('web.admin.internals.view')
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
                            {{ count($manufacturer->ships) }}
                        </td>
                        <td>
                            {{ count($manufacturer->groundVehicles) }}
                        </td>
                        @can('web.admin.starcitizen.manufacturers.update')
                            <td class="text-center">
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.admin.starcitizen.manufacturers.edit', $manufacturer->getRouteKey()) }}
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
        </div>
    </div>
@endsection