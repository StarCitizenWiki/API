@extends('admin.layouts.default_wide')

@section('content')
    <div class="card-deck">
        <div class="card">
            <h4 class="card-header">@lang('Schiffsdaten')</h4>
            <div class="card-body">
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
                                'label' => __('Production Status'),
                                'id' => 'status',
                                'value' => $ship->productionStatus->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Production Note'),
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
                                'label' => __('Size'),
                                'id' => 'size',
                                'value' => $ship->size->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Type'),
                                'id' => 'type',
                                'value' => $ship->type->english()->translation,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Length'),
                                'id' => 'length',
                                'value' => $ship->length.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Beam'),
                                'id' => 'beam',
                                'value' => $ship->beam.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Height'),
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
                                'label' => __('Cargo Capacity'),
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
                                'label' => __('Crew Min'),
                                'id' => 'crew_min',
                                'value' => $ship->min_crew,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Crew Max'),
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
                                'label' => __('SCM Speed'),
                                'id' => 'scm',
                                'value' => $ship->scm_speed.' m/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Afterburner Speed'),
                                'id' => 'afterburner',
                                'value' => $ship->afterburner_speed.' m/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Pitch'),
                                'id' => 'pitch',
                                'value' => ($ship->pitch_max ?? '-').' deg/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Yaw'),
                                'id' => 'yaw',
                                'value' => ($ship->yaw_max ?? '-').' deg/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Roll'),
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
                                'label' => __('X-Axis Acceleration'),
                                'id' => 'x_axis_accel',
                                'value' => ($ship->x_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Y-Axis Acceleration'),
                                'id' => 'y_axis_accel',
                                'value' => ($ship->y_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Z-Axis Acceleration'),
                                'id' => 'z_axis_accel',
                                'value' => ($ship->z_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                    </div>

                @endcomponent
            </div>
        </div>
        @component('components.forms.form', [
            'action' => route('web.admin.starcitizen.vehicles.ships.update', $size->id),
            'method' => 'PATCH',
        ])
            <div class="card">
                <h4 class="card-header">@lang('Übersetzungen')</h4>
                <div class="card-body">
                    @foreach($ship->descriptionsCollection() as $key => $translation)
                        @component('components.forms.form')
                            @component('components.forms.form-group', [
                                'inputType' => 'textarea',
                                'label' => __('Beschreibung ').$key,
                                'id' => 'translation_'.$key,
                                'rows' => 6,
                                'value' => $translation->translation,
                            ])
                                @slot('inputOptions')
                                    @if($key === config('language.english'))
                                        readonly
                                    @endif
                                @endslot
                            @endcomponent
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
