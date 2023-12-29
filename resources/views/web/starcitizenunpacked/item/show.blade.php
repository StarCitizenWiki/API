@extends('web.layouts.default')

@section('title', __('Item').' - ' . $item->name)


@section('content')
    <div class="card mb-4">
        <div id="item-live-search"><item-live-search api-token="{{ $apiToken ?? '' }}"></item-live-search></div>
    </div>
    <div class="card">
        <div class="card-header">
            <h4>
                {{ $item->name }}
            </h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @foreach($item->translationsCollection() as $translation)
                        <p><strong>{{ __($translation->locale_code) }}</strong>: {{ $translation->translation ?? '-' }}</p>
                    @endforeach
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col-12">
                            <h4>@lang('Basisdaten')</h4>
                        </div>
                        <div class="col col-md-6 col-xl-4">
                            <table class="table">
                                <tr>
                                    <th>@lang('Hersteller'):</th>
                                    <td>{{ $item->manufacturer->name }} ({{ $item->manufacturer->code }})</td>
                                </tr>
                                @foreach($item->descriptionData->filter(fn ($datum) => $datum->name !== 'Manufacturer') as $datum)
                                <tr>
                                    <th>{{ $datum->name }}:</th>
                                    <td>{{ $datum->value }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <th>@lang('Inventar'):</th>
                                    <td>{{ $item->container->calculated_scu ?? '-' }} SCU</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col col-md-6 col-xl-4">
                            <table class="table">
                                <tr>
                                    <th>@lang('Länge'):</th>
                                    <td>{{ $item?->vehicle?->length ?? $item->dimension->length }}m</td>
                                </tr>
                                <tr>
                                    <th>@lang('Breite'):</th>
                                    <td>{{ $item?->vehicle?->width ?? $item->dimension->width }}m</td>
                                </tr>
                                <tr>
                                    <th>@lang('Höhe'):</th>
                                    <td>{{ $item?->vehicle?->height ?? $item->dimension->height }}m</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col col-md-6 col-xl-4">
                            <table class="table">
                                <tr>
                                    <th>UUID:</th>
                                    <td><a href="{{ config('app.url') }}/api/v2/items/{{ $item->uuid }}">{{ $item->uuid }}</a></td>
                                </tr>
                                <tr>
                                    <th>@lang('Type'):</th>
                                    <td>{{ $item->type }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Class'):</th>
                                    <td>{{ $item->class_name }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    @includeUnless($item->shops->isEmpty(), 'components.starcitizenunpacked.shop_table', [ 'shops' => $item->shops ])
                </div>
            </div>
        </div>
    </div>
@endsection
