@extends('web.layouts.default_wide')

@section('title', __('Comm-Link').' - '.$commLink->title. ' ' . __('(bearbeiten)'))

@section('content')
    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.rsi.comm-links.update', $commLink->getRouteKey()),
        'class' => 'card',
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Comm-Link bearbeiten')</h4>
            <div class="card-body">
                @include('components.errors')
                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Titel'),
                            'id' => 'title',
                            'value' => $commLink->title,
                        ])
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-4 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'date',
                            'label' => __('Veröffentlichung'),
                            'id' => 'created_at',
                            'value' => $commLink->created_at->format('Y-m-d'),
                        ])
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('URL'),
                            'id' => 'url',
                            'value' => $commLink->url,
                        ])
                            @slot('inputOptions')
                                @if(null !== $commLink->url)
                                    readonly
                                @endif
                                placeholder="/comm-link/KATEGORIE/ID-TITEL"
                            @endslot
                        @endcomponent
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-12 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'select',
                            'label' => __('Channel'),
                            'id' => 'channel',
                        ])
                            @slot('selectOptions')
                                @foreach($channels as $channel)
                                    <option value="{{ $channel->id }}"
                                            @if($commLink->channel->name === $channel->name)
                                                selected
                                            @endif
                                    >{{ $channel->name }}</option>
                                @endforeach
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'select',
                            'label' => __('Kategorie'),
                            'id' => 'category',
                        ])
                            @slot('selectOptions')
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            @if($commLink->category->name === $category->name)
                                                selected
                                            @endif
                                    >{{ $category->name }}</option>
                                @endforeach
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'select',
                            'label' => __('Serie'),
                            'id' => 'series',
                        ])
                            @slot('selectOptions')
                                @foreach($series as $serie)
                                    <option value="{{ $serie->id }}"
                                            @if($commLink->series->name === $serie->name)
                                                selected
                                            @endif
                                    >{{ $serie->name }}</option>
                                @endforeach
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-2 mt-xl-4 pt-xl-2">
                        <button class="btn btn-outline-secondary ml-auto" name="save">@lang('Speichern')</button>
                    </div>
                </div>
                <hr>
                <nav class="mb-3">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-en_EN-tab" data-toggle="tab" href="#nav-en_EN" role="tab" aria-controls="nav-en_EN" aria-selected="true">
                            @lang('en_EN')
                        </a>
                        <a class="nav-item nav-link" id="nav-settings-tab" data-toggle="tab" href="#nav-settings" role="tab" aria-controls="nav-settings" aria-selected="false">
                            @lang('Einstellungen')
                        </a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tab-translations">
                    <div class="tab-pane fade show active" id="nav-en_EN" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                        <div class="form-group">
                            <textarea class="form-control-plaintext d-none" id="en_EN" name="en_EN">@lang('Platzhalter')</textarea>
                            {!! empty($commLink->english()->translation) ? __('Nicht vorhanden') : nl2br($commLink->english()->translation) !!}
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-settings" role="tabpanel" aria-labelledby="nav-settings-tab">
                        <div class="alert alert-warning text-center mb-3">
                            @lang('Achtung, durch das Klicken auf Speichern wird die ausgewählte Version des Comm-Links importiert!')
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-6 col-xl-3">
                                <div class="form-group">
                                    <label for="version">@lang('Importierte Version'):</label>
                                    <select class="form-control" id="version" name="version" disabled>
                                        @foreach($versions as $version)
                                            <option value="{{ $version['file'] }}" @if($version['file'] === $commLink->file) selected @endif>{{ $version['output'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6 col-xl-3">
                                <p class="mb-2">
                                    @lang('Vorhandene Versionen'):
                                </p>
                                @foreach($versions as $version)
                                    @unless(\Illuminate\Support\Str::startsWith($version['output'], 'Aktuell'))
                                        <a class="btn btn-block btn-outline-secondary" href="{{ route('web.rsi.comm-links.preview', [$commLink->getRouteKey(), $version['file_clean']]) }}">@lang('Vorschau Version vom') {{ $version['output'] }}</a>
                                    @endunless
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex">
                <a href="{{ route('web.rsi.comm-links.show', $commLink->getRouteKey()) }}" class="btn btn-outline-primary">@lang('Lesen')</a>
                <!--<button class="btn btn-outline-secondary ml-auto" name="changeVersion">@lang('Version ändern')</button>-->
            </div>
        </div>
    @endcomponent
@endsection