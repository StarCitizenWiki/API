<h4>Shops</h4>
<table class="table table-striped">
    <thead>
    <tr>
        <th>@lang('Ort')</th>
        <th>@lang('Preis')</th>
        <th>@lang('Version')</th>
    </tr>
    </thead>
    @foreach($shops->sortBy('shop_data.average_price') as $shop)
        <tr>
            <td>{{ $shop->name_raw }}</td>
            <td>{{ number_format($shop->shop_data->average_price) }} aUEC</td>
            <td>{{ $shop->version }}</td>
        </tr>
    @endforeach
</table>

<table class="table table-striped">
    <thead>
    <tr>
        <th>@lang('Ort')</th>
        <th>@lang('1 Tag')</th>
        <th>@lang('3 Tage')</th>
        <th>@lang('7 Tage')</th>
        <th>@lang('30 Tage')</th>
    </tr>
    </thead>
    @foreach($shops->filter(fn($shop) => isset($shop->shop_data->price1) && $shop->shop_data->rentable) as $shop)
        <tr>
            <td>{{ $shop->name_raw }}</td>
            <td>{{ number_format($shop->shop_data->price1) }} aUEC</td>
            <td>{{ number_format($shop->shop_data->price3) }} aUEC</td>
            <td>{{ number_format($shop->shop_data->price7) }} aUEC</td>
            <td>{{ number_format($shop->shop_data->price30) }} aUEC</td>
        </tr>
    @endforeach
</table>