@extends('layouts.admin')
@section('title')
    @lang('admin/shorturls/index.header')
@endsection

@section('content')
    <table class="table table-striped" id="urlTable" cellspacing="0">
        <thead>
        <tr>
            <th><span>@lang('admin/shorturls/index.id')</span></th>
            <th><span>@lang('admin/shorturls/index.url')</span></th>
            <th><span>@lang('admin/shorturls/index.hash')</span></th>
            <th><span>@lang('admin/shorturls/index.owner')</span></th>
            <th><span>@lang('admin/shorturls/index.created_at')</span></th>
            <th><span>@lang('admin/shorturls/index.expires_at')</span></th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @if(count($urls) > 0)
            @foreach($urls as $url)
            <tr>
                <td>
                    {{ $url->id }}
                </td>
                <td>
                    {{ $url->url }}
                </td>
                <td>
                    {{ $url->hash_name }}
                </td>
                <td>
                    {{ $url->user()->first()->email }}
                </td>
                <td>
                    {{ Carbon\Carbon::parse($url->created_at)->format('d.m.Y') }}
                </td>
                <td>
                    @component('components.shorturls.expiresfield', ['expires' => $url->expires])@endcomponent
                </td>
                <td>
                    @component('components.edit_delete_block')
                        @slot('edit_url')
                            {{ route('admin_urls_edit_form', $url->id) }}
                        @endslot
                        @slot('delete_url')
                            {{ route('admin_urls_delete') }}
                        @endslot
                        {{ $url->id }}
                    @endcomponent
                </td>
            </tr>
            @endforeach
        @else
        <tr>
            <td colspan="7">@lang('admin/shorturls/index.no_urls_found')</td>
        </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('scripts')
    @include('components.init_dataTables')
@endsection
