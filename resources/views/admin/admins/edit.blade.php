@extends('admin.layouts.default')

@section('title', __('Admin').' - '.$admin->username)

@section('content')
    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.admin.admins.update', $admin->getRouteKey()),
        'class' => 'card',
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Admin bearbeiten')</h4>
            <div class="card-body">
                @include('components.errors')
                <div class="row">
                    <div class="col-12 col-lg-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('ID'),
                            'id' => 'id',
                            'value' => $admin->id,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-3">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Provider ID'),
                            'id' => 'provider_id',
                            'value' => $admin->provider_id,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-7">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Benutzername'),
                            'id' => 'username',
                            'value' => $admin->username,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-7">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('E-Mail'),
                            'id' => 'email',
                            'value' => $admin->email ?? 'Keine E-Mail vorhanden',
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                    <div class="col-12 col-lg-5">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Provider'),
                            'id' => 'provider',
                            'value' => $admin->provider,
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('Gruppen'),
                            'id' => 'groups',
                            'value' => $admin->groups->implode('name', ', '),
                        ])
                            @slot('inputOptions')
                                readonly
                            @endslot
                        @endcomponent
                    </div>
                </div>
                <hr>
                <h5>Sessions:</h5>
                @foreach($admin->sessions as $session)
                    <div>
                        <p>Agent: {{ $session->user_agent }}</p>
                        <p>Letzte Aktivität: {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</p>
                    </div>
                @endforeach
                <hr>
                <p class="mb-0">
                    Durch den Klick auf <i>Blockieren</i> wird der Nutzer ausgeloggt und bis zum nächsten Login blockiert.<br>
                    Für einen dauerthaften Ausschluss muss der Nutzer zuerst auf dem Wiki blockiert werden:
                    <a class="text-info ml-auto" target="_blank" href="{{ config('api.wiki_url') }}/Spezial:Sperren/{{ $admin->username }}">Admin auf Wiki Blockieren</a>
                </p>
            </div>
            <div class="card-footer d-flex">
                <button class="btn btn-outline-danger ml-auto" name="block">@lang('Blockieren')</button>
            </div>
        </div>
    @endcomponent
@endsection
