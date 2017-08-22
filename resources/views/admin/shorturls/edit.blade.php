@extends('admin.layouts.default')

@section('content')
    <div class="card">
        <h4 class="card-header">__LOC__Edit ShortUrl: {{ $url->hash }}</h4>
        <div class="card-body">
            @component('components.forms.form', [
                'method' => 'PATCH',
                'action' => route('admin_urls_update', $url->getRouteKey()),
            ])
                @component('components.forms.form-group', [
                    'inputType' => 'url',
                    'label' => '__LOC__Url',
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
                            'label' => '__LOC__Hash',
                            'id' => 'hash',
                            'required' => 1,
                            'value' => $url->hash,
                            'tabIndex' => 2,
                            'inputOptions' => 'spellcheck=false',
                        ])@endcomponent
                    </div>
                    <div class="col-12 col-md-6">
                        @component('admin.components.user_dropdown', [
                            'label' => '__LOC__User',
                            'required' => 1,
                            'tabIndex' => 3,
                            'selectedID' => $url->user_id,
                        ])@endcomponent
                    </div>
                </div>
                @component('components.forms.form-group', [
                    'inputType' => 'dateTime-local',
                    'label' => '__LOC__expired_at',
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

                <button class="btn btn-outline-secondary" name="save">__LOC__Speichern</button>
                @if($url->trashed())
                    <button class="btn btn-outline-success pull-right" name="restore">__LOC__Restore</button>
                @else
                    <button class="btn btn-outline-danger pull-right" name="delete">__LOC__Löschen</button>
                @endif
            @endcomponent
        </div>
    </div>
@endsection