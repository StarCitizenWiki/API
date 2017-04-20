<table class="table">
    <thead>
        <tr>
            <th><span>Methods</span></th>
            <th><span>Path</span></th>
            <th><span>Name</span></th>
            <th><span>Action</span></th>
            <th><span>Middleware</span></th>
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
