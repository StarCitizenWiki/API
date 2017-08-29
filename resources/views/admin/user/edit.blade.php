@extends('admin.layouts.default_wide')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <div class="card">
                <h4 class="card-header">@lang('Benutzer bearbeiten') @lang('ID'): {{ $user->id }}</h4>
                <div class="card-body">
                    @component('components.forms.form', [
                        'action' => route('admin_user_update', $user->getRouteKey()),
                        'method' => 'PATCH',
                    ])
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                @component('components.forms.form-group', [
                                    'label' => __('ID'),
                                    'id' => 'id',
                                    'inputOptions' => 'disabled',
                                    'value' => $user->id,
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-lg-3">
                                @component('components.forms.form-group', [
                                    'label' => __('Hash ID'),
                                    'id' => 'hash_id',
                                    'inputOptions' => 'disabled',
                                    'value' => $user->getRouteKey(),
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-lg-6">
                                @component('components.forms.form-group', [
                                    'label' => __('Name'),
                                    'id' => 'name',
                                    'value' => $user->name,
                                ])@endcomponent
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                @component('components.forms.form-group', [
                                    'inputType' => 'email',
                                    'label' => __('E-Mail'),
                                    'id' => 'email',
                                    'value' => $user->email,
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-lg-6">
                                @component('components.forms.form-group', [
                                    'inputType' => 'select',
                                    'label' => __('Status'),
                                    'id' => 'state',
                                    'inputClass' => 'custom-select w-100',
                                ])
                                    @slot('selectOptions')
                                        <option>Normal</option>
                                        <option>Unlimitiert</option>
                                        <option>Gesperrt</option>
                                    @endslot
                                @endcomponent
                            </div>
                        </div>
                        @component('components.forms.form-group', [
                            'label' => __('Api Key'),
                            'id' => 'api_token',
                            'value' => $user->api_token,
                        ])@endcomponent
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'integer',
                                    'label' => __('Anfragen pro Minute'),
                                    'id' => 'requests_per_minute',
                                    'value' => $user->requests_per_minute,
                                ])@endcomponent
                            </div>
                            <div class="col-12 col-lg-3">
                                @component('components.forms.form-group', [
                                    'inputType' => 'integer',
                                    'label' => __('In der letzten Minute'),
                                    'id' => 'requests_per_minute',
                                    'inputOptions' => 'disabled',
                                    'value' => $user->requests_per_minute,
                                ])@endcomponent
                            </div>
                        </div>

                        @if($user->trashed())
                            <button class="btn btn-outline-success" name="restore">@lang('Wiederherstellen')</button>
                        @else
                            <button class="btn btn-outline-danger" name="delete">@lang('Löschen')</button>
                        @endif
                        <button class="btn btn-outline-secondary pull-right" name="save">@lang('Speichern')</button>
                    @endcomponent
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">

        </div>
    </div>
    {{ $user }}
@endsection