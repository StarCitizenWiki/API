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
                        'value' => $user->name,
                        'label' => __('Projekt / Organisation / Name'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->email,
                        'label' => __('E-Mail'),
                    ])@endcomponent
                </div>
            </div>

            <h6 class="card-title mt-5">API-Daten:</h6>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'value' => $user->api_token,
                'label' => __('Api Key'),
            ])@endcomponent
            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => __($notification_level_text),
                        'label' => __('Benachrichtigungslevel'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->requests_per_minute,
                        'label' => __('Anfragen pro Minute'),
                    ])@endcomponent
                </div>
            </div>
        </div>
    </div>
@endsection