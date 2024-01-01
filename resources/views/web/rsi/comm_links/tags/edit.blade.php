@extends('web.layouts.default')

@section('title', __('Tag').' - '.$tag->translated_name. ' ' . __('(bearbeiten)'))

@section('content')
    @component('components.forms.form', [
        'action' => route('web.rsi.comm-links.image-tags.update', $tag->getRouteKey()),
        'method' => 'POST',
        'class' => 'card mb-4'
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Tag Bearbeiten')</h4>
            <div class="card-body">
                @include('components.errors')
                @include('components.messages')
                @component('components.forms.form-group', [
                    'inputType' => 'text',
                    'label' => __('Name'),
                    'id' => 'name',
                    'value' => $tag->name,
                    'required' => true,
                ])@endcomponent
                @component('components.forms.form-group', [
                    'inputType' => 'text',
                    'label' => __('Name') . ' ' . __('Englisch'),
                    'id' => 'name_en',
                    'value' => $tag->name_en,
                    'required' => true,
                ])@endcomponent

                <button class="btn btn-outline-secondary" type="submit">@lang('Speichern')</button>
            </div>
        </div>
    @endcomponent
@endsection


