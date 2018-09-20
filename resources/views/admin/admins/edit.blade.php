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
                            'value' => empty($admin->email) ? 'Keine E-Mail vorhanden' : $admin->email,
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
                @unless($admin->isBlocked())
                    <p class="mb-0">
                        Durch den Klick auf <i>Blockieren</i> wird der Nutzer ausgeloggt und blockiert.<br>
                        Dies blockiert den Nutzer allerdings <i>nicht</i> auf dem Wiki:
                        <a class="text-info ml-auto" target="_blank" href="{{ config('api.wiki_url') }}/Spezial:Sperren/{{ $admin->username }}">Auf Wiki Blockieren</a>
                    </p>
                @else
                    <p class="mb-0">
                        Durch den Klick auf <i>Freischalten</i> wird der Nutzer auf der API erneut freigeschaltet.<br>
                        Dies schaltet den Nutzer allerdings <i>nicht</i> auf dem Wiki frei:
                        <a class="text-info ml-auto" target="_blank" href="{{ config('api.wiki_url') }}/Spezial:Freigeben/{{ $admin->username }}">Auf Wiki Freigeben</a>
                    </p>
                @endunless
            </div>
            <div class="card-footer d-flex">
                @if($admin->isBlocked())
                    <button class="btn btn-outline-success ml-auto" name="restore">@lang('Freischalten')</button>
                @else
                    <button class="btn btn-outline-danger ml-auto" name="block">@lang('Blockieren')</button>
                @endif
            </div>
        </div>
    @endcomponent
@endsection
