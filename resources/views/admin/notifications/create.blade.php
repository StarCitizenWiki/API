@extends('admin.layouts.default')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Benachrichtigung hinzufügen')</h4>
        <div class="card-body">
            @include('components.errors')
            @component('components.forms.form', [
                'action' => route('web.admin.notifications.create'),
            ])
                @component('components.forms.form-group', [
                    'inputType' => 'textarea',
                    'label' => __('Inhalt'),
                    'id' => 'content',
                    'rows' => 6,
                    'value' => old('content'),
                ])@endcomponent

                <div class="row">
                    <div class="col-12 col-lg-6">
                        @component('components.forms.form-group', [
                            'inputType' => 'select',
                            'inputClass' => 'custom-select w-100',
                            'label' => __('Typ'),
                            'id' => 'level',
                        ])
                            @slot('selectOptions')
                                <option value="0" @if(old('level') == 0) selected @endif>@lang('Info')</option>
                                <option value="1" @if(old('level') == 1) selected @endif>@lang('Warnung')</option>
                                <option value="2" @if(old('level') == 2) selected @endif>@lang('Fehler')</option>
                                <option value="3" @if(old('level') == 3) selected @endif>@lang('Kritisch')</option>
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-6">
                        @component('components.forms.form-group', [
                            'inputType' => 'datetime-local',
                            'label' => __('Ablaufdatum'),
                            'id' => 'expired_at',
                            'value' => old('expired_at'),
                            'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
                        ])@endcomponent
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-6">
                        @component('components.forms.form-group', [
                            'inputType' => 'number',
                            'label' => __('Reihenfolge'),
                            'id' => 'order',
                            'value' => old('order'),
                            'inputOptions' => 'min=0 max=5',
                        ])
                            <small class="help-block">@lang('Reihenfolge auf Startseite, Aufsteigend sortiert')</small>
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-6">
                        @component('components.forms.form-group', [
                            'inputType' => 'datetime-local',
                            'label' => __('Ausgabedatum'),
                            'id' => 'published_at',
                            'value' => old('published_at'),
                        ])@endcomponent
                    </div>
                </div>
                <div class="form-group">
                    <span class="d-block">@lang('Ausgabetyp'):</span>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="status" name="output[]" value="status"
                               @if(old('output_status')) checked @endif>
                        <label class="custom-control-label" for="status">@lang('Statusseite')</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="index" name="output[]" value="index"
                               @if(old('output_index')) checked @endif>
                        <label class="custom-control-label" for="index">@lang('Startseite')</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="email" name="output[]" value="email"
                               @if(old('output_email')) checked @endif>
                        <label class="custom-control-label" for="email">@lang('E-Mail')</label>
                    </div>
                </div>

                <button class="btn btn-outline-secondary float-right" name="save">@lang('Speichern')</button>
            @endcomponent
        </div>
    </div>
@endsection
