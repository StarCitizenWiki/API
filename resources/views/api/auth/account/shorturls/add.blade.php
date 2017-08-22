@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', trans('auth/account/shorturls/add.header'))

@section('content')
@include('components.errors')

<div class="card">
    <h4 class="card-header">@lang('auth/account/shorturls/add.header')</h4>
    <div class="card-body">
        @component('components.forms.form', ['action' => route('account_urls_add')])
            @component('components.forms.form-group', [
                'id' => 'url',
                'label' => trans('auth/account/shorturls/add.url'),
                'inputType' => 'url',
                'tabIndex' => 1,
                'autofocus' => 1,
                'required' => 1,
                'value' => old('url'),
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'id' => 'hash',
                'label' => trans('auth/account/shorturls/add.name'),
                'tabIndex' => 2,
                'value' => old('hash'),
                'inputOptions' => 'data-minlength=3 spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'id' => 'expires',
                'label' => trans('auth/account/shorturls/add.expires'),
                'inputType' => 'datetime-local',
                'tabIndex' => 3,
                'value' => old('expires'),
                'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
            ])@endcomponent

            <button class="btn btn-outline-success btn-block-xs-only pull-right">@lang('auth/account/shorturls/add.add')</button>
        @endcomponent
    </div>
</div>
@endsection
