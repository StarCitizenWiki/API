@extends('api.auth.layouts.default_wide')

{{-- Page Title --}}
@section('title', '__LOC__ShortUrls')


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
                <h4 class="card-header">__LOC__ShortURLs</h4>

                <div class="card-body px-0">
                    <table class="table table-striped table-responsive" id="urlTable" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th><span>__LOC__Short</span></th>
                            <th><span>__LOC__url</span></th>
                            <th><span>__LOC__hash</span></th>
                            <th><span>__LOC__created_at</span></th>
                            <th><span>__LOC__expired_at_at</span></th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($urls) > 0)
                            @foreach($urls as $url)
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
                                        {{ Carbon\Carbon::parse($url->created_at)->format('d.m.Y') }}
                                    </td>
                                    <td>
                                        @component('components.shorturls.expired_atfield', ['expired_at' => $url->expired_at])@endcomponent
                                    </td>
                                    <td>
                                        @component('components.edit_delete_block')
                                            @slot('edit_url')
                                                {{ route('account_urls_edit_form', $url->getRouteKey()) }}
                                            @endslot
                                            @slot('delete_url')
                                                {{ route('account_urls_delete') }}
                                            @endslot
                                            {{ $url->getRouteKey() }}
                                        @endcomponent
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7">__LOC__No_Urls_Found</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>
@endsection


@section('body__after')
    @parent
    @include('components.init_dataTables')
@endsection
