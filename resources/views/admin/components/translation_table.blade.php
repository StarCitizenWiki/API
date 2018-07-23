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

    @forelse($translations as $translation)
        <tr>
            <td>
                {{ $translation->id }}
            </td>
            @foreach($translation->translationsCollection() as $translationObject)
                <td>
                    {{ optional($translationObject)->translation ?? '-' }}
                </td>
            @endforeach
            <td>
                @component('components.edit_delete_block')
                    @slot('edit_url')
                        {{ route($editRoute, $translation->id) }}
                    @endslot
                    {{ $translation->id }}
                @endcomponent
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7">@lang('Keine Übersetzungen vorhanden')</td>
        </tr>
    @endforelse
    </tbody>
</table>