@extends('admin.layouts.default_wide')

@section('content')
    <div class="card-deck">
        <div class="card">
            <h4 class="card-header">@lang('Fahrzeugdaten') <small class="float-right mt-1">Letztes Update: {{ $ground_vehicle->updated_at->diffForHumans() }}</small></h4>
            <div class="card-body">
                @component('components.forms.form')
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Name'),
                                'id' => 'name',
                                'value' => $ground_vehicle->name,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Hersteller'),
                                'id' => 'manufacturer',
                                'value' => $ground_vehicle->manufacturer->name_short,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('CIG ID'),
                                'id' => 'cig_id',
                                'value' => $ground_vehicle->cig_id,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Chassis ID'),
                                'id' => 'chassis_id',
                                'value' => $ground_vehicle->chassis_id,
                            ])@endcomponent
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Produktionsstatus'),
                                'id' => 'status',
                                'value' => $ground_vehicle->productionStatus->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Produktionsnotiz'),
                                'id' => 'note',
                                'value' => $ground_vehicle->productionNote->english()->translation,
                            ])@endcomponent
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Größe'),
                                'id' => 'size',
                                'value' => $ground_vehicle->size->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Typ'),
                                'id' => 'type',
                                'value' => $ground_vehicle->type->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Länge'),
                                'id' => 'length',
                                'value' => $ground_vehicle->length.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Breite'),
                                'id' => 'beam',
                                'value' => $ground_vehicle->beam.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Höhe'),
                                'id' => 'height',
                                'value' => $ground_vehicle->height.' m',
                            ])@endcomponent
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Frachtkapazität'),
                                'id' => 'cargo',
                                'value' => ($ground_vehicle->cargo_capacity ?? '-').' SCU',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Mass'),
                                'id' => 'mass',
                                'value' => $ground_vehicle->mass.' Kg',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Min. Besatzung'),
                                'id' => 'crew_min',
                                'value' => $ground_vehicle->min_crew,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Max. Besatzung'),
                                'id' => 'crew_max',
                                'value' => $ground_vehicle->max_crew,
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
                                    @forelse($ground_vehicle->foci as $focus){{--
                                        --}}{{ $focus->english()->translation }}, {{--
                                    --}}@empty{{--
                                        --}}-{{--
                                    --}}@endforelse
                                @endslot
                            @endcomponent
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('SCM Geschwindigkeit'),
                                'id' => 'scm',
                                'value' => $ground_vehicle->scm_speed.' m/s',
                            ])@endcomponent
                        </div>
                    </div>

                @endcomponent
            </div>
        </div>
        @component('components.forms.form', [
            'action' => route('web.admin.starcitizen.vehicles.ships.update', $ground_vehicle->getRouteKey()),
            'method' => 'PATCH',
            'class' => 'card h-100 d-flex flex-column justify-content-between'
        ])
            <div class="wrapper">
                <h4 class="card-header">@lang('Übersetzungen')</h4>
                <div class="card-body">
                    @foreach($ground_vehicle->translationsCollection() as $key => $translation)
                        @component('components.forms.form-group', [
                            'inputType' => 'textarea',
                            'label' => __('Beschreibung ').$key,
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
                <div class="card-footer">
                    <button class="btn btn-outline-success" name="save">@lang('Speichern')</button>
                </div>
            </div>
        @endcomponent
    </div>
@endsection
