@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Raumschiffe')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('CIG ID')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('Hersteller')</th>
                    <th>@lang('Fokus')</th>
                    <th>@lang('Typ')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Notiz')</th>
                </tr>
                </thead>
                <tbody>

                @forelse($ships as $ship)
                    <tr>
                        <td>
                            {{ $ship->id }}
                        </td>
                        <td>
                            {{ $ship->cig_id }}
                        </td>
                        <td>
                            {{ $ship->name }}
                        </td>
                        <td>
                            {{ $ship->manufacturer->name_short }}
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine Benutzer vorhanden')</td>
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