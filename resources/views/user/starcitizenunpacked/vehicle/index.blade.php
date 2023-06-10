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
                        <th>@lang('Name')</th>
                        <th>@lang('Klassenname')</th>
                        <th>@lang('Hersteller')</th>
                        <th>@lang('Karriere')</th>
                        <th>@lang('Rolle')</th>
                        <th>@lang('Größe')</th>
                        <th>@lang('Länge')</th>
                        <th>@lang('Breite')</th>
                        <th>@lang('Höhe')</th>
                        <th>@lang('Version')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>
                            @can('web.user.internals.view')
                                <td>
                                    {{ $vehicle->id }}
                                </td>
                            @endcan
                            <td title="{{ $vehicle->item_uuid }}">
                                {{ $vehicle->name }}
                            </td>
                            <td>
                                {{ $vehicle->class_name }}
                            </td>
                            <td>
                                {{ $vehicle->manufacturer->name }}
                            </td>
                            <td>
                                {{ $vehicle->career }}
                            </td>
                            <td>
                                {{ $vehicle->role }}
                            </td>
                            <td>
                                {{ $vehicle->size }}
                            </td>
                            <td>
                                {{ $vehicle->beam }}
                            </td>
                            <td>
                                {{ $vehicle->width }}
                            </td>
                            <td>
                                {{ $vehicle->height }}
                            </td>
                            <td>
                                {{ $vehicle->item->version }}
                            </td>
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
    @if(count($vehicles) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection