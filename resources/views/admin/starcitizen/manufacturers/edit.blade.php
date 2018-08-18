@extends('admin.layouts.default_wide')

@section('content')
    <div class="card-deck">
        @component('admin.components.card', [
            'class' => 'mb-4',
        ])
            @slot('title')
                <h4 class="mb-0">@lang('Herstellerdaten') <small class="float-right mt-1">Letztes Update: {{ $manufacturer->updated_at->diffForHumans() }}</small></h4>
            @endslot
            @component('components.forms.form')
                <div class="row">
                    <div class="col-12 col-lg-3">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'inputOptions' => 'readonly',
                            'label' => __('Code'),
                            'id' => 'code',
                            'value' => $manufacturer->name_short,
                        ])@endcomponent
                    </div>
                    <div class="col-12 col-lg-6">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'inputOptions' => 'readonly',
                            'label' => __('Name'),
                            'id' => 'name',
                            'value' => $manufacturer->name,
                        ])@endcomponent
                    </div>
                    <div class="col-12 col-lg-3">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'inputOptions' => 'readonly',
                            'label' => __('CIG ID'),
                            'id' => 'cig_id',
                            'value' => $manufacturer->cig_id,
                        ])@endcomponent
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-6">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'inputOptions' => 'readonly',
                            'label' => __('Raumschiffe'),
                            'id' => 'ships',
                            'value' => count($manufacturer->ships),
                        ])@endcomponent
                    </div>
                    <div class="col-12 col-lg-6">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'inputOptions' => 'readonly',
                            'label' => __('Fahrzeuge'),
                            'id' => 'vehicles',
                            'value' => count($manufacturer->groundVehicles),
                        ])@endcomponent
                    </div>
                </div>
            @endcomponent
        @endcomponent

        @component('components.forms.form', [
            'action' => route('web.admin.starcitizen.manufacturers.update', $manufacturer->getRouteKey()),
            'method' => 'PATCH',
            'class' => 'card h-100 d-flex flex-column justify-content-between'
        ])
            <div class="wrapper">
                <h4 class="card-header">@lang('Übersetzungen')</h4>
                <div class="card-body">
                    @include('components.errors')
                    @include('components.messages')
                    @foreach($manufacturer->translationsCollection() as $key => $translation)
                        @component('components.forms.form-group', [
                            'inputType' => 'textarea',
                            'label' => __('Beschreibung ').__($key),
                            'id' => "description_{$key}",
                            'rows' => 6,
                            'value' => $translation->description,
                        ])
                            @slot('inputOptions')
                                @if($key === config('language.english'))
                                    readonly
                                @endif
                            @endslot
                        @endcomponent
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Bekannt für ').__($key),
                            'id' => "known_for_{$key}",
                            'rows' => 6,
                            'value' => $translation->known_for,
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
