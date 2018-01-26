@extends('admin.layouts.default_wide')

@section('content')
    <div class="card mb-3">
        <h4 class="card-header">@lang('ShortUrls')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('Hash ID')</th>
                    <th>@lang('Benutzer')</th>
                    <th>@lang('Erstelldatum')</th>
                    <th>@lang('Hash')</th>
                    <th>@lang('Url')</th>
                    <th>@lang('Ablaufdatum')</th>
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
                            {{ $url->user->name }}
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
                                    {{ route('admin.url.edit_form', $url->getRouteKey()) }}
                                @endslot
                                @if($url->trashed())
                                    @slot('restore_url')
                                        {{ route('admin.url.restore', $url->getRouteKey()) }}
                                    @endslot
                                @else
                                    @slot('delete_url')
                                        {{ route('admin.url.delete', $url->getRouteKey()) }}
                                    @endslot
                                @endif
                                {{ $url->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine ShortUrls vorhanden')</td>
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