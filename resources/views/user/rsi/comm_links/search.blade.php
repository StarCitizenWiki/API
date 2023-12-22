@extends('user.layouts.default_wide')

@section('title', __('Comm-Link Rückwärtssuche'))

@section('head__content')
    @parent
    <style>
        .card-body p {
            margin-bottom: 0;
        }

        @media (max-width: 1200px) {
            .card-deck {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')

    @include('components.messages')

    @include('components.errors')

    <div id="cl-live-search"><comm-link-live-search api-token="{{ $apiToken }}"></comm-link-live-search></div>

    <div class="card-deck">
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    {{ __('Comm-Link Bildlinksuche') }}
                </h4>
            </div>
            <div class="card-body">
                @component('components.forms.form', [
                    'action' => route('web.user.rsi.comm-links.reverse-image-link-search.post'),
                    'class' => 'd-flex h-100 flex-column',
                ])
                    @component('components.forms.form-group', [
                        'inputType' => 'url',
                        'label' => __('Comm-Link Bild URL'),
                        'id' => 'url',
                    ])
                        @slot('inputOptions')
                            pattern="http?s:\/\/(?:media\.)?robertsspaceindustries.com\/.*"
                            placeholder="https://robertsspaceindustries.com/media/..." required
                        @endslot
                        <small>@lang('URL der Form'): <br>https://robertsspaceindustries.com/media/...<br>https://media.robertsspaceindustries.com/...</small>
                    @endcomponent

                    <button class="btn btn-block btn-outline-secondary mt-auto">@lang('Suche nach Comm-Links mit Bildlink')</button>
                @endcomponent
            </div>
        </div>

        @auth
        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    {{ __('Comm-Link Inhalt') }}
                </h4>
            </div>
            <div class="card-body">
                @component('components.forms.form', [
                    'action' => route('web.user.rsi.comm-links.image-text-search.post'),
                    'class' => 'd-flex h-100 flex-column',
                ])
                    @component('components.forms.form-group', [
                        'inputType' => 'text',
                        'label' => __('Comm-Link Inhalt'),
                        'id' => 'query',
                        'inputOptions' => 'required',
                    ])
                        <small>@lang('Text, welcher in einem Comm-Link vorkommt')</small>
                    @endcomponent

                    <button class="btn btn-block btn-outline-secondary mt-auto">@lang('Suche nach Bildern')</button>
                @endcomponent
            </div>
        </div>
        @endauth

        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    {{ __('Comm-Link Bildsuche') }}
                </h4>
            </div>
            <div class="card-body">
                @component('components.forms.form', [
                    'action' => route('web.user.rsi.comm-links.reverse-image-search.post'),
                    'class' => 'd-flex h-100 flex-column',
                    'enctype' => 'multipart/form-data'
                ])
                    @component('components.forms.form-group', [
                            'inputType' => 'file',
                            'inputOptions' => 'required',
                            'label' => __('Comm-Link Bild'),
                            'id' => 'image',
                        ])
                    @endcomponent

                    <p>@lang('Genauigkeit'):</p>
                    <div class="flex-row">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity1" value="5">
                            <label class="form-check-label" for="similarity1" title="5%">@lang('Exakt')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity2" value="20" checked>
                            <label class="form-check-label" for="similarity2" title="20%">@lang('Sehr ähnlich')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity3" value="40">
                            <label class="form-check-label" for="similarity3" title="40%">@lang('Ähnlich')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity4" value="50">
                            <label class="form-check-label" for="similarity4" title="50%">@lang('Ungenau')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity5" value="75">
                            <label class="form-check-label" for="similarity5" title="75%">@lang('Sehr ungenau')</label>
                        </div>
                    </div>


                    <button class="btn btn-block btn-outline-secondary mt-auto">@lang('Suche nach Comm-Links mit Bild')</button>
                @endcomponent
            </div>
        </div>
    </div>
@endsection
