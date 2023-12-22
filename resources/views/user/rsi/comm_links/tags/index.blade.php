@extends('user.layouts.default_wide')

@section('title', __('Comm-Link Image Tags'))

@section('head__content')
    @parent
    <style>
        span ul {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    @include('user.rsi.comm_links.components.create_tag_form')

    <div class="card">
        <div class="card-header d-flex">
            <h4 class="mb-0 pt-1">@lang('Tags')</h4>
            @unless(empty($tags) || !method_exists($tags, 'links'))
                <span class="d-flex ml-auto">{{ $tags->links() }}</span>
            @endunless
        </div>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0" data-order='[[ 2, "desc" ]]' data-page-length="50" data-length-menu='[ [25, 50, 100, -1], [25, 50, 100, "@lang('Alle')"] ]'>
                <thead>
                    <tr>
                        <th>@lang('ID')</th>
                        <th>@lang('Name')</th>
                        <th>@lang('Bilder')</th>
                        <th data-orderable="false"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tags as $tag)
                    <tr>
                        <td>
                            {{ $tag->id }}
                        </td>
                        <td>
                            <a href="{{ route('web.user.rsi.comm-links.images.index-by-tag', $tag->getRouteKey()) }}">{{ $tag->name }}</a>
                        </td>
                        <td>
                            {{ $tag->images_count }}
                        </td>
                        <td class="text-center">
                            @component('components.edit_delete_block')
                                @can('web.user.rsi.comm-links.update')
                                    @slot('edit_url')
                                        #
{{--                                        {{ route('web.user.rsi.comm-links.edit', $tag->getRouteKey()) }}--}}
                                    @endslot
                                @endcan
                                {{ $tag->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">@lang('Keine Tags vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @unless(empty($tags) || !method_exists($tags, 'links'))
        <div class="card-footer">{{ $tags->links() }}</div>
        @endunless
    </div>
@endsection

@section('body__after')
    @parent
    @if(count($tags) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection