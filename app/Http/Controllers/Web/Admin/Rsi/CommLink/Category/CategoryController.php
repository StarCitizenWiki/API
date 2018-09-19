<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink\Category;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Category\Category;

/**
 * Comm Link Category Controller
 */
class CategoryController extends Controller
{
    /**
     * CommLinkController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
    }

    /**
     * All Categories
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.admin.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.rsi.comm_links.categories.index',
            [
                'categories' => Category::orderBy('name')->get(),
            ]
        );
    }

    /**
     * Get All Comm Links in a given Category
     *
     * @param \App\Models\Rsi\CommLink\Category\Category $category
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Category $category)
    {
        $this->authorize('web.admin.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = $category->commLinks()->orderByDesc('cig_id')->paginate(20);

        return view(
            'admin.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
