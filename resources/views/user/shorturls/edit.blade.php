@extends('user.layouts.default')

@section('title', __('ShortUrl bearbeiten'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('ShortUrl bearbeiten')</h4>
        <div class="card-body">
            @include('components.errors')

            @component('components.forms.form', [
                'method' => 'PATCH',
                'action' => route('account_url_update', $url->getRouteKey()),
            ])
                @component('components.forms.form-group', [
                    'inputType' => 'url',
                    'label' => __('Url'),
                    'id' => 'url',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => $url->url,
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'label' => __('Hash'),
                    'id' => 'hash',
                    'required' => 1,
                    'value' => $url->hash,
                    'tabIndex' => 2,
                    'inputOptions' => 'data-minlength=3 spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'datettime-local',
                    'label' => __('Ablaufdatum'),
                    'id' => 'expired_at',
                    'tabIndex' => 3,
                    'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
                ])
                    @unless(is_null($url->expired_at))
                        @slot('value')
                            {{ $url->expired_at->format("Y-m-d\TH:i") }}
                        @endslot
                    @endunless
                @endcomponent

                <button class="btn btn-outline-danger" name="delete">@lang('Löschen')</button>
                <button class="btn btn-outline-secondary float-right" name="save">@lang('Speichern')</button>
            @endcomponent
        </div>
    </div>
@endsection