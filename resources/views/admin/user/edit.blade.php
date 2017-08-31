@extends('admin.layouts.default')

@section('content')
    @component('admin.components.card', [
        'class' => 'mb-4',
        'icon' => 'user',
    ])
        @slot('title')
            #@lang('Benutzer bearbeiten')
        @endslot
        @include('components.errors')
        @component('components.forms.form', [
            'action' => route('admin_user_update', $user->getRouteKey()),
            'method' => 'PATCH',
        ])
            <div class="row">
                <div class="col-12 col-lg-3">
                    @component('components.forms.form-group', [
                        'label' => __('ID'),
                        'id' => 'id',
                        'inputOptions' => 'disabled',
                        'value' => $user->id,
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-3">
                    @component('components.forms.form-group', [
                        'label' => __('Hash ID'),
                        'id' => 'hash_id',
                        'inputOptions' => 'disabled',
                        'value' => $user->getRouteKey(),
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'label' => __('Name'),
                        'id' => 'name',
                        'value' => $user->name,
                    ])@endcomponent
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'inputType' => 'email',
                        'label' => __('E-Mail'),
                        'id' => 'email',
                        'value' => $user->email,
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'inputType' => 'select',
                        'label' => __('Status'),
                        'id' => 'state',
                        'inputClass' => 'custom-select w-100',
                    ])
                        @slot('selectOptions')
                            <option value="0">Normal</option>
                            <option value="1">Unlimitiert</option>
                            <option value="2">Gesperrt</option>
                        @endslot
                    @endcomponent
                </div>
            </div>
            @component('components.forms.form-group', [
                'label' => __('Api Key'),
                'id' => 'api_token',
                'value' => $user->api_token,
            ])@endcomponent
            <div class="row">
                <div class="col-12 col-lg-3">
                    @component('components.forms.form-group', [
                        'inputType' => 'integer',
                        'label' => __('Anfragen pro Minute'),
                        'id' => 'requests_per_minute',
                        'value' => $user->requests_per_minute,
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-3">
                    @component('components.forms.form-group', [
                        'inputType' => 'integer',
                        'label' => __('In der letzten Minute'),
                        'id' => 'requests_per_minute',
                        'inputOptions' => 'disabled',
                        'value' => $user->requests_per_minute,
                    ])@endcomponent
                </div>
                <div class="col-12 col-lg-6">
                    @component('components.forms.form-group', [
                        'label' => __('Passwort ändern'),
                        'id' => 'password',
                    ])@endcomponent
                </div>
            </div>
            @component('components.forms.form-group', [
                'inputType' => 'textarea',
                'label' => __('Notiz'),
                'id' => 'notes',
                'value' => $user->notes,
            ])@endcomponent


            @if($user->trashed())
                <button class="btn btn-outline-success" name="restore">@lang('Wiederherstellen')</button>
            @else
                <button class="btn btn-outline-danger" name="delete">@lang('Löschen')</button>
            @endif
            <button class="btn btn-outline-secondary float-right" name="save">@lang('Speichern')</button>
        @endcomponent
    @endcomponent

    @component('admin.components.card', [
        'class' => 'mb-4',
        'contentClass' => 'p-2',
        'icon' => 'table',
    ])
        @slot('title')
            @lang('ShortUrls')
            <small class="float-right mt-1">
                <a href="{{ route('admin_user_url_list', $user->getRouteKey()) }}">
                    <i class="far fa-external-link"></i>
                </a>
            </small>
        @endslot
        <table class="table table-responsive table-sm mb-0">
            <tr>
                <th>@lang('ID')</th>
                <th>@lang('Hash')</th>
                <th>@lang('Url')</th>
                <th>@lang('Erstelldatum')</th>
                <th></th>
            </tr>
            @forelse($user->shortUrls as $shortUrl)
                <tr>
                    <td>{{ $shortUrl->id }}</td>
                    <td>{{ $shortUrl->hash }}</td>
                    <td>{{ $shortUrl->url }}</td>
                    <td title="{{ $shortUrl->created_at }}"><span class="d-block d-xxl-none">{{ $shortUrl->created_at->format('d.m.Y') }}</span><span class="d-none d-xxl-block">{{ $shortUrl->created_at }}</span></td>
                    <td class="text-center"><a href="{{ route('admin_url_edit_form', $shortUrl->getRouteKey()) }}"><i class="far fa-pencil"></i></a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">@lang('Keine ShortUrls vorhanden')</td>
                </tr>
            @endforelse
        </table>
    @endcomponent
    @component('admin.components.card', [
        'class' => 'mb-4',
        'contentClass' => 'p-2',
        'icon' => 'table',
    ])
        @slot('title')
            @lang('ApiRequests')
            <small class="float-right mt-1">
                <a href="{{ route('admin_user_request_list', $user->getRouteKey()) }}">
                    <i class="far fa-external-link"></i>
                </a>
            </small>
        @endslot
        <table class="table table-responsive table-sm mb-0">
            <tr>
                <th>@lang('ID')</th>
                <th>@lang('Url')</th>
                <th>@lang('Erstelldatum')</th>
                <th></th>
            </tr>
            @forelse($user->apiRequests as $api_request)
                <tr>
                    <td>{{ $api_request->user->name }}</td>
                    <td title="{{ $api_request->created_at }}"><span class="d-block d-xxl-none">{{ $api_request->created_at->format('d.m.Y') }}</span><span class="d-none d-xxl-block">{{ $api_request->created_at }}</span></td>
                    <td>{{ $api_request->request_uri }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">@lang('Keine Api Anfragen vorhanden')</td>
                </tr>
            @endforelse
        </table>
    @endcomponent
@endsection