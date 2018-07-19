@extends('user.layouts.default')

{{-- Page Title --}}
@section('title', __('Account löschen'))

@section('content')
<div class="card">
    <h4 class="card-header">@lang('Account löschen')</h4>

    <div class="card-body">
        <h6 class="card-title">@lang('Danger Zone'):</h6>

        @unless(Auth::user()->isBlocked())
            @component('components.forms.form', [
                'method' => 'DELETE',
                'action' => route('web.user.account.delete'),
            ])
                <button class="btn btn-danger btn-block-xs-only">@lang('Löschen')</button>
            @endcomponent
        @endunless
    </div>
</div>
@endsection