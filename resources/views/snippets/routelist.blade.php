<div class="text-left">
    <ul class="list-unstyled">
        @foreach(Route::getRoutes() as $route)
            <li>
                <div class="row mb-1">
                    <span class="col-1 badge badge-info mr-3 pt-1">{{ implode(' | ', $route->methods()) }}</span>
                    <span class="col-4 mr-3">{{ $route->uri() }}</span>
                    <span class="col-2 mr-3">{{ $route->getActionName() }}</span>
                    <span class="col-3 mr-3">{{ $route->domain() }}</span>
                </div>
            </li>
        @endforeach
    </ul>
</div>