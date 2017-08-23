@extends('admin.layouts.default')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('ShortUrl bearbeiten'): {{ $url->hash }}</h4>
        <div class="card-body">
            @component('components.forms.form', [
                'method' => 'PATCH',
                'action' => route('admin_urls_update', $url->getRouteKey()),
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

                <div class="row">
                    <div class="col-12 col-md-6">
                        @component('components.forms.form-group', [
                            'label' => __('Hash'),
                            'id' => 'hash',
                            'required' => 1,
                            'value' => $url->hash,
                            'tabIndex' => 2,
                            'inputOptions' => 'spellcheck=false',
                        ])@endcomponent
                    </div>
                    <div class="col-12 col-md-6">
                        @component('admin.components.user_dropdown', [
                            'label' => __('Benutzer'),
                            'required' => 1,
                            'tabIndex' => 3,
                            'selectedID' => $url->user_id,
                        ])@endcomponent
                    </div>
                </div>
                @component('components.forms.form-group', [
                    'inputType' => 'dateTime-local',
                    'label' => __('Ablaufdatum'),
                    'id' => 'expired_at',
                    'tabIndex' => 4,
                    'inputOptions' => 'spellcheck=false',
                ])
                    @unless(is_null($url->expired_at))
                        @slot('value')
                            {{ $url->expired_at->format("Y-m-d\TH:i:s") }}
                        @endslot
                    @endunless
                @endcomponent

                <button class="btn btn-outline-secondary" name="save">@lang('Speichern')</button>
                @if($url->trashed())
                    <button class="btn btn-outline-success pull-right" name="restore">@lang('Wiederherstellen')</button>
                @else
                    <button class="btn btn-outline-danger pull-right" name="delete">@lang('Löschen')</button>
                @endif
            @endcomponent
        </div>
    </div>
@endsection