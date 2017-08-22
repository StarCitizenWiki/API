@extends('admin.layouts.default_wide')

@section('content')
    <div class="card mb-3">
        <h4 class="card-header">ShortURLs</h4>
        <div class="card-body px-0">
            <table class="table table-striped table-responsive mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Hash ID</th>
                    <th>User</th>
                    <th>Created</th>
                    <th>Hash</th>
                    <th>URL</th>
                    <th>Expires</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                    @forelse($urls as $url)
                        <tr @if($url->trashed()) class="text-muted" @endif>
                            <td>
                                {{ $url->id }}
                            </td>
                            <td>
                                {{ $url->getRouteKey() }}
                            </td>
                            <td>
                                {{ $url->user()->first()->name }}
                            </td>
                            <td title="{{ $url->created_at->format('d.m.Y H:i:s') }}">
                                {{ $url->created_at->format('d.m.Y') }}
                            </td>
                            <td>
                                {{ $url->hash }}
                            </td>
                            <td>
                                {{ $url->url }}
                            </td>
                            <td title="@unless(is_null($url->expired_at)){{ $url->expired_at->format('d.m.Y H:i:s') }}@endunless">
                                @unless(is_null($url->expired_at))
                                    {{ $url->expired_at->format('d.m.Y H:i:s') }}
                                @else
                                    -
                                @endunless
                            </td>
                            <td>
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('admin_urls_edit_form', $url->getRouteKey()) }}
                                    @endslot
                                    @if($url->trashed())
                                        @slot('restore_url')
                                            {{ route('admin_urls_restore', $url->getRouteKey()) }}
                                        @endslot
                                    @else
                                        @slot('delete_url')
                                            {{ route('admin_urls_delete', $url->getRouteKey()) }}
                                        @endslot
                                    @endif
                                    {{ $url->getRouteKey() }}
                                @endcomponent
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">@lang('auth/account/urls/index.no_urls_found')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $urls->links() }}</div>
    </div>
@endsection

@section('body__after')
    @parent
    @include('components.init_dataTables')
@endsection