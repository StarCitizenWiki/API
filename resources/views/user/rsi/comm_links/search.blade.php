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
                    {{ __('Suche nach Comm-Links mit Medienurl') }}
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

                    <button class="btn btn-block btn-outline-secondary mt-auto">@lang('Suche')</button>
                @endcomponent
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    {{ __('Suche nach Comm-Link mit Bild') }}
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
                            <input class="form-check-input" type="radio" name="similarity" id="similarity1" value="95">
                            <label class="form-check-label" for="similarity1" title=">= 95% @lang('Ähnlichkeit')">@lang('Exakt')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity2" value="80" checked>
                            <label class="form-check-label" for="similarity2" title=">= 80% @lang('Ähnlichkeit')">@lang('Sehr ähnlich')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity3" value="60">
                            <label class="form-check-label" for="similarity3" title=">= 60% @lang('Ähnlichkeit')">@lang('Ähnlich')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity4" value="50">
                            <label class="form-check-label" for="similarity4" title=">= 50% @lang('Ähnlichkeit')">@lang('Ungenau')</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="similarity" id="similarity5" value="25">
                            <label class="form-check-label" for="similarity5" title=">= 25% @lang('Ähnlichkeit')">@lang('Sehr ungenau')</label>
                        </div>
                    </div>
                    <button class="btn btn-block btn-outline-secondary mt-3">@lang('Suche')</button>
                @endcomponent
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4>
                    {{ __('Suche nach Comm-Link Medien') }}
                </h4>
            </div>
            <div class="card-body">
                @component('components.forms.form', [
                    'action' => route('web.user.rsi.comm-links.images.search'),
                    'class' => 'd-flex h-100 flex-column',
                ])
                    @component('components.forms.form-group', [
                        'inputType' => 'text',
                        'label' => __('Dateiname'),
                        'id' => 'query',
                        'placeholder' => 'Carrack',
                        'required' => true
                    ])
                    @endcomponent

                    <button class="btn btn-block btn-outline-secondary mt-auto">@lang('Suche')</button>
                @endcomponent
            </div>
        </div>
    </div>
@endsection
