@extends('user.layouts.default_wide')

@section('title', __('Transkript').' - '.$transcript->title ?? ''.' bearbeiten')

@section('content')
    <div class="d-flex mb-3">
        @unless(null === $prev)
            <a href="{{ route('web.user.transcripts.edit', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriges')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriges')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.user.transcripts.edit', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächstes')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled ml-auto">@lang('Nächstes')</a>
        @endunless
    </div>
    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.user.transcripts.update', $transcript->getRouteKey()),
        'class' => 'card mb-3',
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Transkript bearbeiten')</h4>
            <div class="card-body">
                @include('components.errors')
                @include('components.messages')
                <div class="row">
                    <div class="col-12 col-lg-12 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('YouTube URL'),
                            'id' => 'youtube_url',
                            'value' => $transcript->youtube_url,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-8 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Dauer'),
                            'id' => 'runtime',
                            'value' => gmdate('H:i:s', $transcript->runtime),
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-4 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'date',
                            'label' => __('Veröffentlichung'),
                            'id' => 'upload_date',
                            'value' => $transcript->upload_date ? $transcript->upload_date->format('Y-m-d') : $transcript->upload_date->format('Y-m-d'),
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-8 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Datei'),
                            'id' => 'file',
                            'value' => $transcript->filename,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Titel'),
                            'id' => 'title',
                            'value' => $transcript->title,
                        ])
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-8 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Playlist'),
                            'id' => 'playlist_name',
                            'value' => $transcript->playlist_name,
                        ])
                        @endcomponent
                    </div>

                </div>

                <div class="row">
                    <div class="col-12 col-lg-12 col-xl-2 mt-3">
                        <button class="btn btn-outline-secondary btn-block" name="save">@lang('Speichern')</button>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
    <div class="card">
        <h4 class="card-header">@lang('Inhalt')</h4>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-8 col-xl-9" id="description">
                    <h6>YouTube @lang('Beschreibung')</h6>
                    {!! empty($transcript->youtube_description) ? 'Nicht vorhanden' : nl2br($transcript->youtube_description) !!}
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>@lang('Transkript Englisch'):</h6>
                            {!! empty($transcript->english()->translation) ? 'Nicht vorhanden' : nl2br($transcript->english()->translation) !!}
                            <hr>
                            <h6>@lang('Transkript Deutsch'):</h6>
                            {!! empty($transcript->german()->translation) ? 'Nicht vorhanden' : nl2br($transcript->german()->translation) !!}
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 col-xl-3">
                    <img src="{{ $transcript->thumbnail }}" class="img-fluid" alt="thumbnail"/>
                </div>
            </div>
        </div>
    </div>
@endsection