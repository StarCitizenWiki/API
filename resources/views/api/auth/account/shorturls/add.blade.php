@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', '__LOC__Add_Url')

@section('content')
@include('components.errors')

<div class="card">
    <h4 class="card-header">@lang('auth/account/shorturls/add.header')</h4>
    <div class="card-body">
        @component('components.forms.form', ['action' => route('account_urls_add')])
            @component('components.forms.form-group', [
                'id' => 'url',
                'label' => '__LOC__Url',
                'inputType' => 'url',
                'tabIndex' => 1,
                'autofocus' => 1,
                'required' => 1,
                'value' => old('url'),
                'inputOptions' => 'spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'id' => 'hash',
                'label' => '__LOC__Hash',
                'tabIndex' => 2,
                'value' => old('hash'),
                'inputOptions' => 'data-minlength=3 spellcheck=false',
            ])@endcomponent

            @component('components.forms.form-group', [
                'id' => 'expired_at',
                'label' => '__LOC__Expired_at',
                'inputType' => 'datetime-local',
                'tabIndex' => 3,
                'value' => old('expired_at'),
                'inputOptions' => 'min='.\Carbon\Carbon::now()->format("Y-m-d\TH:i"),
            ])@endcomponent

            <button class="btn btn-outline-success btn-block-xs-only pull-right">__LOC__Add</button>
        @endcomponent
    </div>
</div>
@endsection
