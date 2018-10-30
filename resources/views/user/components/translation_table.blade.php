<table class="table table-striped mb-0">
    <thead>
        <tr>
            @can('web.user.internals.view')
                <th>@lang('ID')</th>
            @endcan
            @foreach($languages as $language)
                <th>@lang($language->locale_code)</th>
            @endforeach
            @can('web.user.translations.update')
                <th data-orderable="false">&nbsp;</th>
            @endcan
        </tr>
    </thead>
    <tbody>

    @forelse($translations as $translation)
        <tr>
            @can('web.user.internals.view')
                <td>
                    {{ $translation->id }}
                </td>
            @endcan
            @foreach($translation->translationsCollection() as $translationObject)
                <td>
                    {{ optional($translationObject)->translation ?? '-' }}
                </td>
            @endforeach
            @can('web.user.translations.update')
                <td class="text-center">
                    @component('components.edit_delete_block')
                        @slot('edit_url')
                            {{ route($editRoute, $translation->getRouteKey()) }}
                        @endslot
                        {{ $translation->getRouteKey() }}
                    @endcomponent
                </td>
            @endcan
        </tr>
    @empty
        <tr>
            <td colspan="3">@lang('Keine Übersetzungen vorhanden')</td>
        </tr>
    @endforelse
    </tbody>
</table>