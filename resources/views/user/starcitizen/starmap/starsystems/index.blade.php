@extends('user.layouts.default_wide')

@section('title', __('Sternensysteme'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Sternensysteme')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        @can('web.user.internals.view')
                            <th>@lang('ID')</th>
                        @endcan
                        <th>@lang('CIG ID')</th>
                        <th>@lang('Code')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Typ')</th>
                        <th>@lang('Planeten')</th>
                        <th>@lang('Monde')</th>
                        <th>@lang('Größe')</th>
                        <th>@lang('Bevölkerung')</th>
                        <th>@lang('Wirtschaft')</th>
                        <th>@lang('Gefahr')</th>
                        <th>@lang('Update')</th>
                        @can('web.user.starcitizen.starmap.view')
                            <th data-orderable="false"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($systems as $system)
                        <tr>
                            @can('web.user.internals.view')
                                <td>
                                    {{ $system->id }}
                                </td>
                            @endcan
                            <td>
                                {{ $system->cig_id }}
                            </td>
                            <td>
                                {{ $system->code }}
                            </td>
                            <td>
                                {{ $system->name }}
                            </td>
                            <td>
                                {{ $system->status }}
                            </td>
                            <td>
                                {{ $system->type }}
                            </td>
                            <td>
                                {{ $system->planets_count }}
                            </td>
                            <td>
                                {{ $system->moons_count }}
                            </td>
                            <td>
                                {{ $system->aggregated_size }}
                            </td>
                            <td>
                                {{ $system->aggregated_population }}
                            </td>
                            <td>
                                {{ $system->aggregated_economy }}
                            </td>
                            <td>
                                {{ $system->aggregated_danger }}
                            </td>
                            <td data-order="{{ $system->time_modified->timestamp }}">
                                {{ $system->time_modified->diffForHumans() }}
                            </td>
                            @can('web.user.starcitizen.starmap.view')
                                <td class="text-center">
                                    @component('components.edit_delete_block')
                                        @slot('show_url')
                                            {{ route('web.user.starcitizen.starmap.starsystems.show', $system->getRouteKey()) }}
                                        @endslot
                                        {{ $system->getRouteKey() }}
                                    @endcomponent
                                </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10">@lang('Keine Sternensysteme vorhanden')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    @if(count($systems) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection