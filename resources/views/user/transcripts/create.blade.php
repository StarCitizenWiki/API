@extends('user.layouts.default_wide')

@section('title', __('Transkript'). ' erstellen')

@section('content')
    @component('components.forms.form', [
        'method' => 'POST',
        'action' => route('web.user.transcripts.store'),
        'class' => 'card',
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Transkript erstellen')</h4>
            <div class="card-body">
                @include('components.errors')
                @include('components.messages')
                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Titel (Quelle)'),
                            'id' => 'source_title',
                            'value' => old('source_title') ?? '',
                        ])
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-5">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Url (Quelle)'),
                            'id' => 'source_url',
                            'value' => old('source_url') ?? '',
                        ])
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-4 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'date',
                            'label' => __('Veröffentlichung (Quelle)'),
                            'id' => 'source_published_at',
                            'value' => old('source_published_at') ?? '',
                        ])
                        @endcomponent
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Titel'),
                            'id' => 'title',
                            'value' => old('title') ?? '',
                        ])
                            @slot('inputOptions')
                                required
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-5">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('YouTube URL'),
                            'id' => 'youtube_url',
                            'value' => old('youtube_url') ?? '',
                        ])
                            @slot('inputOptions')
                                required
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-4 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'date',
                            'label' => __('Veröffentlichung'),
                            'id' => 'published_at',
                            'value' => old('published_at') ?? '',
                        ])
                        @endcomponent
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-12 col-xl-3">
                        @component('components.forms.form-group', [
                            'inputType' => 'select',
                            'label' => __('Format'),
                            'id' => 'format',
                        ])
                            @slot('selectOptions')
                                @foreach($formats as $format)
                                    <option value="{{ $format->id }}"
                                        @if(old('format') === $format->name)
                                        selected
                                        @endif
                                    >{{ $format->name }}</option>
                                @endforeach
                            @endslot
                            @slot('inputOptions')
                                required
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-1">
                        @component('components.forms.form-group', [
                            'inputType' => 'number',
                            'label' => __('Wiki ID'),
                            'id' => 'wiki_id',
                            'value' => old('wiki_id') ?? ''
                        ])
                            @slot('inputOptions')
                                required
                                min="1"
                                max="100000"
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-2 mt-xl-4 pt-xl-2">
                        <button class="btn btn-outline-secondary" name="save">@lang('Speichern')</button>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        @component('components.forms.form-group', [
                            'inputType' => 'textarea',
                            'label' => __('de_DE'),
                            'id' => 'de_DE',
                        ])
                            @slot('value')
                                {{ old('de_DE') ?? '' }}
                            @endslot
                            @slot('inputOptions')
                                required
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12">
                        @component('components.forms.form-group', [
                            'inputType' => 'textarea',
                            'label' => __('en_EN'),
                            'id' => 'en_EN',
                        ])
                            @slot('value')
                                {{ old('en_EN') ?? '' }}
                            @endslot
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
@endsection