@extends('admin.layouts.default')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Vehicle Types')</h4>
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

                @forelse($types as $type)
                    <tr>
                        <td>
                            {{ $type->id }}
                        </td>
                        @foreach($type->translationsCollection() as $typeTranslation)
                            <td>
                                {{ optional($typeTranslation)->translation }}
                            </td>
                        @endforeach
                        <td>
                            @component('components.edit_delete_block')
                                @slot('edit_url')
                                    {{ route('web.admin.starcitizen.vehicles.types.show', $type->id) }}
                                @endslot
                                {{ $type->id }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('Keine Fahrzeug Typen vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection