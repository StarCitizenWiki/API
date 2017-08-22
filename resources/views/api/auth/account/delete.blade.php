@extends('api.auth.layouts.default')

{{-- Page Title --}}
@section('title', '__LOC__Delete_Account')

@section('content')
<div class="card">
    <h4 class="card-header">__LOC__Delete_Account</h4>

    <div class="card-body">
        <h6 class="card-title">__LOC__Danger Zone:</h6>

        @unless(Auth::user()->isBlacklisted())
            @component('components.forms.form', [
                'method' => 'DELETE',
                'action' => route('account_delete'),
            ])
                <button class="btn btn-danger btn-block-xs-only pull-right">__LOC__Löschen</button>
            @endcomponent
        @endunless
    </div>
</div>
@endsection