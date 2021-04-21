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
                        'label' => __('Comm-Link Bild Url'),
                        'id' => 'url',
                    ])
                        @slot('inputOptions')
                            pattern="http?s:\/\/(?:media\.)?robertsspaceindustries.com\/.*"
                            placeholder="https://robertsspaceindustries.com/media/..." required
                        @endslot
                        <small>URL der Form: <br>https://robertsspaceindustries.com/media/...<br>https://media.robertsspaceindustries.com/...</small>
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
                        <small>Text, welcher in einem Comm-Link vorkommt</small>
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

                    <p>Genauigkeit:</p>
                    <div class="flex-row">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity1" value="1">
                            <label class="form-check-label" for="similarity1">{{ __('Exakt') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity2" value="5" checked>
                            <label class="form-check-label" for="similarity2">{{ __('Sehr ähnlich') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity3" value="10">
                            <label class="form-check-label" for="similarity3">{{ __('Ähnlich') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity4" value="18">
                            <label class="form-check-label" for="similarity4">{{ __('Ungenau') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity5" value="25">
                            <label class="form-check-label" for="similarity5">{{ __('Sehr ungenau') }}</label>
                        </div>
                    </div>


                    <p class="mt-3">
                        <a data-toggle="collapse" href="#method" role="button" aria-expanded="false" aria-controls="method">
                            Methode:
                        </a>
                    </p>

                    <div class="collapse mb-3" id="method">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="method" id="method1" value="perceptual" checked>
                            <label class="form-check-label" for="method1">{{ __('Wahrnehmung') }} &mdash; Hash basierend auf Merkmalen des Inhalts</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="method" id="method2" value="difference">
                            <label class="form-check-label" for="method2">{{ __('Differenz') }} &mdash; Hash basierend auf dem vorherigen Pixel</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="method" id="method3" value="average">
                            <label class="form-check-label" for="method3">{{ __('Durchschnitt') }} &mdash; Hash basierend auf der durchschnittlichen Bildfarbe</label>
                        </div>
                    </div>

                    <button class="btn btn-block btn-outline-secondary mt-auto">@lang('Suche nach Comm-Links mit Bild')</button>
                @endcomponent
            </div>
        </div>
    </div>
@endsection
