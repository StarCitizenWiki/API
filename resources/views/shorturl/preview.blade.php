@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', __('ShortURL Vorschau'))

@section('main--class', 'mt-5')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => url('/'),
    ])@endcomponent

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('ShortURL Vorschau')</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => ''])
                @component('components.forms.form-group', [
                    'inputType' => 'text',
                    'label' => __('Kurzform'),
                    'id' => 'short',
                    'value' => $shorturl,
                    'tabIndex' => 1,
                    'inputClass' => 'form-control disabled',
                    'inputOptions' => 'disabled readonly',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'text',
                    'label' => __('Entkürzt'),
                    'id' => 'long',
                    'value' => $longurl,
                    'tabIndex' => 2,
                    'inputClass' => 'form-control disabled',
                    'inputOptions' => 'disabled readonly',
                ])@endcomponent

                <a href="{{ $longurl }}" class="btn btn-outline-secondary">@lang('Weiterleiten')</a>
                <a href="#" class="btn btn-link float-right text-light-grey" onclick="history.go(-1);">@lang('Zurück')</a>
            @endcomponent
        </div>
    </div>
@endsection