<table class="table">
    <thead>
        <tr>
            <th><span>@lang('components/routelist.methods')</span></th>
            <th><span>@lang('components/routelist.path')</span></th>
            <th><span>@lang('components/routelist.name')</span></th>
            <th><span>@lang('components/routelist.action')</span></th>
            <th><span>@lang('components/routelist.middleware')</span></th>
        </tr>
    </thead>
    <tbody>
    @foreach(Route::getRoutes() as $route)
        <?php $methods = implode(' | ', $route->methods()); ?>
        <tr>
            <td>
                <span class="badge
                    @if($methods === 'POST')
                        {{ 'badge-info' }}
                    @elseif($methods === 'GET | HEAD' || $methods === 'GET')
                        {{ 'badge-success' }}
                    @elseif($methods === 'DELETE')
                        {{ 'badge-danger' }}
                    @else
                        {{ 'badge-default' }}
                    @endif
                    ">
                    {{ $methods }}
                </span>
            </td>
            <td>
                {{ $route->uri() }}
            </td>
            <td>
                {{ $route->getName() }}
            </td>
            <td>
                {{ $route->getActionName() }}
            </td>
            <td>
                {{ implode(', ', $route->middleware()) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
