@extends('admin.layouts.default')

@section('content')
    @component('admin.components.card', [
        'class' => 'mb-4',
        'icon' => 'user',
    ])
        @slot('title')
            #@lang('Benutzer bearbeiten')
        @endslot
        @include('components.errors')
        @component('components.forms.form', [
            'action' => route('web.admin.users.update', $user->getRouteKey()),
            'method' => 'PATCH',
        ])
            <div class="row">
                <div class="col-12 col-lg-3">
                    @component('components.forms.form-group', [
                        'label' => __('ID'),
                        'id' => 'id',
                        'inputOptions' => 'disabled',
                        'value' => $user->getRouteKey(),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-9">
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
                            <option value="0">@lang('Normal')</option>
                            <option value="1">@lang('Unlimitiert')</option>
                            <option value="2">@lang('Gesperrt')</option>
                        @endslot
                    @endcomponent
                </div>
            </div>
            @component('components.forms.form-group', [
                'label' => __('Api Key'),
                'id' => 'api_token',
                'inputOptions' => 'disabled',
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
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'label' => __('Passwort ändern'),
                        'id' => 'password',
                    ])@endcomponent
                </div>
            </div>
            @component('components.forms.form-group', [
                'inputType' => 'textarea',
                'label' => __('Notiz'),
                'id' => 'notes',
                'value' => $user->notes,
            ])@endcomponent


            @if($user->trashed())
                <button class="btn btn-outline-success" name="restore">@lang('Wiederherstellen')</button>
            @else
                <button class="btn btn-outline-danger" name="delete">@lang('Löschen')</button>
            @endif
            <button class="btn btn-outline-secondary float-right" name="save">@lang('Speichern')</button>
        @endcomponent
    @endcomponent
@endsection