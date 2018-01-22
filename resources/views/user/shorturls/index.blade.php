@extends('user.layouts.default_wide')

@section('title', __('ShortUrls'))

@section('content')
    <div class="row">
        <div class="col-12 col-md-8 col-lg-3 mx-auto">
            @if (session('hash'))
                <div class="alert alert-success text-center">
                    {{config('app.shorturl_url')}}/{{ session('hash') }}
                </div>
            @endif
            @include('components.errors')
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-xl-11 mx-auto mt-xl-1">
            <div class="card">
                <h4 class="card-header">@lang('ShortUrls')</h4>
                <div class="card-body px-0">
                    <table class="table table-striped table-responsive mb-0">
                        <thead>
                        <tr>
                            <th><span>@lang('Short')</span></th>
                            <th><span>@lang('Url')</span></th>
                            <th><span>@lang('Hash')</span></th>
                            <th><span>@lang('Erstelldatum')</span></th>
                            <th><span>@lang('Ablaufdatum')</span></th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($urls as $url)
                            <tr>
                                <td>
                                    {{config('app.shorturl_url')}}/{{ $url->hash }}
                                </td>
                                <td>
                                    {{ $url->url }}
                                </td>
                                <td>
                                    {{ $url->hash }}
                                </td>
                                <td>
                                    {{ $url->created_at->format('d.m.Y') }}
                                </td>
                                <td>
                                    @component('components.shorturls.expired_at_field', ['expired_at' => $url->expired_at])@endcomponent
                                </td>
                                <td>
                                    @component('components.edit_delete_block')
                                        @slot('edit_url')
                                            {{ route('account_url_edit_form', $url->getRouteKey()) }}
                                        @endslot
                                        @slot('delete_url')
                                            {{ route('account_url_delete', $url->getRouteKey()) }}
                                        @endslot
                                        {{ $url->getRouteKey() }}
                                    @endcomponent
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">@lang('Keine ShortUrls gefunden')</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $urls->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@section('body__after')
    @parent
    @include('components.init_dataTables')
@endsection
