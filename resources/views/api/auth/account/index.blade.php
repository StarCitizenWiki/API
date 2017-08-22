@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', '__LOC__Account')

@section('content')
    <div class="card">
        <h4 class="card-header">__LOC__Account</h4>

        <div class="card-body">
            <h6 class="card-title">__LOC__Stammdaten:</h6>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'value' => $user->name,
                'label' => '__LOC__Name',
            ])@endcomponent

            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->email,
                        'label' => '__LOC__Email',
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => \App\Models\Notification::NOTIFICATION_LEVEL_TYPES[$user->receive_notification_level].' and up',
                        'label' => '__LOC__Notification_Level',
                    ])@endcomponent
                </div>
            </div>
            <h6 class="card-title mt-5">API-Daten:</h6>
            @component('components.forms.form-group', [
                'id' => '',
                'inputOptions' => 'readonly',
                'value' => $user->api_token,
                'label' => '__LOC__Api_Key',
            ])@endcomponent

            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $user->requests_per_minute,
                        'label' => '__LOC__Requests_per_minute',
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'id' => '',
                        'inputOptions' => 'readonly',
                        'value' => $request_count,
                        'label' => '__LOC__Anfragen in der letzten Minute',
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