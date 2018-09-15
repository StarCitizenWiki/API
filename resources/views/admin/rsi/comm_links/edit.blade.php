@extends('admin.layouts.default_wide')

@section('title', __('Comm Link').' - '.$commLink->title.' bearbeiten')

@section('content')
    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.admin.rsi.comm-links.update', $commLink->getRouteKey()),
        'class' => 'card',
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Comm Link bearbeiten')</h4>
            <div class="card-body">
                @include('components.errors')
                <div class="row">
                    <div class="col-12 col-lg-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Titel'),
                            'id' => 'title',
                            'value' => $commLink->title,
                        ])
                            @slot('inputOptions')
                                @if(strlen($commLink->title) > 4)
                                    readonly
                                @endif
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'date',
                            'label' => __('Veröffentlichung'),
                            'id' => 'created_at',
                            'value' => $commLink->created_at->format("Y-m-d"),
                        ])
                            @slot('inputOptions')
                                @if(!$commLink->created_at->eq('2012-01-01 00:00:00'))
                                    readonly
                                @endif
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-4">
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
                <hr>
                <nav class="mb-3">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-en_EN-tab" data-toggle="tab" href="#nav-en_EN" role="tab" aria-controls="nav-en_EN" aria-selected="true">
                            @lang('en_EN')
                        </a>
                        <a class="nav-item nav-link" id="nav-de_DE-tab" data-toggle="tab" href="#nav-de_DE" role="tab" aria-controls="nav-de_DE" aria-selected="false">
                            @lang('de_DE')
                        </a>
                        @can('web.admin.rsi.comm-links.update_settings')
                        <a class="nav-item nav-link" id="nav-settings-tab" data-toggle="tab" href="#nav-settings" role="tab" aria-controls="nav-settings" aria-selected="false">
                            @lang('Einstellungen')
                        </a>
                        @endcan
                    </div>
                </nav>
                <div class="tab-content" id="nav-tab-translations">
                    <div class="tab-pane fade show active" id="nav-en_EN" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                        <div class="form-group">
                            <textarea class="form-control-plaintext d-none" id="en_EN" name="en_EN">Placeholder</textarea>
                            {!! empty($commLink->english()->translation) ? 'Nicht vorhanden' : $commLink->english()->translation !!}
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-de_DE" role="tabpanel" aria-labelledby="nav-de_DE-tab">
                        <div class="form-group">
                            <textarea class="form-control edit-content" name="de_DE" id="de_DE">{!! old('de_DE') ?? optional($commLink->german())->translation !!}</textarea>
                        </div>
                    </div>
                    @can('web.admin.rsi.comm-links.update_settings')
                    <div class="tab-pane fade" id="nav-settings" role="tabpanel" aria-labelledby="nav-settings-tab">
                        <div class="alert alert-warning text-center mb-3">
                            @lang('Achtung, durch das Klicken auf Speichern wird die ausgewählte Version des Comm Links importiert!')
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="version">Importierte Version:</label>
                                    <select class="form-control" id="version" name="version">
                                        @foreach($versions as $version)
                                            <option value="{{ $version['file'] }}" @if($version['file'] === $commLink->file) selected @endif>{{ $version['output'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                @foreach($versions as $version)
                                    @unless(starts_with($version['output'], 'Aktuell'))
                                        <a class="btn btn-block btn-outline-secondary" href="{{ route('web.admin.rsi.comm-links.preview', [$commLink->getRouteKey(), $version['file_clean']]) }}">Vorschau Version vom {{ $version['output'] }}</a>
                                    @endunless
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="card-footer d-flex">
                <a href="{{ route('web.admin.rsi.comm-links.show', $commLink->getRouteKey()) }}" class="btn btn-outline-primary">@lang('Lesen')</a>
                <button class="btn btn-outline-secondary ml-auto" name="save">@lang('Speichern')</button>
            </div>
        </div>
    @endcomponent
@endsection

@section('body__after')
    @parent
    <script>
      tinymce.init({
        selector: '#de_DE',
        menubar: 'undo',
        toolbar: 'undo redo',
        branding: false,
        skin_url: '/css/skin/lightgray',
        min_height: 500,
      });
    </script>
@endsection