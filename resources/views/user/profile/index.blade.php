@extends('user.layouts.default')

{{-- Page Title --}}
@section('title', __('Account'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Account')</h4>

        <div class="card-body">
            <h6 class="card-title">@lang('Stammdaten'):</h6>
            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->username,
                        'label' => __('Benutzername'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->email ?? __('Nicht vorhanden'),
                        'label' => __('E-Mail'),
                    ])@endcomponent
                </div>
            </div>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'label' => __('Gruppen'),
            ])
                @slot('value')
                    {{ $user->groups->map(function($group) { return __($group->name); })->implode('name', ', ') }}
                @endslot
            @endcomponent

            <h6 class="card-title mt-5">API-Daten:</h6>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'value' => $user->api_token,
                'label' => __('Api Key'),
            ])@endcomponent

            <h6 class="card-title mt-5">Einstellungen:</h6>
        </div>
    </div>
@endsection
