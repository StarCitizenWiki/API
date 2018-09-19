@extends('admin.layouts.default_wide')

@section('title', __('Comm Link').' - '.$commLink->title.' - Vorschau: '.$version)

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
                {{ $commLink->title }} Vorschau: {{ $version }}
            </h4>
        </div>
        <div class="card-body">
            @include('components.messages')
            <nav class="mb-3">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-preview-tab" data-toggle="tab" href="#nav-preview" role="tab" aria-controls="nav-preview" aria-selected="true">
                        @lang('Vorschau')
                    </a>
                    <a class="nav-item nav-link" id="nav-en_EN-tab" data-toggle="tab" href="#nav-en_EN" role="tab" aria-controls="nav-en_EN" aria-selected="false">
                        @lang('Aktuell')
                    </a>
                </div>
            </nav>

            <div class="tab-content" id="nav-tab-translations">
                <div class="tab-pane fade show active" id="nav-preview" role="tabpanel" aria-labelledby="nav-preview-tab">
                    {!! empty($preview) ? 'Nicht vorhanden' : $preview !!}
                </div>
                <div class="tab-pane fade" id="nav-en_EN" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                    {!! optional($commLink->english())->translation ?? 'Nicht vorhanden' !!}
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a class="btn btn-outline-secondary" href="javascript:history.back()">@lang('Zurück')</a>
        </div>
    </div>
@endsection
