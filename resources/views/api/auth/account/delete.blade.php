@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', trans('auth/account/index.delete'))

@section('content')
<div class="card">
    <h4 class="card-header">@lang('auth/account/index.delete')</h4>

    <div class="card-body">
        <h6 class="card-title">Danger Zone:</h6>

        @unless(Auth::user()->isBlacklisted())
            @component('components.forms.form', [
                'method' => 'DELETE',
                'action' => route('account_delete'),
            ])
                <button class="btn btn-danger btn-block-xs-only pull-right">@lang('auth/account/index.delete')</button>
            @endcomponent
        @endunless
    </div>
</div>
@endsection