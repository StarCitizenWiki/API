@extends('web.layouts.default_wide')

@section('title', __('Änderungsübersicht'))

@section('content')
    <div class="d-flex ">
        <form class="form-inline" id="modelForm">
            <label class="my-1 mr-2" for="model">@lang('Modell')</label>
            <select class="custom-select form-control my-1 mr-sm-2" id="model">
                <option value="" selected>@lang('Alle')</option>
                @foreach($models as $model)
                    <option value="{{ $model->changelog_type }}">{{ class_basename($model->changelog_type) }}</option>
                @endforeach
            </select>
        </form>

        <form class="form-inline" id="typeForm">
            <label class="my-1 mr-2" for="type">@lang('Typ')</label>
            <select class="custom-select form-control my-1 mr-sm-2" id="type">
                <option value="" selected>@lang('Alle')</option>
                @foreach($types as $type)
                    <option value="{{ $type->type }}">{{ $type->type }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card">
        <h4 class="card-header">@lang('Änderungsübersicht')</h4>
        <div class="card-body px-0 table-responsive">
            <div class="dataTables_wrapper">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            @can('web.internals.view')
                                <th>@lang('ID')</th>
                            @endcan
                            <th>@lang('Benutzer')</th>
                            <th>@lang('Typ')</th>
                            <th>@lang('Modell')</th>
                            <th>@lang('Datum')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($changelogs as $changelog)
                            <tr>
                                @can('web.internals.view')
                                    <td>
                                        {{ $changelog->id }}
                                    </td>
                                @endcan
                                <td>
                                    {!! $changelog->user_link !!}
                                </td>
                                <td>
                                    <a data-toggle="collapse" href="#details_{{$loop->index}}" role="button"
                                       aria-expanded="false" aria-controls="#details_{{$loop->index}}">
                                        <u>{{ __($changelog->type) }}</u>
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ $changelog->model_route }}">
                                        {{ class_basename($changelog->changelog_type) }}
                                    </a>
                                </td>
                                <td data-content="{{ $changelog->created_at->format('d.m.Y H:i:s') }}" data-toggle="popover">
                                    {{ $changelog->created_at->diffForHumans() }}
                                </td>
                            </tr>

                            <tr id="details_{{ $loop->index }}" class="collapse" style="overflow-y:scroll">
                                <td colspan="5">
                                    @unless($changelog->model_route === '#')
                                        <a href="{{ $changelog->model_route }}">{{ __('Änderungen') }} {{ __('Ansehen') }}</a>
                                        <hr>
                                    @endunless

                                    <p class="mb-0">@lang('Details'):</p>
                                    <pre><code class="mb-0" style="white-space: pre-wrap;">{!! $changelog->formatted_changelog !!}</code></pre>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9">@lang('Keine Änderungen vorhanden')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $changelogs->links() }}</div>
    </div>
@endsection

@section('body__after')
    @parent
    <script>
        (() => {
            const currentUrl = new URL(window.location)
            const modelSelect = document.getElementById('model')
            const typeSelect = document.getElementById('type')
            const filters = ['model', 'type'];

            const listener = (ev) => {
                currentUrl.searchParams.delete('page')

                const filter = ev.target.parentElement.id === 'modelForm' ? 'model' : 'type';

                if (filter === 'model') {
                    currentUrl.searchParams.delete('model')
                } else {
                    currentUrl.searchParams.delete('type')
                }

                currentUrl.searchParams.append(
                    filter,
                    ev.target.value
                )

                window.location = currentUrl.href
            };

            filters.forEach((filter) => {
                if (currentUrl.searchParams.has(filter)) {
                    modelSelect.querySelectorAll('option').forEach(option => {
                        option.selected = option.value === currentUrl.searchParams.get(filter);
                    })

                    document.querySelectorAll('nav .page-item a.page-link').forEach(navLink => {
                        const url = new URL(navLink.href)
                        url.searchParams.append(filter, currentUrl.searchParams.get(filter))

                        navLink.href = url.toString()
                    })
                }
            });

            modelSelect.addEventListener('change', listener);
            typeSelect.addEventListener('change', listener);
        })();
    </script>
@endsection