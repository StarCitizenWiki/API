@extends('admin.layouts.default')

{{-- Page Title --}}
@section('title', __('ShortUrl hinzufügen'))

@section('content')
    @include('components.errors')

    <div class="card">
        <h4 class="card-header">@lang('ShortUrl hinzufügen')</h4>
        <div class="card-body">
            @component('components.forms.form', ['action' => route('admin.url.add')])
                @component('components.forms.form-group', [
                    'id' => 'url',
                    'label' => __('Url'),
                    'inputType' => 'url',
                    'tabIndex' => 1,
                    'autofocus' => 1,
                    'required' => 1,
                    'value' => old('url'),
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                <div class="row">
                    <div class="col-12 col-md-6">
                        @component('components.forms.form-group', [
                            'id' => 'hash',
                            'label' => __('Hash'),
                            'tabIndex' => 2,
                            'value' => old('hash'),
                            'inputOptions' => 'data-minlength=3 spellcheck=false',
                        ])@endcomponent
                    </div>
                    <div class="col-12 col-md-6">
                        @component('components.forms.form-group', [
                            'id' => 'expired_at',
                            'label' => __('Ablaufdatum'),
                            'inputType' => 'datetime-local',
                            'tabIndex' => 3,
                            'value' => old('expired_at'),
                            'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
                        ])@endcomponent
                    </div>
                </div>

                <button class="btn btn-outline-success btn-block-xs-only float-right">@lang('Hinzufügen')</button>
            @endcomponent
        </div>
    </div>
@endsection
