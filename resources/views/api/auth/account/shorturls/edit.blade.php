@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', trans('auth/account/shorturls/edit.header'))

@section('content')
@include('components.errors')

<div class="card">
    <h4 class="card-header">@lang('auth/account/shorturls/edit.header')</h4>
    <div class="card-body">
        @component('components.forms.form', [
            'action' => route('account_urls_update'),
            'method' => 'PATCH',
        ])
            <input name="id" type="hidden" value="{{ $url->id }}">
            @component('components.forms.form-group', [
                'id' => 'url',
                'label' => trans('auth/account/shorturls/edit.url'),
                'inputType' => 'url',
                'tabIndex' => 1,
                'autofocus' => 1,
                'value' => $url->url,
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'id' => 'hash_name',
                'label' => trans('auth/account/shorturls/edit.name'),
                'tabIndex' => 2,
                'value' => $url->hash_name,
                'inputOptions' => 'data-minlength=3 spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'id' => 'expires',
                'label' => trans('auth/account/shorturls/edit.expires'),
                'inputType' => 'datetime-local',
                'tabIndex' => 3,
                'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
            ])
                @slot('value')
                    @unless(is_null($url->expires)){{ \Carbon\Carbon::parse($url->expires)->format('Y-m-d\TH:i') }}@endunless
                @endslot
            @endcomponent

            <button class="btn btn-outline-success btn-block-xs-only pull-right">@lang('auth/account/shorturls/edit.edit')</button>
        @endcomponent
    </div>
</div>
@endsection
