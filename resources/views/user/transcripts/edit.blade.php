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
        'class' => 'card',
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Transkript bearbeiten')</h4>
            <div class="card-body">
                @include('components.errors')
                @include('components.messages')
                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-4">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Titel (Quelle)'),
                            'id' => 'source_title',
                            'value' => $transcript->source_title,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-12 col-xl-5">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Url (Quelle)'),
                            'id' => 'source_url',
                            'value' => $transcript->source_url,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-4 col-xl-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'date',
                            'label' => __('Veröffentlichung (Quelle)'),
                            'id' => 'created_at',
                            'value' => $transcript->source_published_at->format('Y-m-d'),
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
                            'value' => $transcript->youtube_url,
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
                            'value' => $transcript->created_at ? $transcript->created_at->format('Y-m-d') : $transcript->published_at->format('Y-m-d'),
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
                                        @if($transcript->format->name === $format->name)
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
                            'value' => $transcript->wiki_id
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
                        {!! empty($transcript->english()->translation) ? 'Nicht vorhanden' : nl2br($transcript->english()->translation) !!}
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
@endsection