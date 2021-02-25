@extends('user.layouts.default_wide')

@section('title')
    @lang('Raumschiff') {{ $ship->name}} @lang('bearbeiten')
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-xl-6">
            @component('user.components.card', [
                'class' => 'mb-4',
            ])
                @slot('title')
                    <h4 class="mb-0">@lang('Schiffsdaten')
                        <small class="float-right mt-1">Letztes Update: {{ $ship->updated_at->diffForHumans() }}</small>
                    </h4>
                @endslot
                @component('components.forms.form')
                    <div class="row">
                        <div class="col-8 col-md-8 col-lg-4 col-xl-8 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Name'),
                                'id' => 'name',
                                'value' => $ship->name,
                            ])@endcomponent
                        </div>
                        <div class="col-4 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Preis'),
                                'id' => 'msrp',
                                'value' => ($ship->msrp ?? '-').'$',

                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-2 col-xl-6 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Hersteller'),
                                'id' => 'manufacturer',
                                'value' => $ship->manufacturer->name_short,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('CIG ID'),
                                'id' => 'cig_id',
                                'value' => $ship->cig_id,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Chassis ID'),
                                'id' => 'chassis_id',
                                'value' => $ship->chassis_id,
                            ])@endcomponent
                        </div>

                        <div class="col-12 col-lg-6 col-xl-6 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Produktionsstatus'),
                                'id' => 'status',
                                'value' => optional($ship->productionStatus->german())->translation ?? optional($ship->productionStatus->english())->translation ?? '',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-6 col-xl-6 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Produktionsnotiz'),
                                'id' => 'note',
                                'value' => optional($ship->productionNote->german())->translation ?? optional($ship->productionNote->english())->translation ?? '',
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
                                'value' => optional($ship->size->german())->translation ?? optional($ship->size->english())->translation ?? '',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Typ'),
                                'id' => 'type',
                                'value' => optional($ship->type->german())->translation ?? optional($ship->type->english())->translation ?? '',
                            ])@endcomponent
                        </div>

                        <div class="col-12 col-md-4 col-lg-2 col-xl-4 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Länge'),
                                'id' => 'length',
                                'value' => $ship->length.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-2 col-xl-4 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Breite'),
                                'id' => 'beam',
                                'value' => $ship->beam.' m',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-2 col-xl-4 col-xxl-2">
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
                        <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Frachtkapazität'),
                                'id' => 'cargo',
                                'value' => ($ship->cargo_capacity ?? '-').' SCU',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Mass'),
                                'id' => 'mass',
                                'value' => $ship->mass.' Kg',
                            ])@endcomponent
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Min. Besatzung'),
                                'id' => 'crew_min',
                                'value' => $ship->min_crew,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 col-xl-6 col-xxl-3">
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
                        <div class="col-12 col-lg-6 col-xl-12">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Fokus'),
                                'id' => 'focus',
                            ])
                                @slot('value')
                                    {{
                                        $ship->foci->transform(function(\App\Models\StarCitizen\Vehicle\Focus\Focus $focus) {
                                            return optional($focus->german())->translation ?? optional($focus->english())->translation ?? __('Keiner');
                                        })->implode(', ')
                                    }}
                                @endslot
                            @endcomponent
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 col-xl-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('SCM Geschwindigkeit'),
                                'id' => 'scm',
                                'value' => $ship->scm_speed.' m/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-6 col-lg-5 col-xl-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Afterburner Geschwindigkeit'),
                                'id' => 'afterburner',
                                'value' => $ship->afterburner_speed.' m/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Steigung'),
                                'id' => 'pitch',
                                'value' => ($ship->pitch_max ?? '-').' deg/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Gierung'),
                                'id' => 'yaw',
                                'value' => ($ship->yaw_max ?? '-').' deg/s',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
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
                        <div class="col-12 col-lg-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('X-Achsen-Beschleunigung'),
                                'id' => 'x_axis_accel',
                                'value' => ($ship->x_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Y-Achsen-Beschleunigung'),
                                'id' => 'y_axis_accel',
                                'value' => ($ship->y_axis_acceleration ?? '-').' m/s²',
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-lg-4">
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
                'action' => route('web.user.starcitizen.vehicles.ships.update', $ship->getRouteKey()),
                'method' => 'PATCH',
                'class' => 'card'
            ])
                <div class="wrapper">
                    <h4 class="card-header">@lang('Übersetzungen')</h4>
                    <div class="card-body">
                        @include('components.errors')
                        @include('components.messages')
                        @foreach($ship->translationsCollection() as $key => $translation)
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
                        Schiff
                    @endcomponent
                </div>
            </div>
        </div>
    </div>
@endsection
