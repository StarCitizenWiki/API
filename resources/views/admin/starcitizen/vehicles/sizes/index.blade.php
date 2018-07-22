@extends('admin.layouts.default')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Vehicle Sizes')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    @foreach($languages as $language)
                        <th>{{ $language->locale_code }}</th>
                    @endforeach
                    <th></th>
                </tr>
                </thead>
                <tbody>

                @forelse($sizes as $size)
                    <tr>
                        <td>
                            {{ $size->id }}
                        </td>
                        @foreach($size->translationsCollection() as $sizeTranslation)
                            <td>
                                {{ optional($sizeTranslation)->translation }}
                            </td>
                        @endforeach
                        <td>
                            @component('components.edit_delete_block')
                                @slot('edit_url')
                                    {{ route('web.admin.starcitizen.vehicles.sizes.show', $size->id) }}
                                @endslot
                                {{ $size->id }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine Fahrzeug Größen vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection