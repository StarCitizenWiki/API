@extends('admin.layouts.default_wide')

@section('content')
    <div class="card-deck">
        @component('admin.components.card', [
            'class' => 'mb-4',
        ])
            @slot('title')
                <h4 class="mb-0">@lang('Schiffsdaten') <small class="float-right mt-1">Letztes Update: {{ $ship->updated_at->diffForHumans() }}</small></h4>
            @endslot
            @component('components.forms.form')
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Name'),
                                'id' => 'name',
                                'value' => $ship->name,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Hersteller'),
                                'id' => 'manufacturer',
                                'value' => $ship->manufacturer->name_short,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('CIG ID'),
                                'id' => 'cig_id',
                                'value' => $ship->cig_id,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Chassis ID'),
                                'id' => 'chassis_id',
                                'value' => $ship->chassis_id,
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
                                'value' => $ship->productionStatus->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Produktionsnotiz'),
                                'id' => 'note',
                                'value' => $ship->productionNote->english()->translation,
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
                                'value' => $ship->size->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Typ'),
                                'id' => 'type',
                                'value' => $ship->type->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Länge'),
                                'id' => 'length',
                                'value' => $ship->length.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Breite'),
                                'id' => 'beam',
                                'value' => $ship->beam.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Höhe'),
                                'id' => 'height',
                                'value' => $ship->height.' m',
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
                                'value' => ($ship->cargo_capacity ?? '-').' SCU',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Mass'),
                                'id' => 'mass',
                                'value' => $ship->mass.' Kg',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Min. Besatzung'),
                                'id' => 'crew_min',
                                'value' => $ship->min_crew,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Max. Besatzung'),
                                'id' => 'crew_max',
                                'value' => $ship->max_crew,
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
                                    @forelse($ship->foci as $focus){{--
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
                                'value' => $ship->scm_speed.' m/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Afterburner Geschwindigkeit'),
                                'id' => 'afterburner',
                                'value' => $ship->afterburner_speed.' m/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Steigung'),
                                'id' => 'pitch',
                                'value' => ($ship->pitch_max ?? '-').' deg/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Gierung'),
                                'id' => 'yaw',
                                'value' => ($ship->yaw_max ?? '-').' deg/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Rotation'),
                                'id' => 'roll',
                                'value' => ($ship->roll_max ?? '-').' deg/s',
                            ])@endcomponent
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('X-Achsen-Beschleunigung'),
                                'id' => 'x_axis_accel',
                                'value' => ($ship->x_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Y-Achsen-Beschleunigung'),
                                'id' => 'y_axis_accel',
                                'value' => ($ship->y_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Z-Achsen-Beschleunigung'),
                                'id' => 'z_axis_accel',
                                'value' => ($ship->z_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                    </div>

                @endcomponent
        @endcomponent

        @component('components.forms.form', [
            'action' => route('web.admin.starcitizen.vehicles.ships.update', $ship->getRouteKey()),
            'method' => 'PATCH',
            'class' => 'card h-100 d-flex flex-column justify-content-between'
        ])
            <div class="wrapper">
                <h4 class="card-header">@lang('Übersetzungen')</h4>
                <div class="card-body">
                    @include('components.errors')
                    @include('components.messages')
                    @foreach($ship->translationsCollection() as $key => $translation)
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
                <div class="card-footer d-flex">
                    <button class="btn btn-outline-secondary ml-auto" name="save">@lang('Speichern')</button>
                </div>
            </div>
        @endcomponent
    </div>
@endsection
