@extends('user.layouts.default')

@section('title', __('Benutzer').' - '.$user->username)

@section('content')
    @component('components.forms.form', [
        'method' => 'PATCH',
        'action' => route('web.user.users.update', $user->getRouteKey()),
        'class' => 'card',
    ])
        <div class="wrapper">
            <h4 class="card-header">@lang('Benutzer bearbeiten')</h4>
            <div class="card-body">
                @include('components.errors')
                @include('components.messages')
                <h6>Stammdaten:</h6>
                <div class="row">
                    <div class="col-12 col-lg-2">
                        @component('components.forms.form-group', [
                            'inputType' => 'text',
                            'label' => __('ID'),
                            'id' => 'id',
                            'value' => $user->id,
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
                            'value' => $user->provider_id,
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
                            'value' => $user->username,
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
                            'value' => empty($user->email) ? 'Keine E-Mail vorhanden' : $user->email,
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
                            'value' => $user->provider,
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
                        ])
                        @slot('value')
                            {{ $user->groups->map(function($group) { return __($group->name); })->implode('name', ', ') }}
                        @endslot
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
                            'label' => __('API Schlüssel'),
                            'id' => 'api_token',
                            'value' => $user->api_token,
                        ])@endcomponent
                    </div>
                </div>

                <hr>

                <h6>Einstellungen:</h6>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="no_api_throttle" name="no_api_throttle" aria-describedby="no_api_throttle_help_block" @if($user->settings->isUnthrottled()) checked @endif>
                                <label class="custom-control-label" for="no_api_throttle">@lang('Deaktiviertes Rate-Limiting')</label>
                                <small id="no_api_throttle_help_block" class="form-text text-muted">
                                    @lang('Deaktiviere das Rate-Limiting bei Api Anfragen für diesen Benutzer')
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <h6>Aktive Sessions:</h6>
                <div class="row">
                    <div class="col-12">
                        @forelse($user->sessions as $session)
                            <p>
                                {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->format('d.m.Y H:i') }}
                                &mdash;
                                {{ $session->user_agent }}
                            </p>
                        @empty
                            @lang('Keine aktiven Sessions vorhanden')
                        @endforelse
                    </div>
                </div>

                <hr>

                <h6>Hinweis:</h6>
                @unless($user->blocked)
                    <p class="mb-0">
                        Durch den Klick auf <i>Blockieren</i> wird der Nutzer ausgeloggt und blockiert.<br>
                        Dies blockiert den Nutzer allerdings <i>nicht</i> auf dem Wiki:
                        <a class="text-info ml-auto" target="_blank" href="{{ config('api.wiki_url') }}/Spezial:Sperren/{{ $user->username }}">Auf Wiki Blockieren</a>
                    </p>
                @else
                    <p class="mb-0">
                        Durch den Klick auf <i>Freischalten</i> wird der Nutzer auf der API erneut freigeschaltet.<br>
                        Dies schaltet den Nutzer allerdings <i>nicht</i> auf dem Wiki frei:
                        <a class="text-info ml-auto" target="_blank" href="{{ config('api.wiki_url') }}/Spezial:Freigeben/{{ $user->username }}">Auf Wiki Freigeben</a>
                    </p>
                @endunless
            </div>
            <div class="card-footer d-flex">
                @if($user->blocked)
                    <button class="btn btn-outline-success" name="restore">@lang('Freischalten')</button>
                @else
                    <button class="btn btn-outline-danger" name="block">@lang('Blockieren')</button>
                @endif
                <button class="btn btn-outline-secondary ml-auto" name="save">@lang('Speichern')</button>
            </div>
        </div>
    @endcomponent
@endsection
