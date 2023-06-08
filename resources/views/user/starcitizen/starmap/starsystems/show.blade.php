@extends('user.layouts.default_wide')

@section('title')
    @lang('Sternensystem') {{ $system->name}}
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-xl-6">
            @component('user.components.card', [
                'class' => 'mb-4',
            ])
                @slot('title')
                    <h4 class="mb-0">@lang('Sternensystemdaten')
                        <small class="float-right mt-1">@lang('Letztes Update'): {{ $system->time_modified->diffForHumans() }}</small>
                    </h4>
                @endslot
                @component('components.forms.form')
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Name'),
                                'id' => 'name',
                                'value' => $system->name,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Code'),
                                'id' => 'code',
                                'value' => $system->code,

                            ])@endcomponent
                        </div>

                        <div class="col-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('CIG ID'),
                                'id' => 'cig_id',
                                'value' => $system->cig_id,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Status'),
                                'id' => 'status',
                                'value' => $system->status,
                            ])@endcomponent
                        </div>

                        <div class="col-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Typ'),
                                'id' => 'type',
                                'value' => $system->type,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Größe'),
                                'id' => 'size',
                                'value' => $system->aggregated_size,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Bevölkerung'),
                                'id' => 'population',
                                'value' => $system->aggregated_population,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Wirtschaft'),
                                'id' => 'economy',
                                'value' => $system->aggregated_economy,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Gefahr'),
                                'id' => 'danger',
                                'value' => $system->aggregated_danger,
                            ])@endcomponent
                        </div>

                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Planeten'),
                                'id' => 'planets',
                                'value' => $system->planets_count,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Sterne'),
                                'id' => 'stars',
                                'value' => $system->stars_count,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Monde'),
                                'id' => 'moons',
                                'value' => $system->moons_count,
                            ])@endcomponent
                        </div>
                        <div class="col-12 col-md-4 col-lg-3 col-xl-3 col-xxl-2">
                            @component('components.forms.form-group', [
                                'inputType' => 'text',
                                'inputOptions' => 'readonly',
                                'label' => __('Raumstationen'),
                                'id' => 'stations',
                                'value' => $system->stations_count,
                            ])@endcomponent
                        </div>
                    </div>
                @endcomponent
            @endcomponent

            <div class="card">
                <h4 class="card-header">
                    <a data-toggle="collapse" href="#celestial_objects" role="button"
                       aria-expanded="false" aria-controls="celestial_objects">@lang('Himmelskörper')</a>
                </h4>
                <div class="card-body" id="celestial_objects">
                    <ul>
                    @foreach($system->celestialObjects as $obejct)
                        @include('user.starcitizen.starmap.components.celestial_object', ['object' => $obejct])
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            @component('components.forms.form', [
                'action' => route('web.user.starcitizen.vehicles.ships.update', $system->getRouteKey()),
                'method' => 'PATCH',
                'class' => 'card'
            ])
                <div class="wrapper">
                    <h4 class="card-header">@lang('Übersetzungen')</h4>
                    <div class="card-body">
                        @include('components.errors')
                        @include('components.messages')
                        @foreach($system->translationsCollection() as $key => $translation)
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
        </div>
    </div>
@endsection