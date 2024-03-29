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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>
                            @can('web.internals.view')
                                <td>
                                    {{ $vehicle->id }}
                                </td>
                            @endcan
                            <td title="{{ $vehicle->item_uuid }}">
                                <a href="{{ config('app.url') }}/api/v2/vehicles/{{ $vehicle->item_uuid }}">{{ $vehicle->name }}</a>
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
                                {{ $vehicle->length }}
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
                            <td>
                                @include('components.edit_delete_block', [
                                    'show_url' => route('web.starcitizenunpacked.items.show', $vehicle->item->uuid)
                                ])
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