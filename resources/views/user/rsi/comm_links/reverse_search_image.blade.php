@extends('user.layouts.default')

@section('title', __('Comm-Link Bild Rückwärtssuche'))

@section('head__content')
    @parent
    <style>
        .card-body p {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    @include('components.messages')

    <div class="card">
        <div class="card-header">
            <h4>
                {{ __('Comm-Link Rückwärtssuche Bildlink') }}
            </h4>
        </div>
        <div class="card-body">
            @component('components.forms.form', [
                'action' => route('web.user.rsi.comm-links.reverse-search-image.post'),
                'class' => 'mb-3',
            ])
                @component('components.forms.form-group', [
                    'inputType' => 'url',
                    'label' => __('Comm-Link Bild Url'),
                    'id' => 'src',
                ])
                    @slot('inputOptions')
                        pattern="http?s:\/\/(?:media\.)?robertsspaceindustries.com\/.*"
                        placeholder="https://robertsspaceindustries.com/media/..." required
                    @endslot
                    <small>URL der Form: <br>https://robertsspaceindustries.com/media/...<br>https://media.robertsspaceindustries.com/...</small>
                @endcomponent

                <button class="btn btn-block btn-outline-secondary">@lang('Suche')</button>
            @endcomponent
        </div>
    </div>

    <div class="card mt-5">
        <div class="card-header">
            <h4>
                {{ __('Comm-Link Rückwärtssuche Bild') }}
            </h4>
        </div>
        <div class="card-body">
            @component('components.forms.form', [
                'action' => route('web.user.rsi.comm-links.reverse-search-image.post'),
                'class' => 'mb-3',
                'enctype' => 'multipart/form-data'
            ])
                @component('components.forms.form-group', [
                        'inputType' => 'file',
                        'inputOptions' => 'required',
                        'label' => __('Comm-Link Bild'),
                        'id' => 'image',
                    ])
                @endcomponent

                <p>Genauigkeit:</p>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="similarity" id="similarity1" value="1">
                    <label class="form-check-label" for="similarity1">{{ __('Exakt') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="similarity" id="similarity2" value="10" checked>
                    <label class="form-check-label" for="similarity2">{{ __('Genau') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="similarity" id="similarity3" value="25">
                    <label class="form-check-label" for="similarity3">{{ __('Ungenau') }}</label>
                </div>

                <button class="btn btn-block btn-outline-secondary mt-3">@lang('Suche')</button>
            @endcomponent
        </div>
    </div>
@endsection
