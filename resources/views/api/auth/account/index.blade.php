@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', trans('auth/account/index.header'))

@section('content')
    <div class="card">
        <h4 class="card-header">Account</h4>

        <div class="card-body">
            <h6 class="card-title">Stammdaten:</h6>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'value' => $user->name,
                'label' => trans('auth/account/index.name'),
            ])@endcomponent

            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->email,
                        'label' => trans('auth/account/index.email'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => \App\Models\Notification::NOTIFICATION_LEVEL_TYPES[$user->receive_notification_level].' and up',
                        'label' => trans('auth/account/index.notification_level'),
                    ])@endcomponent
                </div>
            </div>
            <h6 class="card-title mt-5">API-Daten:</h6>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'value' => $user->api_token,
                'label' => trans('auth/account/index.api_key'),
            ])@endcomponent

            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->requests_per_minute,
                        'label' => trans('auth/account/index.requests_per_minute'),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $request_count,
                        'label' => 'Anfragen in der letzten Minute',
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