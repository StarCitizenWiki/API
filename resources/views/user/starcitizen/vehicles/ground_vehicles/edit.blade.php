@extends('user.layouts.default_wide')

@section('title')
    @lang('Fahrzeug') {{ $groundVehicle->name}} @lang('bearbeiten')
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-xl-6">
            @component('user.components.card', [
                'class' => 'mb-4',
            ])
                @slot('title')
                    <h4 class="mb-0">@lang('Fahrzeugdaten') <small class="float-right mt-1">Letztes Update: {{ $groundVehicle->updated_at->diffForHumans() }}</small></h4>
                @endslot
                @component('components.forms.form')
                        <div class="row">
                            <div class="col-8 col-md-8 col-lg-4 col-xl-8 col-xxl-4">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Name'),
                                    'id' => 'name',
                                    'value' => $groundVehicle->name,
                                ])@endcomponent
                            </div>
                            <div class="col-4 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Preis'),
                                    'id' => 'msrp',
                                    'value' => ($groundVehicle->msrp ?? '-').'$',

                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-4 col-lg-2 col-xl-6 col-xxl-4">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Hersteller'),
                                    'id' => 'manufacturer',
                                    'value' => $groundVehicle->manufacturer->name_short,
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('CIG ID'),
                                    'id' => 'cig_id',
                                    'value' => $groundVehicle->cig_id,
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Chassis ID'),
                                    'id' => 'chassis_id',
                                    'value' => $groundVehicle->chassis_id,
                                ])@endcomponent
                            </div>

                            <div class="col-12 col-lg-6 col-xl-6 col-xxl-4">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Produktionsstatus'),
                                    'id' => 'status',
                                    'value' => optional($groundVehicle->productionStatus->german())->translation ?? optional($groundVehicle->productionStatus->english())->translation ?? '',
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-lg-6 col-xl-6 col-xxl-4">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Produktionsnotiz'),
                                    'id' => 'note',
                                    'value' => optional($groundVehicle->productionNote->german())->translation ?? optional($groundVehicle->productionNote->english())->translation ?? '',
                                ])@endcomponent
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Größe'),
                                    'id' => 'size',
                                    'value' => optional($groundVehicle->size->german())->translation ?? optional($groundVehicle->size->english())->translation ?? '',
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Typ'),
                                    'id' => 'type',
                                    'value' => optional($groundVehicle->type->german())->translation ?? optional($groundVehicle->type->english())->translation ?? '',
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-4 col-lg-2 col-xl-4 col-xxl-2">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Länge'),
                                    'id' => 'length',
                                    'value' => $groundVehicle->length.' m',
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-4 col-lg-2 col-xl-4 col-xxl-2">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Breite'),
                                    'id' => 'beam',
                                    'value' => $groundVehicle->beam.' m',
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-4 col-lg-2 col-xl-4 col-xxl-2">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Höhe'),
                                    'id' => 'height',
                                    'value' => $groundVehicle->height.' m',
                                ])@endcomponent
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Frachtkapazität'),
                                    'id' => 'cargo',
                                    'value' => ($groundVehicle->cargo_capacity ?? '-').' SCU',
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Mass'),
                                    'id' => 'mass',
                                    'value' => $groundVehicle->mass.' Kg',
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Min. Besatzung'),
                                    'id' => 'crew_min',
                                    'value' => $groundVehicle->min_crew,
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Max. Besatzung'),
                                    'id' => 'crew_max',
                                    'value' => $groundVehicle->max_crew,
                                ])@endcomponent
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-6">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('Fokus'),
                                    'id' => 'focus',
                                ])
                                    @slot('value')
                                        {{
                                            $groundVehicle->foci->transform(function(\App\Models\StarCitizen\Vehicle\Focus\Focus $focus) {
                                                return optional($focus->german())->translation ?? optional($focus->english()->translation) ?? __('Keiner');
                                            })->implode(', ')
                                        }}
                                    @endslot
                                @endcomponent
                            </div>

                            <div class="col-12 col-lg-6">
                                @component('components.forms.form-group', [
                                    'inputType' => 'text',
                                    'inputOptions' => 'readonly',
                                    'label' => __('SCM Geschwindigkeit'),
                                    'id' => 'scm',
                                    'value' => $groundVehicle->scm_speed.' m/s',
                                ])@endcomponent
                            </div>
                        </div>

                    @endcomponent
            @endcomponent

            <div class="card">
                <h4 class="card-header">
                    <a data-toggle="collapse" href="#components" role="button"
                       aria-expanded="false" aria-controls="components">Komponenten anzeigen</a>
                </h4>
                <div class="card-body collapse" id="components">
                    @include('user.components.starcitizen.vehicle_components', ['componentGroups' => $componentGroups])
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            @component('components.forms.form', [
                'action' => route('web.user.starcitizen.vehicles.ground-vehicles.update', $groundVehicle->getRouteKey()),
                'method' => 'PATCH',
                'class' => 'card'
            ])
                <div class="wrapper">
                    <h4 class="card-header">@lang('Übersetzungen')</h4>
                    <div class="card-body">
                        @include('components.errors')
                        @include('components.messages')
                        @foreach($groundVehicle->translationsCollection() as $key => $translation)
                            @component('components.forms.form-group', [
                                'inputType' => 'textarea',
                                'label' => __('Beschreibung').' '.__($key),
                                'id' => $key,
                                'rows' => 6,
                                'value' => $translation->translation,
                            ])
                                @slot('inputOptions')
                                    @if($key === config('language.english'))
                                        readonly
                                    @endif
                                @endslot
                            @endcomponent
                        @endforeach
                    </div>
                    <div class="card-footer d-flex">
                        <button class="btn btn-outline-secondary ml-auto" name="save">@lang('Speichern')</button>
                    </div>
                </div>
            @endcomponent

            <div class="card mt-3">
                <h4 class="card-header">Änderungen</h4>
                <div class="card-body">
                    @component('user.components.changelog_list', [
                        'changelogs' => $changelogs,
                    ])
                        Fahrzeug
                    @endcomponent
                </div>
            </div>
        </div>
    </div>
@endsection
