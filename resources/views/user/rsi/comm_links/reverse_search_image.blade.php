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
    <div class="card">
        <div class="card-header">
            <h4>
                {{ __('Comm-Link Rückwärtssuche') }}
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
                <button class="btn btn-block btn-outline-secondary">@lang('Search')</button>
            @endcomponent
        </div>
    </div>
@endsection
