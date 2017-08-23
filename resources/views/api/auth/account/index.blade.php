@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', __('Account'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Account')</h4>

        <div class="card-body">
            <h6 class="card-title">@lang('Stammdaten'):</h6>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'value' => $user->name,
                'label' => __('Projekt / Organisation / Name'),
            ])@endcomponent

            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->email,
                        'label' => __('E-Mail'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => \App\Models\Notification::NOTIFICATION_LEVEL_TYPES[$user->receive_notification_level].' and up',
                        'label' => __('Benachrichtigungslevel'),
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
                        'value' => $user->requests_per_minute,
                        'label' => __('Anfragen pro Minute'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $request_count,
                        'label' => __('Anfragen in der letzten Minute'),
                    ])
                        @slot('inputClass')
                            <?php $perc = round(($request_count / $user->requests_per_minute) * 100); ?>
                            @if($perc > 80)
                                form-control border-danger text-danger
                            @elseif($perc >= 50)
                                form-control border-warning text-warning
                            @else
                                form-control border-success text-success
                            @endif
                        @endslot
                    @endcomponent
                </div>
            </div>
        </div>
    </div>
@endsection