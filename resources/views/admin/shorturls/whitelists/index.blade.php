@extends('admin.layouts.default_wide')

@section('content')
    <div class="row">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card mb-3">
                <h4 class="card-header">ShortURLs</h4>
                <div class="card-body px-0">
                    <table class="table table-striped table-responsive mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hash ID</th>
                            <th>Created</th>
                            <th>URL</th>
                            <th>Internal</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($urls as $url)
                            <tr>
                                <td>
                                    {{ $url->id }}
                                </td>
                                <td>
                                    {{ $url->getRouteKey() }}
                                </td>
                                <td title="{{ $url->created_at->format('d.m.Y H:i:s') }}">
                                    {{ $url->created_at->format('d.m.Y') }}
                                </td>
                                <td>
                                    {{ $url->url }}
                                </td>
                                <td class="text-center" data-sort="{{ $url->internal }}">
                                    @component('components.elements.icon')
                                        @if($url->internal)
                                            check
                                        @else
                                            times
                                        @endif
                                    @endcomponent
                                </td>
                                <td class="text-center">
                                    @component('components.edit_delete_block')
                                        @slot('delete_url')
                                            {{ route('admin_urls_whitelist_delete', $url->getRouteKey()) }}
                                        @endslot
                                        {{ $url->getRouteKey() }}
                                    @endcomponent
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">@lang('auth/account/urls/index.no_urls_found')</td>
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