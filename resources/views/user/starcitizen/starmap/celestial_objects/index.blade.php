@extends('user.layouts.default_wide')

@section('title', __('Objekte'))

@section('content')
    <div id="celestial-object-generator">
        <celestial-object-generator api-url="{{ config('mediawiki.api_url') }}"></celestial-object-generator>
    </div>
    <div class="card">
        <h4 class="card-header">@lang('Objekte')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        @can('web.user.internals.view')
                            <th>@lang('ID')</th>
                        @endcan
                        <th>@lang('CIG ID')</th>
                        <th>@lang('Sternensystem')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Bezeichnung')</th>
                        <th>@lang('Typ')</th>
                        <th title="Fair Chance Act">@lang('FCA')</th>
                        <th>@lang('Habitabel')</th>
                        <th>@lang('Lat')</th>
                        <th>@lang('Lon')</th>
                        <th>@lang('Bevölkerung')</th>
                        <th>@lang('Wirtschaft')</th>
                        <th>@lang('Gefahr')</th>
                        <th>@lang('Update')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($objects as $object)
                        <tr>
                            @can('web.user.internals.view')
                                <td>
                                    {{ $object->id }}
                                </td>
                            @endcan
                            <td>
                                {{ $object->cig_id }}
                            </td>
                            <td>
                                {{ $object->starsystem->name }}
                            </td>
                            <td>
                                {{ $object->name }}
                            </td>
                            <td>
                                {{ $object->designation }}
                            </td>
                            <td>
                                {{ $object->type }}
                            </td>
                            <td>
                                {{ $object->fairchanceact === true ? __('Ja') : __('Nein') }}
                            </td>
                            <td>
                                {{ $object->habitable === true ? __('Ja') : __('Nein') }}
                            </td>
                            <td>
                                {{ $object->latitude }}
                            </td>
                            <td>
                                {{ $object->longitude }}
                            </td>
                            <td>
                                {{ $object->sensor_population }}
                            </td>
                            <td>
                                {{ $object->sensor_economy }}
                            </td>
                            <td>
                                {{ $object->sensor_danger }}
                            </td>
                            <td data-order="{{ $object->time_modified->timestamp }}">
                                {{ $object->time_modified->diffForHumans() }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10">@lang('Keine Objekte vorhanden')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    @if(count($objects) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection